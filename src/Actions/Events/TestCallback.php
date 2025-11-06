<?php
namespace Hexbatch\Thangs\Actions\Events;

use Hexbatch\Thangs\Actions\Events\Traits\EventCallable;
use Hexbatch\Thangs\Enums\TypeOfThangCallbackStatus;
use Hexbatch\Thangs\Interfaces\IHookEventCallable;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class TestCallback implements IHookEventCallable
{
    use AsAction,EventCallable;
    const EVENT_NAME = 'test_callback';
    const HOOK_BASE = 'HappyTestCallback';

    public function onEvent(array $command_data, string $status_key): array
    {
        Log::debug("hook called with args",$command_data);
        $command_data['truth'] = "this works";
        $command_data[$status_key] = ['code'=>205,'status'=>TypeOfThangCallbackStatus::SUCCESSFUL];
        return $command_data;
    }
}
