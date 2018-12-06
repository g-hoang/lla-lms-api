<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* Public Routes */
Route::post('login', 'AuthController@login');

Route::post('password/forgot', 'AuthController@forgotPassword');
Route::post('password/reset', 'AuthController@resetPassword');

/* Learner Activation */
Route::get('register', 'AuthController@register');
Route::post('setup-password', 'AuthController@activate');



Route::group(
    ['middleware' => ['before' => 'jwt']],
    function () {
        // AUTH & User
        Route::get('logout', 'AuthController@logout');
        Route::get('me', 'AuthController@me');

        Route::get('course/{unit_id?}', 'Controller@index');
        Route::get('unit/{unit_id}', 'Controller@unit');
        Route::get('lesson/{lesson_id}', 'Controller@lesson');
        Route::get('lesson/skip/{lesson_id}', 'Controller@skipLesson');

        Route::get('activity/{id}', 'ActivityController@index');
        Route::post('activity/{id}', 'ActivityController@check');
        Route::get('activity/answers/{id}', 'ActivityController@answers');

        Route::get('lesson/progress/{id}', 'LessonController@progress');


        //Progress Report
        Route::get('progress', 'ProgressController@progress');

        // Progress Tracking
        Route::post('progress/{id}', 'TrackController@track');

        // TEST PURPOSE ONLY (DEV & STAGING)
        Route::get('progress/reset', 'Controller@clearProgressData');
    }
);
