<?php

namespace Hexbatch\Thangs\Data;


use App\Helpers\AttributeConstants;
use Carbon\Carbon;
use Hexbatch\Thangs\Enums\TypeOfThangCallbackStatus;
use OpenApi\Attributes as OA;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[OA\Schema(schema: 'ThangCallback')]
#[TypeScript]
class ThangCallbackData extends Data
{


    public function __construct(



        #[OA\Property( title:"Uuid",format: 'uuid')]
        #[Uuid]
        public ?string $source_command_ref,


        #[OA\Property( title:"Hook uuid",format: 'uuid')]
        #[Uuid]
        public ?string $owning_hook_ref,

        #[Min(0),Max(599)]
        #[OA\Property( title: 'Http code',example: "200",nullable: true)]
        public int|null $callback_http_code,


        #[OA\Property( title:"Uuid",format: 'uuid')]
        #[Uuid]
        public string $ref_uuid,


        #[OA\Property( title:"Callback data", items: new OA\Items(),nullable: true)]
        /** @var mixed[] $callback_data */
        public ?array $callback_data,

        #[OA\Property( title:"Status")]
        public TypeOfThangCallbackStatus $callback_status,

        #[OA\Property( title: "Owner", type: ThangHookData::class)]
        public ThangHookData|Lazy|Optional $owning_hook,

        #[OA\Property( title: "Source command", type: ThangCommandData::class)]
        #[AutoWhenLoadedLazy]
        public ThangCommandData|Lazy|Optional $source_command,

        public null|Lazy|int $id = null,
        public null|Lazy|int $owning_hook_id = null,
        public null|Lazy|int $source_command_id = null,


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




}
