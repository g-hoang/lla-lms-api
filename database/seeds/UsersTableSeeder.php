<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('roles')->truncate();
        DB::table('permission_role')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $roles = [
            'admin',
            'local-admin',
            'content-editor',
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert(
                [ 'name' => $role ]
            );
        }

        DB::table('users')->insert(
            [
                [
                    'firstname' => "Linkup",
                    'lastname' => "Admin",
                    'email' => 'chathura@ceylonit.com',
                    'password' => bcrypt('linkup@admin'),
                    'role_id' => 1,
                    'status' => 'REGISTERED'
                ],
                [
                    'firstname' => "John",
                    'lastname' => "Doe",
                    'email' => 'admin@languagelink.com',
                    'password' => bcrypt('linkup@admin'),
                    'role_id' => 1,
                    'status' => 'REGISTERED'
                ]
            ]
        );

        //factory(App\Models\User::class, 55)->create();
    }
}
