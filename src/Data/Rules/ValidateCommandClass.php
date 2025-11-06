<?php
namespace Hexbatch\Thangs\Data\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class ValidateCommandClass implements ValidationRule
{

    /**
     * Run the validation rule.
     *
     * @param Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!$value ) {return;}

        if (!is_string($value)) {
            $fail("hbc-thangs::thangs.improper_command_class")->translate(['ref'=>strval($value)]);
            return;
        }

        if(!class_exists($value)) {
            $fail("hbc-thangs::thangs.improper_command_class")->translate(['ref'=>$value]);
            return;
        }
        $interfaces = class_implements($value);
        if (!isset($interfaces['Hexbatch\Thangs\Interfaces\ICommandCallable'])) {
            $fail("hbc-thangs::thangs.invalid_command_class")->translate(['ref'=>$value]);
        }


    }

}
