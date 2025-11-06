<?php
namespace Hexbatch\Thangs\Interfaces;

use Hexbatch\Thangs\Enums\TypeOfCmdStatus;

interface ICmdCallReturn
{
    public function getStatus(): TypeOfCmdStatus;
    public function getData(): array;
}
