<?php
/**
 * Created by PhpStorm.
 * User: kenath
 * Date: 12/19/2017
 * Time: 10:14 AM
 */

namespace App\Linkup\Search;

use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind('search', Search::class);
    }

}