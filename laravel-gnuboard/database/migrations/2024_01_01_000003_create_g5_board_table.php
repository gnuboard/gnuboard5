<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('g5_board', function (Blueprint $table) {
            $table->string('bo_table', 20)->primary();
            $table->string('gr_id', 255)->default('');
            $table->string('bo_subject', 255)->default('');
            $table->string('bo_mobile_subject', 255)->default('');
            $table->enum('bo_device', ['both', 'pc', 'mobile'])->default('both');
            $table->string('bo_admin', 255)->default('');
            $table->tinyInteger('bo_list_level')->default(0);
            $table->tinyInteger('bo_read_level')->default(0);
            $table->tinyInteger('bo_write_level')->default(0);
            $table->tinyInteger('bo_reply_level')->default(0);
            $table->tinyInteger('bo_comment_level')->default(0);
            $table->tinyInteger('bo_upload_level')->default(0);
            $table->tinyInteger('bo_download_level')->default(0);
            $table->tinyInteger('bo_html_level')->default(0);
            $table->tinyInteger('bo_link_level')->default(0);
            $table->tinyInteger('bo_count_delete')->default(0);
            $table->tinyInteger('bo_count_modify')->default(0);
            $table->integer('bo_read_point')->default(0);
            $table->integer('bo_write_point')->default(0);
            $table->integer('bo_comment_point')->default(0);
            $table->integer('bo_download_point')->default(0);
            $table->tinyInteger('bo_use_category')->default(0);
            $table->text('bo_category_list');
            $table->tinyInteger('bo_use_sideview')->default(0);
            $table->tinyInteger('bo_use_file_content')->default(0);
            $table->tinyInteger('bo_use_secret')->default(0);
            $table->tinyInteger('bo_use_dhtml_editor')->default(0);
            $table->string('bo_select_editor', 50)->default('');
            $table->tinyInteger('bo_use_rss_view')->default(0);
            $table->tinyInteger('bo_use_good')->default(0);
            $table->tinyInteger('bo_use_nogood')->default(0);
            $table->tinyInteger('bo_use_name')->default(0);
            $table->tinyInteger('bo_use_signature')->default(0);
            $table->tinyInteger('bo_use_ip_view')->default(0);
            $table->tinyInteger('bo_use_list_view')->default(0);
            $table->tinyInteger('bo_use_list_file')->default(0);
            $table->tinyInteger('bo_use_list_content')->default(0);
            $table->integer('bo_table_width')->default(0);
            $table->integer('bo_subject_len')->default(0);
            $table->integer('bo_mobile_subject_len')->default(0);
            $table->integer('bo_page_rows')->default(0);
            $table->integer('bo_mobile_page_rows')->default(0);
            $table->integer('bo_new')->default(0);
            $table->integer('bo_hot')->default(0);
            $table->integer('bo_image_width')->default(0);
            $table->string('bo_skin', 255)->default('');
            $table->string('bo_mobile_skin', 255)->default('');
            $table->string('bo_include_head', 255)->default('');
            $table->string('bo_include_tail', 255)->default('');
            $table->text('bo_content_head');
            $table->text('bo_mobile_content_head');
            $table->text('bo_content_tail');
            $table->text('bo_mobile_content_tail');
            $table->text('bo_insert_content');
            $table->integer('bo_gallery_cols')->default(0);
            $table->integer('bo_gallery_width')->default(0);
            $table->integer('bo_gallery_height')->default(0);
            $table->integer('bo_mobile_gallery_width')->default(0);
            $table->integer('bo_mobile_gallery_height')->default(0);
            $table->integer('bo_upload_size')->default(0);
            $table->tinyInteger('bo_reply_order')->default(0);
            $table->tinyInteger('bo_use_search')->default(0);
            $table->integer('bo_order')->default(0);
            $table->integer('bo_count_write')->default(0);
            $table->integer('bo_count_comment')->default(0);
            $table->integer('bo_write_min')->default(0);
            $table->integer('bo_write_max')->default(0);
            $table->integer('bo_comment_min')->default(0);
            $table->integer('bo_comment_max')->default(0);
            $table->text('bo_notice');
            $table->tinyInteger('bo_upload_count')->default(0);
            $table->tinyInteger('bo_use_email')->default(0);
            $table->enum('bo_use_cert', ['', 'cert', 'adult', 'hp-cert', 'hp-adult'])->default('');
            $table->tinyInteger('bo_use_sns')->default(0);
            $table->tinyInteger('bo_use_captcha')->default(0);
            $table->string('bo_sort_field', 255)->default('');
            $table->string('bo_1_subj', 255)->default('');
            $table->string('bo_2_subj', 255)->default('');
            $table->string('bo_3_subj', 255)->default('');
            $table->string('bo_4_subj', 255)->default('');
            $table->string('bo_5_subj', 255)->default('');
            $table->string('bo_6_subj', 255)->default('');
            $table->string('bo_7_subj', 255)->default('');
            $table->string('bo_8_subj', 255)->default('');
            $table->string('bo_9_subj', 255)->default('');
            $table->string('bo_10_subj', 255)->default('');
            $table->string('bo_1', 255)->default('');
            $table->string('bo_2', 255)->default('');
            $table->string('bo_3', 255)->default('');
            $table->string('bo_4', 255)->default('');
            $table->string('bo_5', 255)->default('');
            $table->string('bo_6', 255)->default('');
            $table->string('bo_7', 255)->default('');
            $table->string('bo_8', 255)->default('');
            $table->string('bo_9', 255)->default('');
            $table->string('bo_10', 255)->default('');
        });
    }

    public function down()
    {
        Schema::dropIfExists('g5_board');
    }
};