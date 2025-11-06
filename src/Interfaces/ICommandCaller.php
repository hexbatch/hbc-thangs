<?php
namespace Hexbatch\Thangs\Interfaces;


use Hexbatch\Thangs\Enums\TypeOfCmdStatus;
use Hexbatch\Thangs\Models\ThangCommand;

interface ICommandCaller
{
    public function onCommandCompletion(ThangCommand $cmd, TypeOfCmdStatus $status, array $output) :void;
}
