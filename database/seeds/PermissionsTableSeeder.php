<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('permissions')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $permissions = [
            'user.list',
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'course.list',
            'course.view',
            'course.create',
            'course.update',
            'course.delete',
            'unit.list',
            'unit.view',
            'unit.create',
            'unit.update',
            'unit.delete',
            'lesson.list',
            'lesson.view',
            'lesson.create',
            'lesson.update',
            'lesson.delete',
            'activity.list',
            'activity.view',
            'activity.create',
            'activity.update',
            'activity.delete',
            'learner.list',
            'learner.view',
            'learner.create',
            'learner.update',
            'learner.delete',
        ];

        foreach ($permissions as $name) {
            DB::table('permissions')->insert(
                [ 'name' => $name ]
            );
        }
    }
}
