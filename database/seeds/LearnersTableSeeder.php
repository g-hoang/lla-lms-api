<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LearnersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('learners')->insert(
            [
                [
                    'firstname' => "Languagelink",
                    'lastname' => "Learner",
                    'email' => 'learner@languagelink.com',
                    'password' => bcrypt('teachme!'),
                    'status' => 'REGISTERED',
                    'is_active' => 1,
                    'center_id' => 1,
                    'language_id' => 1,
                    'country_id' => 4,
                    'dialingcode' => '',
                    'phone' => '',
                    'address1' => '',
                    'address2' => '',
                    'town' => '',
                    'county' => '',
                    'zip' => '',
                    'last_login' => '2018-01-01 12:00:00'
                ]
            ]
        );

        DB::table('course_learners')->insert(
            [
                [
                    'learner_id' => 1,
                    'course_id' => 1,
                    'is_active' => true
                ]
            ]
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // factory(App\Models\Learner::class, 55)->create();
    }
}
