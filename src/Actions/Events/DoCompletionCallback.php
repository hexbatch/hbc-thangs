<?php
namespace Hexbatch\Thangs\Actions\Events;

use Hexbatch\Thangs\Actions\Events\Traits\EventCallable;
use Hexbatch\Thangs\Enums\TypeOfThangCallbackStatus;
use Hexbatch\Thangs\Exceptions\ThangException;
use Hexbatch\Thangs\Interfaces\IHookEventCallable;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class DoCompletionCallback implements IHookEventCallable
{
    use AsAction,EventCallable;
    const EVENT_NAME = 'send_callback_url';
    const string HOOK_NAME = "notification-at-finish";

    const NOTIFICATION_KEY = 'completion_notification';
    const NOTIFICATION_SUBKEY_URL = 'notification_url';


    public function onEvent(array $command_data, string $status_key): array
    {


        $block = $command_data[static::NOTIFICATION_KEY]??[];
        $url = $block[static::NOTIFICATION_SUBKEY_URL]??null;
        if (!$url) {
            throw new ThangException("notification url is not set");
        }

        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json'
        ];
        $response = null;
        try {
            $response = Http::withHeaders($headers)->post($url, $command_data);
            $response->throwIfClientError();
            $response->throwIfServerError();
            $command_data[$status_key] = ['code'=>$response->getStatusCode(),'status'=>TypeOfThangCallbackStatus::SUCCESSFUL];
        } catch (\Illuminate\Http\Client\ConnectionException|\Illuminate\Http\Client\RequestException) {
            $command_data[$status_key] = ['code'=>$response?->getStatusCode()??0,'status'=>TypeOfThangCallbackStatus::FAIL];
        }
        return $command_data;
    }
}
