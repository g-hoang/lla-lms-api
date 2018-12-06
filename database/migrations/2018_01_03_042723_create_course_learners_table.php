<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCourseLearnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_learners', function (Blueprint $table) {
            $table->integer('learner_id')->unsigned();
            $table->integer('course_id')->unsigned();
            $table->timestamps();

            $table->foreign('learner_id')
                ->references('id')
                ->on('learners')
                ->onDelete('cascade');

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('cascade');

            $table->primary(['learner_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_learners');
    }
}
