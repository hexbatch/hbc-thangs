<?php

namespace Hexbatch\Thangs\Data\Params;


use Hexbatch\Thangs\Data\Rules\ValidateCommandClass;
use OpenApi\Attributes as OA;
use Spatie\LaravelData\Attributes\MergeValidationRules;
use Spatie\LaravelData\Attributes\Validation\Max;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[OA\Schema(schema: 'CommandParams')]
#[TypeScript]
#[MergeValidationRules]
class CommandParams extends Data
{

    public function __construct(

        #[Max(255)]
        #[OA\Property( title:"Command class",nullable: true)]
        public string $command_class,


        #[OA\Property( title:"Command args", items: new OA\Items(),nullable: true)]
        /** @var mixed[] $command_args */
        public array|null $command_args ,


        #[OA\Property( title:"Command tags", items: new OA\Items(),nullable: true)]
        /** @var mixed[] $command_tags */
        public array|null $command_tags,

        #[OA\Property( title:"Async")]
        public bool $is_async = false,

        #[OA\Property( title:"Bubble exceptions")]
        public bool $bubble_exceptions = false,


    ) {

    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'command_class' => new ValidateCommandClass(),
        ];
    }

}
