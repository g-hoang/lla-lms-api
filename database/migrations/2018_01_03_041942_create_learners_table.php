<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLearnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('learners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email', 191)->unique();
            $table->string('password')->nullable();
            $table->string('dialingcode')->nullable();
            $table->string('phone')->nullable();
            $table->string('address1', 255)->nullable();
            $table->string('address2', 255)->nullable();
            $table->string('town')->nullable();
            $table->string('county')->nullable();
            $table->integer('country_id')->unsigned();
            $table->string('zip')->nullable();
            $table->enum('status', ['INVITED', 'REGISTERED'])
                ->default('INVITED');
            $table->boolean('is_active')->default(true);
            $table->boolean('has_fullaccess')->default(false);
            $table->integer('center_id')->unsigned();
            $table->integer('language_id')->unsigned();
            $table->string('email_token', 255)->nullable();
            $table->string('api_token', 255)->nullable();
            $table->text('latest_jwt_claims')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->timestamps();

            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->onDelete('cascade');

            $table->foreign('center_id')
                ->references('id')
                ->on('centers')
                ->onDelete('cascade');

            $table->foreign('language_id')
                ->references('id')
                ->on('languages')
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
        Schema::dropIfExists('learners');
    }
}
