<?php

namespace Hexbatch\Thangs\Helpers\ThangTreeTraits;

use Hexbatch\Thangs\Enums\TypeOfThangAsyncPolicy;
use Hexbatch\Thangs\Enums\TypeOfThangSavePolicy;
use Hexbatch\Thangs\Models\ThangCallback;
use Hexbatch\Thangs\Models\ThangCommand;
use Hexbatch\Thangs\Models\ThangHook;
use Illuminate\Support\Facades\DB;

trait ThangSaves
{

    protected function shouldSave(ThangCommand $cmd) : bool{
        if ($this->thang->thang_save_policy === TypeOfThangSavePolicy::NEVER_SAVE) {
            return false;
        }
        else if ($this->thang->thang_save_policy === TypeOfThangSavePolicy::AUTO_SAVE) {
            if ($this->thang->thang_async_policy === TypeOfThangAsyncPolicy::NEVER_ASYNC) {
                return false;
            } else if ($this->thang->thang_async_policy === TypeOfThangAsyncPolicy::ALWAYS_ASYNC) {
                return true;
            } else {
                /** @var ThangHook $hook */
                foreach ($cmd->command_callbacks as $callback) {
                    if ($callback->owning_hook->is_async) {
                        return true;
                    }
                }
                return $cmd->is_async;
            }
        } else {
            return true;
        }
    }


    /**
     * @throws \Throwable
     */
    protected function saveCommand(ThangCommand $cmd, bool $b_force = false)
    {
        if (!$b_force) {
            if (!$this->shouldSave($cmd)) {return;}
        }

        DB::transaction(function () use ($cmd) {
            if (!$this->thang->id ) {
                $this->saveThang();
            }

            if ($cmd->parent_ref_uuid && !$cmd->parent_id) {
                $this->saveCommand($this->getCommand($cmd->parent_ref_uuid), true);

            }
            //if got here, save the poor command, its hooks and callbacks
            $is_new = !$cmd->id;

            if ($is_new) {
                $callbacks = $cmd->command_callbacks;
                unset($cmd->command_callbacks);
                $cmd->save();
                $this->setChildrenForParentId($cmd);

                foreach ($callbacks as $callback) {
                    unset($callback->owning_hook);
                    $callback->source_command_id = $cmd->id;
                    $callback->save();
                }
                $cmd->loadMissing('command_callbacks','command_callbacks.owning_hook');
                //callbacks saved again when modified elsewhere
            } else {
                $cmd->save();
            }

        });

    }

    /**
     * @throws \Throwable
     */
    protected function saveCallback(ThangCallback $callback) {
        $cmd = $this->getCommand($callback->source_command_ref);
        if (!$this->shouldSave($cmd)) {return;}
        if (!$cmd->id) { $this->saveCommand($cmd);}
        $callback->save();
    }

    protected function setChildrenForParentId(ThangCommand $cmd): void
    {
        $kids = $this->getChildren($cmd);

        foreach ($kids as $kid) {
            $kid->parent_id = $cmd->id;
        }
    }

    protected function saveThang()
    {
        if ($this->thang->thang_save_policy === TypeOfThangSavePolicy::NEVER_SAVE) {
            return;
        } else if ($this->thang->thang_save_policy === TypeOfThangSavePolicy::AUTO_SAVE) {
            //only save thang if any commands are saved (have an id)
            $b_never_saved = true;
            foreach ($this->getAllCommands() as $cmd) {
                if ($this->shouldSave($cmd)) {$b_never_saved = false; break;}
            }
            if ($b_never_saved) {return;}
        }

        $is_new = !$this->thang->id;
        $this->thang->save();

        if ($is_new) {
            foreach ($this->getAllCommands() as $cmd) {
                $cmd->owning_thang_id = $this->thang->id;
            }
        }

    }

}
