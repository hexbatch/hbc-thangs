<?php
namespace Hexbatch\Thangs;


use Hexbatch\Thangs\Actions\CommandLine\TestCommand;

use Hexbatch\Thangs\Interfaces\IHookEventCallable;
use Hexbatch\Thangs\Models\Thang;
use Hexbatch\Thangs\Models\ThangCallback;
use Hexbatch\Thangs\Models\ThangCommand;
use Hexbatch\Thangs\Models\ThangHook;
use Hexbatch\Thangs\Traits\SearchFiles;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TorMorten\Eventy\Facades\Eventy;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;



class HexbatchThangsProvider extends PackageServiceProvider
{
    use SearchFiles;
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */


        $package
            ->name('hbc-thangs')
            ->hasCommand(TestCommand::class)
            ->hasConfigFile()
            ->discoversMigrations()
            ->hasTranslations()
            ->runsMigrations()
            ;



    }


    const string HOOK_DIRECTORIES_EVENT = 'thang-hook-event-directories';
    const string HOOK_CREATION_EVENT = 'thang-hook-creation-event';

    /**
     * called when the package is fully ready for use, each time the laravel code runs
     * @return $this
     */
    public function packageBooted()
    {
        Route::model('thang', Thang::class);
        Route::model('thang_command', ThangCommand::class);
        Route::model('thang_callback', ThangCallback::class);
        Route::model('thang_hook', ThangHook::class);


        Eventy::addFilter(static::HOOK_DIRECTORIES_EVENT, function(array $dirs)  {
            $dirs[] = __DIR__. DIRECTORY_SEPARATOR . 'Actions/Events';
            return $dirs;
        });


        Eventy::addFilter(static::HOOK_CREATION_EVENT, function(array $dirs)  {
            $dirs[] = __DIR__. DIRECTORY_SEPARATOR . 'Seeds/Hooks';
            return $dirs;
        });

        $this->registerEvents();
        return $this;
    }

    /**
     * @return IHookEventCallable|null
     */
    protected static function castClassNameToEventCallable(string $full_class_name)  {
        $interfaces = class_implements($full_class_name);
        if (isset($interfaces['Hexbatch\Thangs\Interfaces\IHookEventCallable'])) {
            /** @type IHookEventCallable */
            return $full_class_name;
        }
        return null;
    }



    protected function registerEvents(){
        $dirs = Eventy::filter(static::HOOK_DIRECTORIES_EVENT,[]);
        foreach ($dirs as $dir) {
            $directory = new RecursiveDirectoryIterator($dir);
            $flattened = new RecursiveIteratorIterator($directory);
            $files = new RegexIterator($flattened, '#\.(?:php)$#Di');
            foreach($files as $file) {
                $namespace = static::extract_namespace($file);
                $class = basename($file, '.php');
                $full_class_name = $namespace . '\\' .$class;
                if( $dodge = static::castClassNameToEventCallable($full_class_name)) {
                    $dodge::registerEvent();
                }
            }
        }
    }





}
