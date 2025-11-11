<?php
namespace Hexbatch\Thangs\Interfaces;

use App\Models\UserNamespace;
use Hexbatch\Thangs\Data\Params\CommandParams;
use Hexbatch\Thangs\Enums\TypeOfThangAsyncPolicy;
use Hexbatch\Thangs\Enums\TypeOfThangSavePolicy;
use Hexbatch\Thangs\Helpers\ThangTree;
use Illuminate\Support\Collection;

interface IThangBuilder
{
    public function setSavePolicy(TypeOfThangSavePolicy $policy) : IThangBuilder;
    public function setAsyncPolicy(TypeOfThangAsyncPolicy $policy) : IThangBuilder;

    public function setNamespace(UserNamespace $namespace) : IThangBuilder;
    public function setCallbackUrl(string $url = null) : IThangBuilder;
    public function setThangUuid(string $uuid = null) : IThangBuilder;
    public function bubbleExceptions(bool $b_bubble = true) : IThangBuilder;

    public function leaf(
        string|CommandParams|array $command_class,?bool $is_async = null,array $command_args = [],array $command_tags = [],?bool $bubble_exceptions = null
    ) : IThangBuilder;

    public function tree(
        string|CommandParams|array $command_class,?bool $is_async = null,array $command_args = [],array $command_tags = [],?bool $bubble_exceptions = null
    ) : IThangBuilder;

    public function end(): IThangBuilder;

    public function getSavePolicy() : TypeOfThangSavePolicy;
    public function getAsyncPolicy() : TypeOfThangAsyncPolicy;
    public function getNamespace() : UserNamespace;
    public function getCallbackUrl() : ?string;
    public function getThangUuid() : ?string;
    public function isEmpty() : bool;

    /** @return Collection<CommandParams> */
    public function getCommands() : Collection;

    public function execute() : ThangTree;

    public function showTree() : array;

    public static function createBuilder() : IThangBuilder;
}
