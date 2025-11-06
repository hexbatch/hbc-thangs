<?php
namespace Hexbatch\Thangs\Jobs;




use Hexbatch\Thangs\Helpers\ThangTree;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class RunThangHook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,Batchable;



    public function __construct(
        public string $thang_uuid,
        public ?string $cmd_uuid,
        public string $callback_uuid
    ) {}


    /**
     * @throws \Throwable
     */
    public function handle(): void
    {
        try {
            $tree = ThangTree::makeThangTree(thang: $this->thang_uuid);
            $cmd =$tree->getCommand(uuid: $this->cmd_uuid);

            $callback = $tree->getCallback($this->callback_uuid);


            if (!$callback->owning_hook) {
                throw new \LogicException("Callback's hook not found for $this->callback_uuid");
            }

            if ($callback->isRunning() || $callback->isCompleted()) {
                return;
            }

            $tree->runHook(hook: $callback->owning_hook, cmd: $cmd, callback: $callback);
        } catch (\Exception $e) {
            Log::error(message: "while running callback: ".$e->getMessage(),context: [
                'thang_uuid'=>$this->thang_uuid,'command_uuid'=>$this?->cmd_uuid,
                'file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode()]);
            $this->fail($e);
        }
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->callback_uuid))->expireAfter(180) , new SkipIfBatchCancelled()];
    }
}
