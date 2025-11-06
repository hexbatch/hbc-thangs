<?php
namespace Hexbatch\Thangs\Enums;
use OpenApi\Attributes as OA;

/**
 * postgres enum type_of_thang_async_policy
 */
#[OA\Schema]
enum TypeOfThangAsyncPolicy : string {
    use EnumTryTrait;

    case NEVER_ASYNC = 'never_async'; //not run yet
    case ALWAYS_ASYNC = 'always_async'; //it finished with fail status
    case AUTO_ASYNC = 'auto_async'; //it finished with ok status



}


