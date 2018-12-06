<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lesson_id')->unsigned();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->enum('focus', ['Intro', 'Vocabulary', 'Pronunciation', 'Listening', 'Reading', 'Speaking', 'Writing', 'Grammar', 'General']);
            $table->integer('order')->default(0);
            $table->integer('max_attempts')->default(0);
            $table->integer('max_time')->default(0);
            $table->integer('auto_advance_timer')->default(0);
            $table->boolean('is_optional')->default(false);

            $table->timestamps();

            $table->foreign('lesson_id')
                ->references('id')
                ->on('lessons')
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
        Schema::dropIfExists('activities');
    }
}
