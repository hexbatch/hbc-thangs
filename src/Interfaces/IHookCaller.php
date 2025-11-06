<?php
namespace Hexbatch\Thangs\Interfaces;




use Hexbatch\Thangs\Models\ThangCallback;
use Hexbatch\Thangs\Models\ThangCommand;
use Hexbatch\Thangs\Models\ThangHook;

interface IHookCaller
{
    public function onHookCompletion(ThangCommand $cmd,ThangHook $hook, ?ThangCallback $callback) :void;
}
