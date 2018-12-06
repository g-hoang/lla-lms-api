<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityResponses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('learner_id')->unsigned();
            $table->integer('activity_id')->unsigned();
            $table->text('data');
            $table->timestamps();

            $table->foreign('learner_id')
                ->references('id')
                ->on('learners')
                ->onDelete('cascade');

            $table->foreign('activity_id')
                ->references('id')
                ->on('activities')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_responses');
    }
}
