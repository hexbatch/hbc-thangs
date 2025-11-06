<?php

namespace Hexbatch\Thangs\Callables;

use Hexbatch\Thangs\Enums\TypeOfCmdStatus;

use Hexbatch\Thangs\Interfaces\ICmdCallReturn;
use Hexbatch\Thangs\Interfaces\ICommandCallable;
use Hexbatch\Thangs\Interfaces\IThangBuilder;
use Illuminate\Support\Facades\Log;

class CallableStub implements ICommandCallable
{

    public static function doCall(array $children_args,   array $command_args): ICmdCallReturn
    {
        Log::debug("calling with args",[$children_args,$command_args]);
        return new CallableReturnStub(status: TypeOfCmdStatus::CMD_SUCCESS,data: ['test'=>1,'found'=>['kids'=>$children_args,'me'=>$command_args]]);
    }

    public static function makeBuild(IThangBuilder $builder): void
    {
        // TODO: Implement makeBuild() method.
    }
}
