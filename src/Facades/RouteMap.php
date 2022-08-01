<?php

namespace Chronos\Facades;

use Illuminate\Support\Facades\Facade;

class RouteMap extends Facade
{
    /**
     * Get the registered component.
     *
     * @return object
     */
    protected static function getFacadeAccessor()
    {
        return 'routeMap';
    }
}