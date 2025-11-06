<?php

namespace Hexbatch\Thangs\Data\Params;


use Illuminate\Support\Optional;
use OpenApi\Attributes as OA;
use Spatie\LaravelData\Attributes\MergeValidationRules;
use Spatie\LaravelData\Attributes\Validation\Max;

use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[OA\Schema(schema: 'ManualCallbackParams',description: "When completing a manual thang callback")]
#[TypeScript]
#[MergeValidationRules]
class ManualCallbackParams extends Data
{

    public function __construct(

        #[Min(0),Max(599)]
        #[OA\Property( title: 'Http code', description: "set a 2xx for successful, 4xx for failing, 5xx for an error", maximum: 599, minimum: 0, example: "200")]
        public int $callback_http_code,


        #[OA\Property( title:"Data", description: "Optionally pass back data",type: 'array',items: new OA\Items(),nullable: true)]
        /** @var mixed[] $data */
        public array|Optional $data


    ) {

    }


}
