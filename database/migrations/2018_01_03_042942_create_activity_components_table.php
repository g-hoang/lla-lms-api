<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_components', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('activity_id')->unsigned();
            $table->enum('component_type', ['IMAGE', 'AUDIO', 'TEXT_BLOCK', 'MCQ', 'TEXT_INPUT', 'TEXT_OUTPUT']);
            $table->integer('order')->default(0);
            $table->text('data');
            $table->timestamps();

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
        Schema::dropIfExists('activity_components');
    }
}
