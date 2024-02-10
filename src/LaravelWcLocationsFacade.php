<?php

namespace Rubloge\LaravelWcLocations;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rubloge\LaravelWcLocations\Skeleton\SkeletonClass
 */
class LaravelWcLocationsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-wc-locations';
    }
}
