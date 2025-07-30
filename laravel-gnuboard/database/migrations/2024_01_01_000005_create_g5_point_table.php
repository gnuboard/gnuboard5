<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('g5_point', function (Blueprint $table) {
            $table->id('po_id');
            $table->string('mb_id', 20)->default('');
            $table->integer('po_point')->default(0);
            $table->string('po_content', 255)->default('');
            $table->string('po_rel_table', 20)->default('');
            $table->string('po_rel_id', 20)->default('');
            $table->string('po_rel_action', 100)->default('');
            $table->integer('po_expired')->default(0);
            $table->date('po_expire_date')->default('9999-12-31');
            $table->integer('po_mb_point')->default(0);
            $table->dateTime('po_datetime');
            
            $table->index(['mb_id', 'po_rel_table', 'po_rel_id', 'po_rel_action'], 'index1');
            $table->index('po_expire_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('g5_point');
    }
};