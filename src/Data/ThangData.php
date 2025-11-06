<?php

namespace Hexbatch\Thangs\Data;


use App\Data\ApiParams\Data\Namespaces\UserNamespaceData;
use App\Helpers\AttributeConstants;
use Carbon\Carbon;
use Hexbatch\Thangs\Enums\TypeOfThangAsyncPolicy;
use Hexbatch\Thangs\Enums\TypeOfThangSavePolicy;
use Hexbatch\Thangs\Models\Thang;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
#[OA\Schema(schema: 'Thang')]
class ThangData extends Data
{

    public function __construct(


        public null|int|Optional|Lazy $id,


        public int|null|Optional|Lazy $owning_namespace_id,

        #[OA\Property( title: 'Created', type: 'string',format: 'datetime',example: "2025-02-25T15:00:59-06:00",nullable: true)]
        #[WithCast(DateTimeInterfaceCast::class, format: DATE_ATOM)]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM, setTimeZone: AttributeConstants::OUTPUT_TIMEZONE)]
        public ?Carbon $created_at,


        #[OA\Property( title: 'Updated', type: 'string',format: 'datetime',example: "2025-03-25T15:00:59-06:00",nullable: true)]
        #[WithCast(DateTimeInterfaceCast::class, format: DATE_ATOM)]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM, setTimeZone: AttributeConstants::OUTPUT_TIMEZONE)]
        public ?Carbon $updated_at,

        #[OA\Property( title:"Uuid",format: 'uuid')]
        #[Uuid]
        public string $ref_uuid,

        #[OA\Property( title:"Finished data", items: new OA\Items(),nullable: true)]
        /** @var mixed[] $finished_data */
        public ?array $finished_data,


        #[OA\Property( title:"Save policy")]
        public TypeOfThangSavePolicy $thang_save_policy,

        #[OA\Property( title:"Async policy")]
        public TypeOfThangAsyncPolicy $thang_async_policy,

        #[OA\Property( ref: UserNamespaceData::class, title: "Namespace")]

        public Optional|UserNamespaceData $owning_namespace,


        #[OA\Property( title: "Commands",type: 'array',items:  new OA\Items(ref: ThangCommandData::class))]
        #[WithoutValidation]
        public Optional|Collection|DataCollection $commands
    ) {

    }



    public static function fromModel(Thang $thang) : self
    {


        $data =  [
            'id'=> Lazy::create(fn() => $thang->id) ,
            'owning_namespace_id'=> Lazy::create(fn() => $thang->owning_namespace_id),
            'ref_uuid'=> $thang->ref_uuid,
            'finished_data'=> $thang->finished_data,
            'thang_save_policy'=> $thang->thang_save_policy,
            'thang_async_policy'=> $thang->thang_async_policy,

            'created_at'=> Carbon::parse($thang->created_at),
            'updated_at'=> Carbon::parse($thang->updated_at),
        ];

        if ($thang->relationLoaded('owning_namespace')) {
            $data['owning_namespace'] = $thang->owning_namespace;
        }

        if ($thang->relationLoaded('commands')) {
            $data['commands'] = ThangCommandData::collect($thang->commands, DataCollection::class)
                ->includeWhen('id',fn() => $thang->sid)
                ->includeWhen('parent_id',fn() => $thang->sid)
                ->includeWhen('owning_thang_id',fn() => $thang->sid)
            ;
        }

        return self::from($data);

    }




}
