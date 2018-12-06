<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('firstname', 50);
                $table->string('lastname', 50);
                $table->string('email', 191)->unique();
                $table->string('password')->nullable();
                $table->rememberToken();
                $table->string('email_token', 255)->nullable();
                $table->dateTime('last_login')->nullable();
                $table->integer('role_id')->unsigned();
                $table->enum('status', ['INVITED', 'REGISTERED'])
                    ->default('INVITED');
                $table->timestamps();
                $table->boolean('is_active')->default(true);
                $table->text('latest_jwt_claims')->nullable();
                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('RESTRICT');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
