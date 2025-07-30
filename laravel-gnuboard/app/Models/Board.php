<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $table = 'g5_board';
    protected $primaryKey = 'bo_table';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'bo_table',
        'gr_id',
        'bo_subject',
        'bo_mobile_subject',
        'bo_device',
        'bo_admin',
        'bo_list_level',
        'bo_read_level',
        'bo_write_level',
        'bo_reply_level',
        'bo_comment_level',
        'bo_upload_level',
        'bo_download_level',
        'bo_html_level',
        'bo_link_level',
        'bo_count_delete',
        'bo_count_modify',
        'bo_read_point',
        'bo_write_point',
        'bo_comment_point',
        'bo_download_point',
        'bo_use_category',
        'bo_category_list',
        'bo_use_sideview',
        'bo_use_file_content',
        'bo_use_secret',
        'bo_use_dhtml_editor',
        'bo_select_editor',
        'bo_use_rss_view',
        'bo_use_good',
        'bo_use_nogood',
        'bo_use_name',
        'bo_use_signature',
        'bo_use_ip_view',
        'bo_use_list_view',
        'bo_use_list_file',
        'bo_use_list_content',
        'bo_table_width',
        'bo_subject_len',
        'bo_mobile_subject_len',
        'bo_page_rows',
        'bo_mobile_page_rows',
        'bo_new',
        'bo_hot',
        'bo_image_width',
        'bo_skin',
        'bo_mobile_skin',
        'bo_include_head',
        'bo_include_tail',
        'bo_content_head',
        'bo_mobile_content_head',
        'bo_content_tail',
        'bo_mobile_content_tail',
        'bo_insert_content',
        'bo_gallery_cols',
        'bo_gallery_width',
        'bo_gallery_height',
        'bo_mobile_gallery_width',
        'bo_mobile_gallery_height',
        'bo_upload_size',
        'bo_reply_order',
        'bo_use_search',
        'bo_order',
        'bo_count_write',
        'bo_count_comment',
        'bo_write_min',
        'bo_write_max',
        'bo_comment_min',
        'bo_comment_max',
        'bo_notice',
        'bo_upload_count',
        'bo_use_email',
        'bo_use_cert',
        'bo_use_sns',
        'bo_use_captcha',
        'bo_sort_field',
        'bo_1_subj',
        'bo_2_subj',
        'bo_3_subj',
        'bo_4_subj',
        'bo_5_subj',
        'bo_6_subj',
        'bo_7_subj',
        'bo_8_subj',
        'bo_9_subj',
        'bo_10_subj',
        'bo_1',
        'bo_2',
        'bo_3',
        'bo_4',
        'bo_5',
        'bo_6',
        'bo_7',
        'bo_8',
        'bo_9',
        'bo_10',
    ];

    protected $casts = [
        'bo_category_list' => 'array',
        'bo_notice' => 'array',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'gr_id', 'gr_id');
    }

    public function getCategoriesAttribute()
    {
        if (empty($this->bo_category_list)) {
            return [];
        }
        
        if (is_array($this->bo_category_list)) {
            return $this->bo_category_list;
        }
        
        return explode('|', $this->bo_category_list);
    }

    public function getNoticesAttribute()
    {
        if (empty($this->bo_notice)) {
            return [];
        }
        
        if (is_array($this->bo_notice)) {
            return $this->bo_notice;
        }
        
        return explode(',', $this->bo_notice);
    }

    public function isUseCategory()
    {
        return $this->bo_use_category == 1;
    }

    public function isUseSecret()
    {
        return $this->bo_use_secret == 1;
    }

    public function isUseGood()
    {
        return $this->bo_use_good == 1;
    }

    public function isUseNogood()
    {
        return $this->bo_use_nogood == 1;
    }
}