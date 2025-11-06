<?php
namespace Hexbatch\Thangs\Interfaces;

interface IHookEventCallable
{
    const int STANDARD_EVENT_PRIORITY = 20;

    public  function onEvent(array $command_data, string $status_key) : array;

    public static function getEventName():string ;

    public static function registerEvent(): void ;
}
