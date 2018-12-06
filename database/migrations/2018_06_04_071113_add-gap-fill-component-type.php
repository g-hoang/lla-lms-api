<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddGapFillComponentType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `activity_components` CHANGE `component_type` `component_type` ENUM('IMAGE','AUDIO','TEXT_BLOCK','MCQ','TEXT_INPUT','TEXT_OUTPUT','GAP_FILL') NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `activity_components` CHANGE `component_type` `component_type` ENUM('IMAGE','AUDIO','TEXT_BLOCK','MCQ','TEXT_INPUT','TEXT_OUTPUT') NOT NULL;)");
    }
}
