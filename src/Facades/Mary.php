<?php

namespace Mary\Facades;

use Illuminate\Support\Facades\Facade;

class Mary extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'mary';
    }
}
