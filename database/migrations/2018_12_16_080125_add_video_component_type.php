<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVideoComponentType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `activity_components` CHANGE `component_type` `component_type` ENUM('IMAGE','AUDIO','TEXT_BLOCK','MCQ','TEXT_INPUT','TEXT_OUTPUT','GAP_FILL','VIDEO') NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `activity_components` CHANGE `component_type` `component_type` ENUM('IMAGE','AUDIO','TEXT_BLOCK','MCQ','TEXT_INPUT','TEXT_OUTPUT','GAP_FILL') NOT NULL;");
    }
}
