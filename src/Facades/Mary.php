<?php

namespace Robsontenorio\Mary\Facades;

use Illuminate\Support\Facades\Facade;

class Mary extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'mary';
    }
}
