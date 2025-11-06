<?php
namespace Hexbatch\Thangs\Enums;

trait EnumTryTrait
{
    public static function tryFromInput(string|int|bool|null $test ) : ?static {
        if ($test === null) {return null;}
        $maybe  = static::tryFrom($test);
        if (!$maybe ) {
            $delimited_values = implode('|',array_column(static::cases(),'value'));
            $class_name = static::class;
            throw new \InvalidArgumentException(__("Invalid thang enum for $class_name: found $test but expected one of $delimited_values"));
        }
        return $maybe;
    }
}
