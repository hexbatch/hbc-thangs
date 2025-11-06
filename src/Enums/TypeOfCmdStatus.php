<?php
namespace Hexbatch\Thangs\Enums;
use OpenApi\Attributes as OA;


/**
 * postgres enum type_of_cmd_status
 */
#[OA\Schema]
enum TypeOfCmdStatus : string {
    use EnumTryTrait;

    case CMD_WAITING = 'cmd_waiting'; //not run yet
    case CMD_FAIL = 'cmd_fail'; //it finished with fail status
    case CMD_SUCCESS = 'cmd_success'; //it finished with ok status
    case CMD_ERROR = 'cmd_error'; //exception thrown
    case CMD_RUNNING = 'cmd_running'; //in the bus

    const FINISHED_STATE = [
      self::CMD_FAIL,self::CMD_ERROR,self::CMD_SUCCESS
    ];

}


