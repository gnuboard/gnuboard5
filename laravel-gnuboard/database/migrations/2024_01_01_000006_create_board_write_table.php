<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 기본 게시판 테이블 생성 예시
        $this->createBoardTable('free');
        $this->createBoardTable('notice');
        $this->createBoardTable('gallery');
        $this->createBoardTable('qa');
    }

    protected function createBoardTable($boardName)
    {
        Schema::create('g5_write_' . $boardName, function (Blueprint $table) {
            $table->id('wr_id');
            $table->integer('wr_num')->default(0);
            $table->string('wr_reply', 10);
            $table->integer('wr_parent')->default(0);
            $table->tinyInteger('wr_is_comment')->default(0);
            $table->tinyInteger('wr_comment')->default(0);
            $table->string('wr_comment_reply', 5);
            $table->string('ca_name', 255);
            $table->string('wr_option', 50)->default('');
            $table->string('wr_subject', 255);
            $table->text('wr_content');
            $table->string('wr_seo_title', 255);
            $table->text('wr_link1');
            $table->text('wr_link2');
            $table->integer('wr_link1_hit')->default(0);
            $table->integer('wr_link2_hit')->default(0);
            $table->integer('wr_hit')->default(0);
            $table->integer('wr_good')->default(0);
            $table->integer('wr_nogood')->default(0);
            $table->string('mb_id', 20);
            $table->string('wr_password', 255);
            $table->string('wr_name', 255);
            $table->string('wr_email', 255);
            $table->string('wr_homepage', 255);
            $table->dateTime('wr_datetime')->default('0000-00-00 00:00:00');
            $table->tinyInteger('wr_file')->default(0);
            $table->string('wr_last', 19);
            $table->string('wr_ip', 255);
            $table->string('wr_facebook_user', 100);
            $table->string('wr_twitter_user', 100);
            $table->string('wr_1', 255);
            $table->string('wr_2', 255);
            $table->string('wr_3', 255);
            $table->string('wr_4', 255);
            $table->string('wr_5', 255);
            $table->string('wr_6', 255);
            $table->string('wr_7', 255);
            $table->string('wr_8', 255);
            $table->string('wr_9', 255);
            $table->string('wr_10', 255);
            
            $table->index(['wr_seo_title', 'wr_id']);
            $table->index('wr_num');
            $table->index(['wr_is_comment', 'wr_id']);
            $table->index('wr_parent');
        });
    }

    public function down()
    {
        Schema::dropIfExists('g5_write_free');
        Schema::dropIfExists('g5_write_notice');
        Schema::dropIfExists('g5_write_gallery');
        Schema::dropIfExists('g5_write_qa');
    }
};