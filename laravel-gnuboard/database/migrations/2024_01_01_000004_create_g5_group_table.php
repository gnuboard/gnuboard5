<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('g5_group', function (Blueprint $table) {
            $table->string('gr_id', 10)->primary();
            $table->string('gr_subject', 255);
            $table->string('gr_device', 50)->default('both');
            $table->string('gr_admin', 255);
            $table->tinyInteger('gr_use_access')->default(0);
            $table->integer('gr_order')->default(0);
            $table->string('gr_1_subj', 255);
            $table->string('gr_2_subj', 255);
            $table->string('gr_3_subj', 255);
            $table->string('gr_4_subj', 255);
            $table->string('gr_5_subj', 255);
            $table->string('gr_6_subj', 255);
            $table->string('gr_7_subj', 255);
            $table->string('gr_8_subj', 255);
            $table->string('gr_9_subj', 255);
            $table->string('gr_10_subj', 255);
            $table->string('gr_1', 255);
            $table->string('gr_2', 255);
            $table->string('gr_3', 255);
            $table->string('gr_4', 255);
            $table->string('gr_5', 255);
            $table->string('gr_6', 255);
            $table->string('gr_7', 255);
            $table->string('gr_8', 255);
            $table->string('gr_9', 255);
            $table->string('gr_10', 255);
        });
    }

    public function down()
    {
        Schema::dropIfExists('g5_group');
    }
};