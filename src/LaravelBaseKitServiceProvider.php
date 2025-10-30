<?php

namespace Naimul007A\LaravelBaseKit;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Naimul007A\LaravelBaseKit\Commands\LaravelBaseKitCommand;

class LaravelBaseKitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-base-kit')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_base_kit_table')
            ->hasCommand(LaravelBaseKitCommand::class);
    }
}
