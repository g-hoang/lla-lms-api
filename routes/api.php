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
Route::post('auth/login', 'AuthController@login');

Route::post('auth/forgot-password', 'AuthController@forgotPassword');

Route::post('auth/setup-password', 'UserController@activate');
Route::get('auth/reset-password', 'AuthController@resetPasswordHandler');
Route::post('auth/reset-password', 'AuthController@resetPassword');



if (env('APP_ENV') != 'production') {
    Route::get('qa/generate/{type}/{count?}', 'QAController@createDummyContent');
    Route::get('qa/clean/{type}', 'QAController@clean');
}


Route::group(
    ['middleware' => ['before' => 'jwt-auth']],
    function () {

        // AUTH & User
        Route::get('auth/logout', 'AuthController@logout');
        Route::get('me', 'AuthController@me');

        // learner
        Route::resource(
            '/learner',
            'LearnerController',
            ['except' => ['edit', 'create']]
        );

        Route::resource(
            '/user',
            'UserController',
            ['except' => ['edit', 'create']]
        );
        Route::post('user/{id}/update-status', 'UserController@updateStatus');

        // Courses
        Route::resource(
            '/course',
            'CourseController',
            ['except' => ['edit', 'create']]
        );
		Route::post('course/{id}/update-status', 'CourseController@updateStatus');
        // Units
        Route::resource(
            '/unit',
            'UnitController',
            ['except' => ['edit', 'create', 'index']]
        );
        Route::get('course/{id}/units', 'UnitController@index');
        Route::post('unit/{id}/set-order', 'UnitController@updateOrder');
        Route::post('unit/f2f-status/{id}', 'UnitController@setF2FClassStatus');

        // Lessons
        Route::resource(
            '/lesson',
            'LessonController',
            ['except' => ['edit', 'create', 'index']]
        );
        Route::get('unit/{id}/lessons', 'LessonController@index');
        Route::post('lesson/{id}/set-order', 'LessonController@updateOrder');

        // Activities
        Route::resource(
            '/activity',
            'ActivityController',
            ['except' => ['edit', 'create', 'index']]
        );
        Route::get('lesson/{id}/activities', 'ActivityController@index');
        Route::post('activity/{id}/set-order', 'ActivityController@updateOrder');
        Route::get('activity/{id}/components', 'ActivityController@components');

        //Lesson Types
        Route::get('lesson-types', 'LessonTypeController@index');

        // Activity Components
        Route::resource(
            '/component',
            'ActivityComponentController',
            ['except' => ['edit', 'create', 'index']]
        );
        Route::get('activity/{id}/components', 'ActivityComponentController@index');
        Route::post('component/set-order', 'ActivityComponentController@updateOrder');
        Route::get('component/get-text-input-ids-for-lesson/{id}', 'ActivityComponentController@getTextInputIdsForLesson');
        Route::post('component/{id}/copy', 'ActivityComponentController@copyComponent');
        Route::post('component/{id}/move', 'ActivityComponentController@moveComponent');

        //Language Focus Types
        Route::get('language-focus-types', 'ActivityController@languageFocusTypes');

        Route::get('country', 'CountryController@index');

        Route::get('language', 'LanguageController@index');

        Route::get('activity/{id}/text-outputs', 'ActivityController@textOutputs');

        //Progress
        Route::get('learner/{id}/progress', 'ProgressController@progress');

        Route::post('learner/resend-activation', 'LearnerController@resendActivationEmail');

        Route::post('user/resend-activation', 'UserController@resendActivationEmail');
    }
);
