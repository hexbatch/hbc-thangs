<?php

use Hexbatch\Things\Controllers\ThingController;
use Illuminate\Support\Facades\Route;


Route::prefix('hbc-thangs')->group(function () {

    $hbc_middleware = [];
    $my_auth = config('hbc-thangs.middleware.auth_alias'); //this decides if the owner type/id is valid
    if ($my_auth) {
        $hbc_middleware[] =  $my_auth;
    }

    $my_user = config('hbc-thangs.middleware.owner_alias'); //this sets the IThingOwner
    if ($my_user) {
        $hbc_middleware[] =  $my_user;
    }

    $hbc_admin = [];
    $my_admin = config('hbc-thangs.middleware.owner_alias'); //this decides if the logged-in user can do sensitive ops
    if ($my_admin) {
        $hbc_admin[] =  $my_admin;
    }


    $hbc_thing_viewable = [];
    $my_thing_viewable = config('hbc-thangs.middleware.thang.viewable_alias'); //view thing
    if ($my_thing_viewable) {
        $hbc_thing_viewable[] =  $my_thing_viewable;
    }

    $hbc_thing_editable = [];
    $my_thing_editable = config('hbc-thangs.middleware.thang.editable_alias'); //edit thing
    if ($my_thing_editable) {
        $hbc_thing_editable[] =  $my_thing_editable;
    }








    $hbc_hook_viewable = [];
    $my_hook_viewable = config('hbc-thangs.middleware.hook_viewable_alias');
    if ($my_hook_viewable) {
        $hbc_hook_viewable[] =  $my_hook_viewable;
    }


    $hbc_hook_editable = [];
    $my_hook_editable = config('hbc-thangs.middleware.hook_editable_alias');
    if ($my_hook_editable) {
        $hbc_hook_editable[] =  $my_hook_editable;
    }


    $hbc_callback_viewable = [];
    $my_thing_callback_view = config('hbc-thangs.middleware.callback_viewable_alias'); //callback viewable
    if ($my_thing_callback_view) {
        $hbc_callback_viewable[] =  $my_thing_callback_view;
    }

//v1/callbacks/manual/8cd6e99a-ca46-4c2a-82d3-db8cdd780dab/question
    Route::prefix('v1')->group(function ()
        use($hbc_middleware,$hbc_admin,
            $hbc_thing_viewable,$hbc_thing_editable,
            $hbc_callback_viewable,
            $hbc_hook_viewable,$hbc_hook_editable)
    {
        Route::prefix('tests')->group(function ()
        {

            Route::middleware([])->prefix('health')->group(function()
            {
                Route::get('/', [\Hexbatch\Thangs\Controllers\ThangController::class, 'health'])->name('hbc-thangs.tests.health');

            }); //tests
        }); //thangs
    }); //v1
}); //outer

