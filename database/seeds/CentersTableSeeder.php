<?php

use Illuminate\Database\Seeder;

class CentersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('centers')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('centers')->insert(
            [
                [
                    'name' => "Vietnam",
                    'country_id' => 4,
                    'is_active' => 1,
                    'email' => 'vietnam@languagelink.com',
                    'address1' => '',
                    'address2' => '',
                    'town' => '',
                    'county' => '',
                    'dialingcode' => '',
                    'phone' => ''
                ]
            ]
        );
    }
}
