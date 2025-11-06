<?php

namespace Hexbatch\Thangs\Helpers;

class ExceptionToArray
{
    public static function makeArray(\Exception $exception) : array {
        $code = $exception->getCode()??0;
        $extra = '';
        if (!ctype_digit((string)$code)) {
            $extra = $code. ' '; //some exceptions throw non-numeric codes
            $code = 0;
        }
        $node = [];
        $node["message"] =  $extra. $exception->getMessage();
        $node["code"] = $code;
        $node["line"] = $exception->getLine();
        $node["file"] = $exception->getFile();
        $node["trace"] = $exception->getTraceAsString();

        $previous_errors = [];
        $prev = $exception;
        while ($prev = $prev->getPrevious()) {
            $x = [];
            $x['message'] = $prev->getMessage();
            $x['code'] = $prev->getCode();
            $x['line'] = $prev->getLine();
            $x['file'] = $prev->getFile();
            $previous_errors[] = $x;
        }
        $node["prev"] = $previous_errors;

        return $node;
    }
}
