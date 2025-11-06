<?php
namespace Hexbatch\Thangs\Seeds\Hooks;

use Hexbatch\Thangs\Actions\Events\TestCallback;
use Hexbatch\Thangs\Interfaces\IHookDefinition;

class TestPreHook implements IHookDefinition
{
    public static function getHookNotes():string {
        return "Test pre hooks";
    }
    public static function getHookTags():array {
        return ['pre-test'];
    }
    public static function getHookName():string {
        return 'Pre'.TestCallback::HOOK_BASE;
    }
    public static function getHookEvent():string {
        return TestCallback::EVENT_NAME;
    }

    public static function isHookPre(): bool {
        return true;
    }

    public static function isAsync(): bool {return false;}

    public static function getHookData():array {
        return ['hook_data'=>"Pre test all the way baby !!!"];
    }

    public static function getPriority(): int { return 20;}
}
