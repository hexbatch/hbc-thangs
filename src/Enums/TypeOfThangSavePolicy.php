<?php
namespace Hexbatch\Thangs\Enums;
use OpenApi\Attributes as OA;

/**
 * postgres type_of_thang_save_policy
 */
#[OA\Schema]
enum TypeOfThangSavePolicy : string {
    use EnumTryTrait;

    case NEVER_SAVE = 'never_save'; //not run yet
    case ALWAYS_SAVE = 'always_save'; //it finished with fail status
    case AUTO_SAVE = 'auto_save'; //it finished with ok status
}


