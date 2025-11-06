<?php

namespace Hexbatch\Thangs\Helpers\ThangTreeTraits;

use Hexbatch\Thangs\Enums\TypeOfThangAsyncPolicy;
use Hexbatch\Thangs\Enums\TypeOfThangCallbackStatus;
use Hexbatch\Thangs\Jobs\RunThangHook;
use Hexbatch\Thangs\Models\ThangCallback;
use Hexbatch\Thangs\Models\ThangCommand;
use Hexbatch\Thangs\Models\ThangHook;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait CommandHooks
{
    /**
     * get all the matching hooks, for all the nodes, in one sql
     * match up which callback goes where
     */
    protected function findHooks(): void
    {
        /** @var \Illuminate\Database\Eloquent\Builder $laravel_hooks */
        $laravel_hooks = ThangHook::with('owning_namespace')
            ->orderBy('hook_priority','desc');


        foreach ($this->getAllCommands() as $cmd) {

            if (count($cmd->command_tags ?? [])) {
                ThangHook::addOrWhereKeywords(build: $laravel_hooks, keywords: $cmd->command_tags ?? [],
                    owning_namespace_id: $this->thang->owning_namespace_id);
            }
        }
        /** @var Collection<ThangHook> $hooks_to_use */
        $hooks_to_use = $laravel_hooks->get();
        foreach ($this->getAllCommands() as $cmd) {

            $my_callbacks = [];

            foreach ($hooks_to_use as $any_hook) {
                if (count(array_intersect($cmd->command_tags ?? [], $any_hook->hook_tags)) > 0) {

                    $callback = new ThangCallback(
                        [
                            'owning_hook_id' => $any_hook->id,
                            'owning_hook_ref' => $any_hook->ref_uuid,
                            'source_command_id' => $cmd->id,
                            'source_command_ref' => $cmd->ref_uuid,
                            'callback_http_code' => 0,
                            'ref_uuid' => Str::uuid(),
                            'callback_data' => null,
                            'callback_status' => TypeOfThangCallbackStatus::BUILDING,
                        ]

                    );

                    $callback->owning_hook = $any_hook;
                    $my_callbacks[] = $callback;
                }
            }

            if (count($my_callbacks)) {
                $data_callbacks = new Collection($my_callbacks);
                $cmd->command_callbacks = $data_callbacks;
            }
        }

    }

    protected function activateHook(ThangHook $hook,ThangCommand $cmd, ThangCallback $call)
    : bool
    {

        $async = $hook->is_async;

        if ($this->thang->thang_async_policy === TypeOfThangAsyncPolicy::ALWAYS_ASYNC) {
            $async = true;
        } elseif ($this->thang->thang_async_policy === TypeOfThangAsyncPolicy::NEVER_ASYNC) {
            $async = false ;
        }

        if ($async) {
            RunThangHook::dispatch(thang_uuid: $this->thang->ref_uuid, cmd_uuid: $cmd->ref_uuid, callback_uuid: $call->ref_uuid);
        } else {
            ThangHook::runHook(hook: $hook, caller: $this, cmd: $cmd, callback: $call);
        }

        return $async;
    }

    public function runHook(ThangHook $hook,ThangCommand $cmd, ThangCallback $callback) {
        ThangHook::runHook(hook: $hook, caller: $this, cmd: $cmd, callback: $callback);
    }

    protected function runPostHooks(
        ThangCommand $cmd
    )
    :void
    {

        foreach ($cmd->command_callbacks as $call) {
            if ( $call->isCompleted() || $call->isRunning()) { continue;}
            if ($call->owning_hook->is_pre) {continue;}
            if (!$call->owning_hook->is_on) {continue;}

            $this->activateHook(hook: $call->owning_hook,cmd: $cmd,call: $call);
        }

    }

    protected function runPreHooks(
        ThangCommand $cmd
    )
    :bool
    {
        $flag_return = false;

        foreach ($cmd->command_callbacks as $call) {
            if ( $call->isCompleted() || $call->isRunning()) { continue;}
            if (!$call->owning_hook->is_pre) {continue;}
            if (!$call->owning_hook->is_on) {continue;}
            if ($call->owning_hook->is_async) {$flag_return = true;}

            $did_async = $this->activateHook(hook: $call->owning_hook,cmd: $cmd,call: $call);
            if ($did_async) {$flag_return = true;}
        }

        return $flag_return;
    }


}
