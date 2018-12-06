<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLearnerlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('learnerlogs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('learner_id')->unsigned();
            $table->integer('foreign_key')->unsigned();
            $table->string('event');
            $table->text('details');
            $table->timestamps();

            $table->foreign('learner_id')
                ->references('id')
                ->on('learners')
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
        Schema::dropIfExists('learnerlogs');
    }
}
