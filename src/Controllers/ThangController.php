<?php

namespace Hexbatch\Thangs\Controllers;

use App\Helpers\Utilities;
use App\Models\UserNamespace;
use Hexbatch\Thangs\Callables\CallableStub;
use Hexbatch\Thangs\Data\Params\ManualCallbackParams;
use Hexbatch\Thangs\Data\ThangCommandData;
use Hexbatch\Thangs\Data\ThangData;
use Hexbatch\Thangs\Enums\TypeOfThangAsyncPolicy;
use Hexbatch\Thangs\Enums\TypeOfThangSavePolicy;
use Hexbatch\Thangs\Helpers\ThangBuilder;

use Hexbatch\Thangs\Helpers\ThangTree;
use Hexbatch\Thangs\Models\Thang;
use Hexbatch\Thangs\Models\ThangCallback;
use Hexbatch\Thangs\Models\ThangCommand;

use OpenApi\Attributes as OA;
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

    public function show_command(ThangCommand $cmd) {
        $data = ThangCommandData::from($cmd);
        return response()->json($data);
    }

    public function show_thang(UserNamespace $namespace,Thang $thang) {
        $thang->sid = true;
        Utilities::ignoreVar($namespace);
        $data = ThangData::from($thang);
        return response()->json($data);
    }


    /**
     * @throws \Throwable
     */
    public function complete_callback(UserNamespace $namespace, ThangCallback $callback,ManualCallbackParams $params) {
        $thang_tree = ThangBuilder::createBuilder()
            ->setNamespace($namespace)
            ->bubbleExceptions()
            ->eatManualCallback(callback: $callback,http_code:  $params->callback_http_code ,data: $params->data??[]);

        return response()->json([
            'errors'=>ThangCommand::getErrorArrayFromCollection($thang_tree->getAllCommands()),
            'commands'=>  ThangCommand::nestCollection($thang_tree->getAllCommands()),
            'thang'=>$thang_tree->getThang(),
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function test_data() {
        $uuid = "e4e33817-3e5e-4f44-a52d-b2982f6a17b6";
        $tree = ThangTree::makeThangTree(thang: $uuid);
        return response()->json($tree->getThang());
    }

    public function test_data2() {
        $uuid = "e4e33817-3e5e-4f44-a52d-b2982f6a17b6";
        $thang = Thang::where('ref_uuid',$uuid)
            ->with([
                'commands', 'owning_namespace',
                'commands.command_callbacks','commands.command_callbacks.owning_hook'
            ])
            ->first();
        // return response()->json($thang);
        $thang->sid = true;
        $data = ThangData::from($thang)->include('id','owning_namespace.id','owning_namespace_id');
        return response()->json($data);
    }

    public function test() {



        $builder = ThangBuilder::createBuilder()
            ->bubbleExceptions()
            ->addParent(command_class: CallableStub::class, is_async: false, command_args: ['peanut_butter'=>1], command_tags: ['post-test','pre-test','test'])

            ->addChild(command_class: CallableStub::class,command_args: ['apples'=>'granny smith'],command_tags: ['post-test','pre-test','lost'])
            ->addParent(command_class: CallableStub::class,command_args: ['More_peanut_butter'=>3],command_tags: ['post-test','pre-test','lost-land'])//
            ->addChild(command_class: CallableStub::class,command_args: ['lakes'=>'red'],command_tags: ['post-test','pre-test','lost-land-dino'])//
            ->end()
            ->setNamespace(Utilities::getCurrentNamespace())
            ->setSavePolicy(TypeOfThangSavePolicy::ALWAYS_SAVE)
            ->setAsyncPolicy(TypeOfThangAsyncPolicy::ALWAYS_ASYNC)
            ;
       // $what = $builder->showTree();
        $thang_tree = $builder->execute();

        return response()->json([
            'errors'=>ThangCommand::getErrorArrayFromCollection($thang_tree->getAllCommands()),
            'commands'=>  ThangCommand::nestCollection($thang_tree->getAllCommands()),
            'thang'=>$thang_tree->getThang(),
        ]);
    }

}
