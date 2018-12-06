<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProgressHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('progress_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('learner_id');
            $table->morphs('completed');
            $table->text('meta_data')->nullable();
            $table->unsignedInteger('attempt');
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
        Schema::dropIfExists('progress_histories');
    }
}
