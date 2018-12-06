<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCenteresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('centers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('address1', 255)->nullable();
            $table->string('address2', 255)->nullable();
            $table->string('town')->nullable();
            $table->string('county')->nullable();
            $table->integer('country_id')->unsigned();
            $table->string('email', 191)->unique();
            $table->string('dialingcode', 50)->nullable();
            $table->string('phone', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
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
        Schema::dropIfExists('centers');
    }
}
