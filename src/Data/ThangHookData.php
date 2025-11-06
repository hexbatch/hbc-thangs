<?php

namespace Hexbatch\Thangs\Data;


use App\Data\ApiParams\Data\Namespaces\UserNamespaceData;
use App\Helpers\AttributeConstants;
use Carbon\Carbon;

use OpenApi\Attributes as OA;
use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
#[OA\Schema(schema: 'ThangHook')]
class ThangHookData extends Data
{

    public function __construct(



        #[OA\Property( title: 'On')]
        public bool $is_on,

        #[OA\Property( title: 'Async')]
        public bool $is_async,

        #[OA\Property( title: 'Before action')]
        public bool $is_pre,

        #[OA\Property( title: 'Priority')]
        public int $hook_priority,

        #[OA\Property( title:"Uuid",format: 'uuid')]
        #[Uuid]
        public string $ref_uuid,

        #[OA\Property( title:"Data", items: new OA\Items(),nullable: true)]
        /** @var mixed[] $hook_data */
        public ?array $hook_data,

        #[OA\Property( title:"Tags", items: new OA\Items(type: 'string'),nullable: true)]
        /** @var string[] $hook_tags */
        public array $hook_tags,

        #[OA\Property( title: 'Notes')]
        #[Max(20000)]
        public null|string $hook_notes,

        #[OA\Property( title: 'Name')]
        #[Max(30)]
        public string $hook_name,

        #[OA\Property( title: 'Event')]
        #[Max(30)]
        public string $event_name,

        #[OA\Property( ref: UserNamespaceData::class, title: "Namespace")]
        #[AutoWhenLoadedLazy]
        public UserNamespaceData|Lazy $owning_namespace,

        #[AutoLazy]
        public Lazy|int $id ,

        #[OA\Property( title: 'Created',type: 'string', format: 'datetime',example: "2025-02-25T15:00:59-06:00",nullable: true)]
        #[WithCast(DateTimeInterfaceCast::class, format: DATE_ATOM)]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM, setTimeZone: AttributeConstants::OUTPUT_TIMEZONE)]
        public ?Carbon $created_at = null,


        #[OA\Property( title: 'Updated', type: 'string',format: 'datetime',example: "2025-03-25T15:00:59-06:00",nullable: true)]
        #[WithCast(DateTimeInterfaceCast::class, format: DATE_ATOM)]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM, setTimeZone: AttributeConstants::OUTPUT_TIMEZONE)]
        public ?Carbon $updated_at = null,

    ) {

    }

}
