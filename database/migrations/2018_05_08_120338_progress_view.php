<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProgressView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE VIEW vProgress AS 
            select
            `id`,
            `learner_id`,
            `course_id`,
             `unit_id`,
            `unit_index`,
            `lesson_id`,
            `lesson_index`,
            `activity_id`,
            `activity_index`,
            `exit_event`,
            `updated_at`
            from `learner_progress`
            where (`exit_event` not in ('EXIT','EXPIRED','INCOMPLETE')) 
            group by `learner_id`, `activity_id` 
            order by `unit_index`,`lesson_index`,`activity_index`,`updated_at` asc;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW vProgress");
    }
}
