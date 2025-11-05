<?php
namespace Hexbatch\Thangs;


use Hexbatch\Thangs\Actions\TestCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;


class HexbatchThangsProvider extends PackageServiceProvider
{

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

            ->runsMigrations()
            ;



    }



    /**
     * called when the package is fully ready for use, each time the laravel code runs
     * @return $this
     */
    public function packageBooted()
    {


        return $this;
    }

}
