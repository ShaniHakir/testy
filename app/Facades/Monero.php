<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Monero extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'monero.rpc';
    }
}
