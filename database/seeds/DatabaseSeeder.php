<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PermissionsTableSeeder::class);
        $this->call(UsersTableSeeder::class);

        $this->call(CountriesTableSeeder::class);
        $this->call(LessonTypesTableSeeder::class);
        $this->call(LanguagesTableSeeder::class);
        $this->call(CentersTableSeeder::class);

        $this->call(LearnersTableSeeder::class);

    }
}
