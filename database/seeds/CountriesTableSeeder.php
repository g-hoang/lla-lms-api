<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('countries')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $countries = [
            ['name' => 'Malta', 'dialling_code' => '+356'],
            ['name' => 'Sri Lanka', 'dialling_code' => '+94'],
            ['name' => 'United Kingdom', 'dialling_code' => '+44'],
            ['name' => 'Vietnam', 'dialling_code' => '+84']
        ];

        DB::table('countries')->insert($countries);

    }
}
