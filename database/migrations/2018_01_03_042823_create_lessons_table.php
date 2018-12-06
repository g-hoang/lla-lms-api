<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('unit_id')->unsigned();
            $table->integer('lesson_type_id')->unsigned();
            $table->string('title');
            $table->text('language_focus')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_optional')->default(false);
            $table->timestamps();

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->onDelete('cascade');

            $table->foreign('lesson_type_id')
                ->references('id')
                ->on('lesson_types')
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
        Schema::dropIfExists('lessons');
    }
}
