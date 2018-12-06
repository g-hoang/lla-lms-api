<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Http\Resources\Country as CountryResource;
use Illuminate\Http\Request;

class CountryController extends ApiController
{
    /**
     * Countries list.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $countries = Country::orderBy('name', 'ASC')->get();

        return CountryResource::collection($countries);
    }

}
