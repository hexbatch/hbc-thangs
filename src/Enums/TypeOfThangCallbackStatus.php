<?php
namespace Hexbatch\Thangs\Enums;
use OpenApi\Attributes as OA;
/**
 * postgres enum type_of_thang_callback_status
 */
#[OA\Schema]
enum TypeOfThangCallbackStatus : string {
    use EnumTryTrait;

    case BUILDING = 'building';
    case RUNNING = 'running';
    case MANUAL = 'manual';
    case SUCCESSFUL = 'successful';
    case FAIL = 'fail';
    case ERROR = 'error';

    const FINISHED_STATE = [
        self::SUCCESSFUL,self::FAIL,self::ERROR
    ];

    const RUNNING_STATE = [
        self::MANUAL,self::RUNNING
    ];

}


