<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'g5_member';
    protected $primaryKey = 'mb_no';
    public $timestamps = false;

    protected $fillable = [
        'mb_id',
        'mb_password',
        'mb_name',
        'mb_nick',
        'mb_nick_date',
        'mb_email',
        'mb_homepage',
        'mb_level',
        'mb_sex',
        'mb_birth',
        'mb_tel',
        'mb_hp',
        'mb_certify',
        'mb_adult',
        'mb_dupinfo',
        'mb_zip1',
        'mb_zip2',
        'mb_addr1',
        'mb_addr2',
        'mb_addr3',
        'mb_addr_jibeon',
        'mb_signature',
        'mb_recommend',
        'mb_point',
        'mb_today_login',
        'mb_login_ip',
        'mb_datetime',
        'mb_ip',
        'mb_leave_date',
        'mb_intercept_date',
        'mb_email_certify',
        'mb_email_certify2',
        'mb_memo',
        'mb_lost_certify',
        'mb_mailling',
        'mb_sms',
        'mb_open',
        'mb_open_date',
        'mb_profile',
        'mb_memo_call',
        'mb_memo_cnt',
        'mb_scrap_cnt',
        'mb_1',
        'mb_2',
        'mb_3',
        'mb_4',
        'mb_5',
        'mb_6',
        'mb_7',
        'mb_8',
        'mb_9',
        'mb_10',
    ];

    protected $hidden = [
        'mb_password',
        'mb_lost_certify',
    ];

    protected $casts = [
        'mb_email_certify' => 'datetime',
        'mb_datetime' => 'datetime',
        'mb_today_login' => 'datetime',
        'mb_leave_date' => 'datetime',
        'mb_nick_date' => 'date',
        'mb_open_date' => 'date',
    ];

    public function getAuthIdentifierName()
    {
        return 'mb_id';
    }

    public function getAuthIdentifier()
    {
        return $this->mb_id;
    }

    public function getAuthPassword()
    {
        return $this->mb_password;
    }

    public function getEmailForVerification()
    {
        return $this->mb_email;
    }

    public function getNameAttribute()
    {
        return $this->mb_name;
    }

    public function getNickAttribute()
    {
        return $this->mb_nick;
    }

    public function points()
    {
        return $this->hasMany(Point::class, 'mb_id', 'mb_id');
    }

    public function getTotalPointsAttribute()
    {
        return $this->mb_point;
    }

    public function isAdmin()
    {
        return $this->mb_level >= 10;
    }

    public function isSuperAdmin()
    {
        return $this->mb_id === config('gnuboard.admin_id', 'admin');
    }

    public function canAccessBoard($board)
    {
        return $this->mb_level >= $board->bo_list_level;
    }

    public function canWriteBoard($board)
    {
        return $this->mb_level >= $board->bo_write_level;
    }

    public function canReadPost($board)
    {
        return $this->mb_level >= $board->bo_read_level;
    }
}
