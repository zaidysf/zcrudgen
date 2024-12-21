<?php

namespace ZaidYasyaf\Zcrudgen\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ZaidYasyaf\Zcrudgen\Zcrudgen
 */
class Zcrudgen extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \ZaidYasyaf\Zcrudgen\Zcrudgen::class;
    }
}
