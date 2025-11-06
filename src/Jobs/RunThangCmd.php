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


class RunThangCmd implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,Batchable;



    public function __construct(
        public string $thang_uuid,
        public string $cmd_uuid
    ) {}


    /**
     * @throws \Throwable
     */
    public function handle(): void
    {
        $tree = null;
        $cmd = null;
        try {
            $tree = ThangTree::makeThangTree(thang: $this->thang_uuid);
            $cmd =$tree->getCommand(uuid: $this->cmd_uuid);
            if ($cmd->isCompleted() ) {
                return;
            }
            $tree->doCommand(cmd: $cmd);
        } catch (\Exception $e) {
            try
            {
                $tree?->markCommandAsError($cmd,$e);

            } catch (\Exception $ef) {
                Log::error(message: "cannot save thang command ".$ef->getMessage());
            }
            Log::error(message: "Error while running thang command: ".$e->getMessage(),context: [
                'thang_uuid'=>$this->thang_uuid,'command_uuid'=>$this->cmd_uuid,
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
        return [(new WithoutOverlapping($this->cmd_uuid))->expireAfter(180) , new SkipIfBatchCancelled()];
    }
}
