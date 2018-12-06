<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('languages')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $languages = [
            ['name' => 'Vietnamese', 'locale' => 'vi'],
            ['name' => 'English', 'locale' => 'en']
        ];

        DB::table('languages')->insert($languages);

    }
}
