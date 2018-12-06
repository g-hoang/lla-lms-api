<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LessonTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('lesson_types')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $countries = [
            'Lesson', 'Extra practice', 'Exam preparation', 'End-of-unit test'
        ];

        foreach ($countries as $name) {
            DB::table('lesson_types')->insert(
                [ 'name' => $name ]
            );
        }
    }
}
