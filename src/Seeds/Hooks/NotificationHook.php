<?php
namespace Hexbatch\Thangs\Seeds\Hooks;

use Hexbatch\Thangs\Actions\Events\DoCompletionCallback;
use Hexbatch\Thangs\Interfaces\IHookDefinition;

class NotificationHook implements IHookDefinition
{
    public static function getHookNotes():string {
        return "sends a notice of when a thang completes";
    }
    public static function getHookTags():array {
        return [DoCompletionCallback::HOOK_NAME];
    }
    public static function getHookName():string {
        return DoCompletionCallback::HOOK_NAME;
    }
    public static function getHookEvent():string {
        return DoCompletionCallback::EVENT_NAME;
    }

    public static function isHookPre(): bool {
        return false;
    }

    public static function isAsync(): bool {return true;}

    public static function getHookData():array {
        return [];
    }

    public static function getPriority(): int { return 0;}
}
