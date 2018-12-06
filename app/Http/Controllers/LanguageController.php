<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use App\Http\Resources\Language as LanguageResource;

class LanguageController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $countries = Language::orderBy('name', 'ASC')->get();

        return LanguageResource::collection($countries);
    }

}
