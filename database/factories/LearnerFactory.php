<?php

use Faker\Generator as Faker;
use App\Models\Learner as Learner;
use App\Models\Center as Center;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Learner::class, function (Faker $faker) {
    static $password;

    return [
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'country_id' => 1,
        'status' => 'INVITED',
        'center_id' => Center::all()->random()->id,
        'language_id' => 1
    ];

});
