<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'wr_id';
    
    protected $boardTable;

    protected $fillable = [
        'wr_num',
        'wr_reply',
        'wr_parent',
        'wr_is_comment',
        'wr_comment',
        'wr_comment_reply',
        'ca_name',
        'wr_option',
        'wr_subject',
        'wr_content',
        'wr_seo_title',
        'wr_link1',
        'wr_link2',
        'wr_link1_hit',
        'wr_link2_hit',
        'wr_hit',
        'wr_good',
        'wr_nogood',
        'mb_id',
        'wr_password',
        'wr_name',
        'wr_email',
        'wr_homepage',
        'wr_datetime',
        'wr_file',
        'wr_last',
        'wr_ip',
        'wr_facebook_user',
        'wr_twitter_user',
        'wr_1',
        'wr_2',
        'wr_3',
        'wr_4',
        'wr_5',
        'wr_6',
        'wr_7',
        'wr_8',
        'wr_9',
        'wr_10',
    ];

    protected $casts = [
        'wr_datetime' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        if (isset($attributes['board_table'])) {
            $this->setTable('g5_write_' . $attributes['board_table']);
            $this->boardTable = $attributes['board_table'];
        }
    }

    public static function forBoard($boardTable)
    {
        $instance = new static(['board_table' => $boardTable]);
        return $instance;
    }

    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    public function board()
    {
        return Board::find($this->boardTable);
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'mb_id', 'mb_id');
    }

    public function parent()
    {
        return $this->belongsTo(static::class, 'wr_parent', 'wr_id')
                    ->where('wr_is_comment', 0);
    }

    public function comments()
    {
        return $this->hasMany(static::class, 'wr_parent', 'wr_id')
                    ->where('wr_is_comment', 1)
                    ->orderBy('wr_comment')
                    ->orderBy('wr_comment_reply');
    }

    public function scopeNotComment($query)
    {
        return $query->where('wr_is_comment', 0);
    }

    public function scopeOnlyComment($query)
    {
        return $query->where('wr_is_comment', 1);
    }

    public function scopeNotice($query)
    {
        $board = $this->board();
        if ($board && $board->bo_notice) {
            $notices = explode(',', $board->bo_notice);
            return $query->whereIn('wr_id', $notices);
        }
        return $query;
    }

    public function isNotice()
    {
        $board = $this->board();
        if ($board && $board->bo_notice) {
            $notices = explode(',', $board->bo_notice);
            return in_array($this->wr_id, $notices);
        }
        return false;
    }

    public function isSecret()
    {
        return strpos($this->wr_option, 'secret') !== false;
    }

    public function isHtml()
    {
        return strpos($this->wr_option, 'html') !== false;
    }

    public function incrementHit()
    {
        $this->increment('wr_hit');
    }

    public function incrementGood()
    {
        $this->increment('wr_good');
    }

    public function incrementNogood()
    {
        $this->increment('wr_nogood');
    }
}