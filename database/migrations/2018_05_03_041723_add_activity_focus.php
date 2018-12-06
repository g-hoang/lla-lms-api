<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActivityFocus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE activities CHANGE COLUMN focus focus ENUM('Intro', 'Vocabulary', 'Pronunciation', 'Listening', 'Reading', 'Speaking', 'Writing', 'Grammar', 'General', 'Functional Language', 'Exam Practice')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE activities CHANGE COLUMN focus focus ENUM('Intro', 'Vocabulary', 'Pronunciation', 'Listening', 'Reading', 'Speaking', 'Writing', 'Grammar', 'General')");
    }
}
