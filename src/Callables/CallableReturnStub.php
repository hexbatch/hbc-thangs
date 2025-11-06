<?php

namespace Hexbatch\Thangs\Callables;

use Hexbatch\Thangs\Enums\TypeOfCmdStatus;
use Hexbatch\Thangs\Interfaces\ICmdCallReturn;

class CallableReturnStub implements ICmdCallReturn
{

    public function __construct(
        protected TypeOfCmdStatus $status,
        protected array $data
    )
    {

    }
    public function getStatus(): TypeOfCmdStatus
    {
       return $this->status;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
