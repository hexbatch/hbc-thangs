<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware;

Route::prefix('hbc-thangs')->group(function () {



    Route::prefix('v1')->group(function ()
    {
        Route::middleware('auth:sanctum')->group(function ()
        {


            Route::prefix('tests')->group(function ()
            {
                Route::middleware(Middleware\ValidateNamespaceMember::class)->prefix('{user_namespace}')->group( function ()
                {

                    Route::post('/', [\Hexbatch\Thangs\Controllers\ThangController::class, 'test'])->name('hbc-thangs.tests.index');
                    Route::get('data', [\Hexbatch\Thangs\Controllers\ThangController::class, 'test_data'])->name('hbc-thangs.tests.data');
                    Route::get('data2', [\Hexbatch\Thangs\Controllers\ThangController::class, 'test_data2'])->name('hbc-thangs.tests.data2');
                });

                Route::middleware([])->prefix('health')->group(function ()
                {
                    Route::get('/', [\Hexbatch\Thangs\Controllers\ThangController::class, 'health'])->name('hbc-thangs.tests.health');
                });
            }); //tests


            Route::prefix('commands')->group(function () {
                Route::middleware(Middleware\ValidateNamespaceMember::class)->prefix('{user_namespace}')->group(function () {
                    Route::prefix('command/{thang_command}')->group(function () {
                        Route::get('show', [\Hexbatch\Thangs\Controllers\ThangController::class, 'show_command'])->name('hbc-thangs.commands.show');
                    });

                });
            });

            Route::prefix('thangs')->group(function () {
                Route::middleware(Middleware\ValidateNamespaceMember::class)->prefix('{user_namespace}')->group(function () {
                    Route::prefix('thang/{thang}')->group(function () {
                        Route::get('show', [\Hexbatch\Thangs\Controllers\ThangController::class, 'show_thang'])->name('hbc-thangs.thangs.show');
                    });

                });
            });

            Route::prefix('callbacks')->group(function () {
                Route::middleware(Middleware\ValidateNamespaceAdmin::class)->prefix('{user_namespace}')->group(function () {
                    Route::prefix('thang/{thang_callback}')->group(function () {
                        Route::post('complete', [\Hexbatch\Thangs\Controllers\ThangController::class, 'complete_callback'])->name('hbc-thangs.callbacks.complete');
                    });

                });
            });

        }); //auth
    }); //v1
}); //outer

