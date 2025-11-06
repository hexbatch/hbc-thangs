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

    public function addChild(
        string $command_class,?bool $is_async = null,bool $bubble_exceptions = false,array $command_args = [],array $command_tags = []
    ) : IThangBuilder;

    public function addParent(
        string $command_class,?bool $is_async = null,bool $bubble_exceptions = false,array $command_args = [],array $command_tags = []
    ) : IThangBuilder;

    public function end(): IThangBuilder;

    public function getSavePolicy() : TypeOfThangSavePolicy;
    public function getAsyncPolicy() : TypeOfThangAsyncPolicy;
    public function getNamespace() : UserNamespace;
    public function getCallbackUrl() : ?string;
    public function getThangUuid() : ?string;

    /** @return Collection<CommandParams> */
    public function getCommands() : Collection;

    public function execute() : ThangTree;

    public function showTree() : array;

    public static function createBuilder() : IThangBuilder;
}
