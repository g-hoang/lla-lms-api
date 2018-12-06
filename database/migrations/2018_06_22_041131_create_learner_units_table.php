<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLearnerUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('learner_units', function (Blueprint $table) {
            $table->integer('learner_id')->unsigned();
            $table->integer('course_id')->unsigned();
            $table->integer('unit_id')->unsigned();
            $table->integer('order')->unsigned();
            $table->timestamps();

            $table->foreign('learner_id')
                ->references('id')
                ->on('learners')
                ->onDelete('cascade');

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->onDelete('cascade');

            $table->primary(['learner_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('learner_units');
    }
}
