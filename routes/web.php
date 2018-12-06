<?php

Route::get('user/register', 'UserController@register');

Route::get('learner/reset-password', 'Learner\AuthController@resetPasswordHandler');
