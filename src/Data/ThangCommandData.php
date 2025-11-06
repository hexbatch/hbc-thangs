<?php

namespace Hexbatch\Thangs\Data;


use App\Helpers\AttributeConstants;
use Carbon\Carbon;

use Hexbatch\Thangs\Data\Rules\ValidateCommandClass;
use Hexbatch\Thangs\Enums\TypeOfCmdStatus;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;
use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\MergeValidationRules;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[OA\Schema(schema: 'ThangCommand')]
#[TypeScript]
#[MergeValidationRules]
class ThangCommandData extends Data
{



    public function __construct(



        #[OA\Property( title:"Uuid",format: 'uuid')]
        #[Uuid]
        public string $ref_uuid,

        #[OA\Property( title:"Parent uuid",format: 'uuid')]
        #[Uuid]
        public string|null $parent_ref_uuid,



        #[OA\Property( title: "Parent", type: ThangCommandData::class)]
        public ThangCommandData|Lazy|Optional|null $parent_command,




        #[Max(255)]
        #[OA\Property( title:"Command class",nullable: true)]
        public string $command_class,



        #[OA\Property( title: "Callbacks",type: 'array',items:  new OA\Items(type: ThangCallbackData::class))]
        #[WithoutValidation]
        /** @var Collection<ThangCallbackData> $command_callbacks */
        public Optional|Collection $command_callbacks,



        #[OA\Property( title:"Async")]
        public bool $is_async = false,

        #[OA\Property( title:"Bubble exceptions")]
        public bool $bubble_exceptions = false,


        #[OA\Property( title:"Command args", items: new OA\Items(),nullable: true)]
                      /** @var mixed[] $command_args */
        public array|null $command_args = [],


        #[OA\Property( title:"Command tags", items: new OA\Items(),nullable: true)]
                      /** @var mixed[] $command_tags */
        public array|null $command_tags = [],

        #[OA\Property( title:"Staging", items: new OA\Items(),nullable: true)]
        /** @var mixed[] $staging_data_from_children */
        public ?array $staging_data_from_children = null,

        #[OA\Property( title:"Errors", items: new OA\Items(),nullable: true)]
        /** @var mixed[] $command_errors */
        public ?array $command_errors = null,

        #[OA\Property( title:"Status")]
        public TypeOfCmdStatus $command_status = TypeOfCmdStatus::CMD_WAITING,

        #[AutoLazy]
        public null|Lazy|int $id = null ,

        #[AutoLazy]
        public null|Lazy|int $owning_thang_id = null,

        #[AutoLazy]
        public null|Lazy|int $parent_id = null,

        #[OA\Property( title: 'Created', type: 'string',format: 'datetime',example: "2025-02-25T15:00:59-06:00",nullable: true)]
        #[WithCast(DateTimeInterfaceCast::class, format: DATE_ATOM)]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM, setTimeZone: AttributeConstants::OUTPUT_TIMEZONE)]
        public ?Carbon $created_at = null,

        #[OA\Property( title: 'Updated',type: 'string', format: 'datetime',example: "2025-03-25T15:00:59-06:00",nullable: true)]
        #[WithCast(DateTimeInterfaceCast::class, format: DATE_ATOM)]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM, setTimeZone: AttributeConstants::OUTPUT_TIMEZONE)]
        public ?Carbon $updated_at = null,

    ) {

    }

//    public static function fromModel(ThangCommand $cmd) : self
//    {
//
//        $data =  [
//            'id'=> Lazy::create(fn() => $cmd->id) ,
//            'owning_thang_id'=> Lazy::create(fn() => $cmd->owning_thang_id),
//            'parent_id'=> Lazy::create(fn() => $cmd->parent_id),
//            'ref_uuid'=> $cmd->ref_uuid,
//            'parent_ref_uuid'=> $cmd->parent_ref_uuid,
//
//
//            'command_class'=> $cmd->command_class,
//            'is_async'=> $cmd->is_async,
//            'bubble_exceptions'=> $cmd->bubble_exceptions,
//            'command_args'=> $cmd->command_args,
//            'command_tags'=> $cmd->command_tags,
//            'staging_data_from_children'=> $cmd->staging_data_from_children,
//            'command_errors'=> $cmd->command_errors,
//            'command_status'=> $cmd->command_status,
//
//            'created_at'=> Carbon::parse($cmd->created_at),
//            'updated_at'=> Carbon::parse($cmd->updated_at),
//        ];
//
//        if ($cmd->relationLoaded('parent_command')) {
//            $data['parent_command'] = $cmd->parent_command;
//
//        }
//
//        if ($cmd->relationLoaded('command_hooks')) {
//            if ($cmd->command_hooks) {
//                $data['command_hooks'] = $cmd->command_hooks;
//            }
//
//        }
//
//        if ($cmd->relationLoaded('command_callbacks')) {
//            if ($cmd->command_callbacks) {
//                $data['command_callbacks'] = $cmd->command_callbacks;
//            }
//        }
//
//        return self::from($data);
//
//    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'command_class' => new ValidateCommandClass(),
        ];
    }







}
