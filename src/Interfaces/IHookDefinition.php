<?php
namespace Hexbatch\Thangs\Interfaces;

interface IHookDefinition
{

    public static function getHookNotes():string ;
    public static function getHookTags():array ;
    public static function getHookData():array ;
    public static function getHookName():string ;
    public static function getHookEvent():string ;

    public static function isHookPre(): bool ;
    public static function isAsync(): bool ;
    public static function getPriority(): int ;
}
