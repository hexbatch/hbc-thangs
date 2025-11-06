<?php
namespace Hexbatch\Thangs\Seeds\Hooks;

use Hexbatch\Thangs\Actions\Events\TestCallback;
use Hexbatch\Thangs\Interfaces\IHookDefinition;

class TestPostHook implements IHookDefinition
{
    public static function getHookNotes():string {
        return "Test post hooks";
    }
    public static function getHookTags():array {
        return ['post-test'];
    }
    public static function getHookName():string {
        return 'Post'.TestCallback::HOOK_BASE;
    }
    public static function getHookEvent():string {
        return TestCallback::EVENT_NAME;
    }

    public static function isHookPre(): bool {
        return false;
    }

    public static function isAsync(): bool {return false;}

    public static function getHookData():array {
        return ['hook_data'=>"this is the post hook"];
    }

    public static function getPriority(): int { return 20;}
}
