<?php
namespace Hexbatch\Thangs\Interfaces;

interface ICommandCallable
{
    public static function doCall(array $children_args, array $command_args) : ICmdCallReturn;
    public static function makeBuild(IThangBuilder $builder) : void;
}
