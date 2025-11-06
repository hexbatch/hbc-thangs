<?php
namespace Hexbatch\Thangs\Actions\Events\Traits;


use Hexbatch\Thangs\Interfaces\IHookEventCallable;
use TorMorten\Eventy\Facades\Eventy;

trait EventCallable
{
    public function handle(array $command_data, string $status_key): array
    {
        return $this->onEvent($command_data,$status_key);
    }

    public static function getEventName(): string
    {
        return static::EVENT_NAME;
    }

    public static function registerEvent(): void
    {
        Eventy::addFilter(static::getEventName(), function(array $command_data, string $status_key)  {
            return static::run($command_data,$status_key);
        }, IHookEventCallable::STANDARD_EVENT_PRIORITY, 2);
    }
}
