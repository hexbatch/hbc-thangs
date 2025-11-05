<?php

namespace Hexbatch\Thangs\Controllers;

use App\OpenApi\ErrorResponse;
use Hexbatch\Things\Enums\TypeOfCallbackStatus;
use Hexbatch\Things\Enums\TypeOfOwnerGroup;
use Hexbatch\Things\Enums\TypeOfThingStatus;
use Hexbatch\Things\Exceptions\HbcThingException;
use Hexbatch\Things\Interfaces\IThingOwner;
use Hexbatch\Things\Models\Thing;
use Hexbatch\Things\Models\ThingCallback;
use Hexbatch\Things\Models\ThingHook;
use Hexbatch\Things\OpenApi\Callbacks\CallbackCollectionResponse;
use Hexbatch\Things\OpenApi\Callbacks\CallbackResponse;
use Hexbatch\Things\OpenApi\Callbacks\CallbackSearchParams;
use Hexbatch\Things\OpenApi\Callbacks\ManualParams;
use Hexbatch\Things\OpenApi\Errors\ThingErrorCollectionResponse;
use Hexbatch\Things\OpenApi\Hooks\HookCollectionResponse;
use Hexbatch\Things\OpenApi\Hooks\HookParams;
use Hexbatch\Things\OpenApi\Hooks\HookResponse;
use Hexbatch\Things\OpenApi\Hooks\HookSearchParams;
use Hexbatch\Things\OpenApi\Things\ThingCollectionResponse;
use Hexbatch\Things\OpenApi\Things\ThingResponse;
use Hexbatch\Things\OpenApi\Things\ThingSearchParams;
use Hexbatch\Things\Requests\CallbackSearchRequest;
use Hexbatch\Things\Requests\HookRequest;
use Hexbatch\Things\Requests\HookSearchRequest;
use Hexbatch\Things\Requests\ManualFillRequest;
use Hexbatch\Things\Requests\ThingSearchRequest;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\HttpFoundation\Response as CodeOf;

class ThangController  {

    #[OA\Get(
        path: '/api/hbc-thangs/v1/tests/health',
        operationId: 'hbc-thangs.tests.health',
        description: "",
        summary: 'Shows the health',
        security: [['bearerAuth' => []]],
        tags: ['health'],
        responses: [
            new OA\Response( response: CodeOf::HTTP_OK, description: 'Its alive!'),
            new OA\Response( response: CodeOf::HTTP_SERVICE_UNAVAILABLE, description: 'Something is wrong')
        ]
    )]
    public function health() {

        return response()->json([], CodeOf::HTTP_OK);
    }

}
