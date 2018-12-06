<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LearnerProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('learner_progress');

        Schema::create('learner_progress', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('learner_id');
            $table->string('learner_name');
            $table->string('learner_email');
            $table->integer('course_id');
            $table->string('course_name');
            $table->integer('unit_id');
            $table->integer('unit_index');
            $table->string('unit_title');
            $table->integer('lesson_id');
            $table->integer('lesson_index');
            $table->string('lesson_title');
            $table->integer('activity_id');
            $table->integer('activity_index');
            $table->string('activity_title');
            $table->string('activity_focus');
            $table->boolean('is_part_of_assessment')->default(false);
            $table->boolean('is_optional')->default(false);
            $table->enum('exit_event', ['INCOMPLETE', 'EXPIRED', 'EXIT','SKIPPED', 'MAX_ATTEMPTS_EXCEEDED', 'MAX_TIME_EXCEEDED', 'AUTO_ADVANCED', 'COMPLETED']);

            $table->integer('max_time')->default(0);
            $table->dateTime('expiry_time')->nullable();
            $table->integer('max_attempts')->default(0);
            $table->integer('attempts')->default(0);
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('scorable_components')->default(0);
            $table->integer('scorable_correct')->default(0);
            $table->integer('scorable_wrong')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('learner_progress');
    }
}
