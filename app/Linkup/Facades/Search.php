<?php
/**
 * Created by PhpStorm.
 * User: kenath
 * Date: 12/19/2017
 * Time: 10:13 AM
 */
namespace App\Linkup\Facades;

use Illuminate\Support\Facades\Facade;

class Search extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'search';
    }

}