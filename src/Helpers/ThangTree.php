<?php

namespace Hexbatch\Thangs\Helpers;


use Hexbatch\Thangs\Actions\Events\DoCompletionCallback;
use Hexbatch\Thangs\Enums\TypeOfCmdStatus;
use Hexbatch\Thangs\Enums\TypeOfThangAsyncPolicy;
use Hexbatch\Thangs\Enums\TypeOfThangCallbackStatus;
use Hexbatch\Thangs\Exceptions\ThangException;
use Hexbatch\Thangs\Helpers\ThangTreeTraits\CommandHooks;
use Hexbatch\Thangs\Helpers\ThangTreeTraits\CommandManager;
use Hexbatch\Thangs\Helpers\ThangTreeTraits\ThangSaves;
use Hexbatch\Thangs\Interfaces\ICommandCaller;
use Hexbatch\Thangs\Interfaces\IHookCaller;
use Hexbatch\Thangs\Interfaces\IThangBuilder;
use Hexbatch\Thangs\Jobs\RunThangCmd;
use Hexbatch\Thangs\Models\Thang;
use Hexbatch\Thangs\Models\ThangCallback;
use Hexbatch\Thangs\Models\ThangCommand;
use Hexbatch\Thangs\Models\ThangHook;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;


class ThangTree implements ICommandCaller, IHookCaller
{
    use CommandManager,CommandHooks,ThangSaves;


    protected Thang|null $thang = null;

    protected function __construct()
    {
        $this->active_command_collection = new Collection();
        $this->passive_command_collection = new Collection();
    }

    public function getThang(): Thang
    {
        return $this->thang;
    }

    /**
     * @throws \Throwable
     */
    public static function makeThangTree(
        null|Thang|string           $thang = null,
        ?IThangBuilder              $builder = null
    ): static
    {
        $ret = new ThangTree();

        if ($thang) {
            if (is_string($thang)) {
                $thang_uuid = $thang;
                $thang = Thang::where('ref_uuid', $thang)
                    ->with(['commands', 'owning_namespace',
                        'commands.command_callbacks','commands.command_callbacks.owning_hook'])
                    ->first();
                if (!$thang) {
                    throw new ThangException("Could not find thang $thang_uuid");
                }
            }

            if ($thang instanceof Thang) {
                $thang->loadMissing('commands', 'owning_namespace',
                    'commands.command_callbacks','commands.command_callbacks.owning_hook');
                $ret->thang = $thang;
            } else {
                $ret->thang = null;
            }

            $ret->addCommands(params: $ret->thang->commands);
            //do not find callbacks if already thang
        } else {

            $ret->thang = new Thang([
                'owning_namespace_id' => $builder->getNamespace()->id,
                'ref_uuid' => Str::uuid()->toString(),
                'finished_data' => null,
                'thang_async_policy' => $builder->getAsyncPolicy(),
                'thang_save_policy' => $builder->getSavePolicy(),
            ]);
            $commands = $builder->getCommands()->all();
            $ret->addCommands(params: $commands);
            if ($builder->getCallbackUrl()) {
                $root_command = $ret->getRootCommand();
                if ($root_command)
                {
                    $extra_data = [
                        DoCompletionCallback::NOTIFICATION_KEY => [
                            DoCompletionCallback::NOTIFICATION_SUBKEY_URL => $builder->getCallbackUrl()
                        ]
                    ];
                    $root_command->command_tags = array_merge($root_command->command_tags ?? [], [DoCompletionCallback::HOOK_NAME]);
                    $root_command->command_args = array_merge($root_command->command_args ?? [], $extra_data);
                    $ret->saveCommand($root_command);
                }

            }

            $ret->findHooks();
        }


        return $ret;
    }



    /**
     * @throws \Throwable
     */
    protected function runCommand(ThangCommand $cmd)
    {
        $this->saveCommand($cmd);
        if (
            ($cmd->is_async || ($this->thang->thang_async_policy === TypeOfThangAsyncPolicy::ALWAYS_ASYNC ) )
            &&
            (!($this->thang->thang_async_policy === TypeOfThangAsyncPolicy::NEVER_ASYNC ))
        )
        {
            RunThangCmd::dispatch(thang_uuid: $this->thang->ref_uuid, cmd_uuid: $cmd->ref_uuid);
        } else {
            $this->doCommand(cmd: $cmd);
        }
    }


    /**
     * @throws \Throwable
     */
    public function onCommandCompletion(ThangCommand $cmd, TypeOfCmdStatus $status, array $output): void
    {
        if ($status === TypeOfCmdStatus::CMD_ERROR) {
            $cmd->command_errors = $output;
            $this->saveCommand($cmd);
        } else {
            $this->saveCommand($cmd);
            $parent_command = null;
            if ($cmd->parent_ref_uuid) {
                $parent_command = $this->getCommand($cmd->parent_ref_uuid);
            }

            if ($parent_command) {
                if ($cmd->isCompleted()) {
                    $parent_command->staging_data_from_children = array_replace_recursive($parent_command->staging_data_from_children ?? [], $output);

                }
                $this->checkHooksThenRunParent($cmd);

            } else
            {
                $this->thang->finished_data = array_merge($this->thang->finished_data ?? [], $output);
                $this->saveThang();
            }

            $this->runPostHooks(cmd: $cmd);


        }


    }

    /**
     * @throws \Throwable
     */
    public function runCommands()
    {

        $ready_commands = $this->getLeaves();
        foreach ($ready_commands as $cmd) {

            if ($cmd->isCompleted() || $cmd->isRunning()) {
                continue;
            }
            if ($cmd->isReady()) {
                $this->runCommand(cmd: $cmd);
            }
        }
    }

    /**
     * @throws \Throwable
     */
    public function onHookCompletion(ThangCommand $cmd,ThangHook $hook, ?ThangCallback $callback): void
    {
        $this->saveCallback($callback);

        if ($hook->is_pre) {
            if ($callback?->isSuccess()) {
                $cmd->command_args = array_replace_recursive($cmd->command_args ?? [], $callback->callback_data);
                $this->saveCommand($cmd);
            }
            $this->checkPreHooksThenRun($cmd);
        } else {
            $this->checkHooksThenRunParent($cmd);
        }
    }


    /**
     * @throws \Throwable
     */
    protected function checkHooksThenRunParent(ThangCommand $cmd)
    {
        $all_complete = true;
        $has_error = false;
        //post hook, see if all ran
        foreach ($cmd->command_callbacks as $callback) {
            if ($callback->owning_hook?->is_pre) {continue;}
            if (!$callback->isCompleted()) {
                $all_complete = false;
            }
            if ($callback->callback_status === TypeOfThangCallbackStatus::ERROR) {
                $has_error = true;
            }
        }

        if ($all_complete && !$has_error) {
            $this->maybeRunParent($cmd);
        }
    }



    /**
     * @throws \Throwable
     */
    protected function checkPreHooksThenRun(ThangCommand $cmd)
    {

        $all_complete = true;
        $has_error = false;
        //post hook, see if all ran
        foreach ($cmd->command_callbacks as $callback) {
            if (!$callback->owning_hook?->is_pre) {continue;}
            if (!$callback->isCompleted()) {
                $all_complete = false;
            }
            if ($callback->callback_status === TypeOfThangCallbackStatus::ERROR) {
                $has_error = true;
            }
        }

        if ($all_complete && !$has_error) {
            $this->runCommand($cmd);
        }
    }

    protected function areNestedChildrenCompleted(ThangCommand $cmd, bool $is_top = false ) {
        $kids = $this->getChildren($cmd);
        foreach ($kids as $kmand) {
            if (!$this->areNestedChildrenCompleted($kmand)) {
                return false ;
            }
        }
        if ($is_top) {return true;}
        return $cmd->isCompleted();
    }

    /**
     * @throws \Throwable
     */
    protected function maybeRunParent(ThangCommand $cmd)
    {
        //do not run if any child has error state
        if ($cmd->isError()) {
            return;
        }
        $parent_command = null;
        if ($cmd->parent_ref_uuid) {
            $parent_command = $this->getCommand($cmd->parent_ref_uuid);
        }
        $red_parent_node = $this->removeCommand(cmd: $cmd);
        if ($parent_command && !$parent_command->isCompleted()) {

            if (!$this->areNestedChildrenCompleted($parent_command,true)) {
                $this->saveCommand($parent_command);
                return;
            }

            if (0 === count($red_parent_node?->getChildren()??[])) {
                $this->runCommand($parent_command);
            } else {
                $this->saveCommand($parent_command);
            }
        }
    }

    /**
     * @throws \Throwable
     */
    public function markCommandAsError(
        ThangCommand $cmd,\Exception $e
    )
    :void
    {
        $cmd->command_errors = ExceptionToArray::makeArray($e);
        $cmd->command_status = TypeOfCmdStatus::CMD_ERROR;
        $this->saveCommand($cmd);
    }
    /**
     * Do pre-hooks first then post
     * @throws \Throwable
     */
    public function doCommand(
        ThangCommand $cmd
    )
    :void
    {

        try {
            if ($cmd->isCompleted()) {
                return;
            }
            /** @noinspection PhpConditionAlreadyCheckedInspection Editor does not do deep inspection*/
            if ($this->runPreHooks(cmd: $cmd) || $cmd->isCompleted()) {
                return;
            }


            if (!$cmd->command_class) {
                $this->onCommandCompletion(cmd: $cmd, status: TypeOfCmdStatus::CMD_ERROR, output: []);
                return;
            }

            $what = $cmd->getStaticCommandClass()::doCall(
                children_args: $cmd->staging_data_from_children??[], command_args: $cmd->command_args);

            $cmd->command_status = $what->getStatus();

            $this->onCommandCompletion(cmd: $cmd, status: $what->getStatus(), output: $what->getData());
        } catch (\Exception $e) {
            $cmd->command_status = TypeOfCmdStatus::CMD_ERROR;
            if ($cmd->bubble_exceptions) {
                throw $e;
            }
            $this->onCommandCompletion(cmd: $cmd, status: TypeOfCmdStatus::CMD_ERROR, output: ExceptionToArray::makeArray($e));
        }
    }

    /**
     * @throws \Throwable
     */
    public function processManual(ThangCallback $callback, int $http_code, array $data)
        : void
    {
        $callback->loadMissing(['owning_hook']);
        $cmd = $this->getCommand(uuid: $callback->source_command_ref);

        $callback->callback_data = $data;
        $callback->callback_http_code = $http_code;
        if ($http_code >= 200 && $http_code < 300) {
            $callback->callback_status = TypeOfThangCallbackStatus::SUCCESSFUL;
        } else  if ($http_code >= 500 && $http_code < 600) {
            $callback->callback_status = TypeOfThangCallbackStatus::ERROR;
        } else {
            $callback->callback_status = TypeOfThangCallbackStatus::FAIL;
        }

        $this->onHookCompletion(cmd: $cmd,hook: $callback->owning_hook,callback: $callback);
    }
}
