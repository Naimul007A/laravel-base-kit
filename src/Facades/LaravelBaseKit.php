<?php

namespace Naimul007A\LaravelBaseKit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Naimul007A\LaravelBaseKit\LaravelBaseKit
 */
class LaravelBaseKit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Naimul007A\LaravelBaseKit\LaravelBaseKit::class;
    }
}
