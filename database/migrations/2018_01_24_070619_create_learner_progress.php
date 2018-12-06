<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLearnerProgress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('exit_event_type', ['INCOMPLETE', 'MAX_ATTEMPTS_EXCEEDED', 'MAX_TIME_EXCEEDED', 'AUTO_ADVANCED', 'COMPLETED']);
            $table->integer('attempts')->default(0);
            $table->integer('scorable_components')->default(0);
            $table->integer('scorable_correct')->default(0);
            $table->integer('scorable_wrong')->default(0);
            $table->boolean('is_part_of_assessment')->default(false);

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
