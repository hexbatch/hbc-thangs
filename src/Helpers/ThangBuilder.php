<?php

namespace Hexbatch\Thangs\Helpers;

use App\Models\UserNamespace;
use Hexbatch\Thangs\Data\Params\CommandParams;
use Hexbatch\Thangs\Data\ThangCommandData;
use Hexbatch\Thangs\Enums\TypeOfThangAsyncPolicy;
use Hexbatch\Thangs\Enums\TypeOfThangSavePolicy;
use Hexbatch\Thangs\Interfaces\IThangBuilder;
use Hexbatch\Thangs\Models\ThangCallback;
use Hexbatch\Thangs\Models\ThangCommand;
use Illuminate\Support\Collection;


class ThangBuilder implements IThangBuilder
{
    protected TypeOfThangSavePolicy $save_policy = TypeOfThangSavePolicy::AUTO_SAVE;
    protected TypeOfThangAsyncPolicy $async_policy = TypeOfThangAsyncPolicy::AUTO_ASYNC;
    protected ?UserNamespace $namespace = null;
    protected ?string $callback_url = null;
    protected bool $always_bubble_exceptions = false;
    protected ?ThangTree $tree = null;

    /** @var Collection<ThangCommandData> */
    protected Collection $list;
    protected \Tree\Builder\NodeBuilder|null $builder = null;

    protected ?ThangTree $thang_tree = null;

    protected ?string $local_parent_uuid = null;
    protected ?string $thang_uuid = null;

    public function setThangUuid(string $uuid = null): IThangBuilder
    {
        $this->thang_uuid = $uuid;
        return $this;
    }

    public function getThangUuid(): ?string
    {
        return $this->thang_uuid;
    }

    protected function __construct()
    {
        $this->builder = new \Tree\Builder\NodeBuilder();
        $this->list = new Collection();
    }

    public static function createBuilder()
    : ThangBuilder
    {
        $ret = new static();
        return $ret;
    }

    public function setSavePolicy(TypeOfThangSavePolicy $policy) : IThangBuilder {
        $this->save_policy = $policy;
        return $this;
    }

    public function setAsyncPolicy(TypeOfThangAsyncPolicy $policy): IThangBuilder
    {
        $this->async_policy = $policy;
        return $this;
    }

    public function setNamespace(UserNamespace $namespace): IThangBuilder
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function setCallbackUrl(string $url = null): IThangBuilder
    {
        $this->callback_url = $url;
        return $this;
    }

    public function bubbleExceptions(bool $b_bubble = true) : IThangBuilder
    {
        $this->always_bubble_exceptions = $b_bubble;
        return $this;
    }

    protected function doCommand(
        string $command_class, ?bool $is_async = null, bool $bubble_exceptions = false,
        array $command_args = [], array $command_tags = []): ThangCommand
    {
        if ($this->always_bubble_exceptions) {$bubble_exceptions = true;}

        $param = CommandParams::validateAndCreate([
            'command_class' =>$command_class,
            'is_async' =>!!$is_async,
            'bubble_exceptions' =>$bubble_exceptions,
            'command_args' =>$command_args,
            'command_tags' =>$command_tags,
        ]);

        $cmd = ThangTree::generateMemoryCommand($param,$this->local_parent_uuid);
        $this->list[] = $cmd;

        return $cmd;
    }

    public function addParent(
        string $command_class, ?bool $is_async = null, bool $bubble_exceptions = false,
        array $command_args = [], array $command_tags = []): IThangBuilder
    {
        $cmd = $this->doCommand(command_class: $command_class,is_async: $is_async,bubble_exceptions: $bubble_exceptions,
                                    command_args: $command_args,command_tags: $command_tags);

        $this->builder->tree($cmd);
        $this->local_parent_uuid = $cmd->ref_uuid;



        return $this;
    }

    public function addChild(
        string $command_class, ?bool $is_async = null, bool $bubble_exceptions = false,
        array $command_args = [], array $command_tags = []): IThangBuilder
    {
        $cmd = $this->doCommand(command_class: $command_class,is_async: $is_async,bubble_exceptions: $bubble_exceptions,
            command_args: $command_args,command_tags: $command_tags);

        $this->builder->leaf($cmd);

        return $this;
    }


    public function end(): IThangBuilder
    {
        $this->builder->end();
        return $this;
    }

    /** @return Collection<CommandParams> */
    public function getCommands(): Collection
    {
        return $this->list;
    }







    public function getSavePolicy(): TypeOfThangSavePolicy
    {
        return $this->save_policy;
    }

    public function getAsyncPolicy(): TypeOfThangAsyncPolicy
    {
        return $this->async_policy;
    }





    public function getNamespace(): UserNamespace
    {
       return $this->namespace;
    }

    public function getCallbackUrl(): ?string
    {
        return $this->callback_url;
    }

    public function showTree() : array
    {
        return ThangCommand::nestCollection($this->list);
    }

    /** @throws \Throwable */
    protected function getThangTree() : ThangTree {
        if ($this->thang_tree && ($this->thang_uuid === $this->thang_tree->getThang()->ref_uuid)) {
            return $this->thang_tree;
        }
        $this->thang_tree = ThangTree::makeThangTree(builder: $this);
        $this->thang_uuid = $this->thang_tree->getThang()->ref_uuid;
        return $this->thang_tree;
    }

    /** @throws \Throwable */
    public function execute() : ThangTree {
        $this->getThangTree()->runCommands();
        return $this->getThangTree();
    }

    /** @throws \Throwable */
    public function eatManualCallback(string|ThangCallback $callback, int $http_code, array $data) : ThangTree {

        if (is_string($callback)) {
            $da_callback = $this->getThangTree()->getCallback($callback);
        } else {
            $da_callback = $callback;
        }

        $da_callback->loadMissing(['source_command','source_command.thang_owner']);
        $this->setThangUuid($da_callback->source_command->thang_owner->ref_uuid);

        $this->getThangTree()->processManual(callback: $da_callback,http_code: $http_code,data: $data);
        return $this->getThangTree();
    }


}
