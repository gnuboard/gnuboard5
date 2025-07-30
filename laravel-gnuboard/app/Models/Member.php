<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Member extends Authenticatable
{
    use Notifiable;

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
    ];

    protected $casts = [
        'mb_datetime' => 'datetime',
        'mb_today_login' => 'datetime',
        'mb_email_certify' => 'datetime',
        'mb_leave_date' => 'date',
        'mb_intercept_date' => 'date',
        'mb_open_date' => 'date',
        'mb_nick_date' => 'date',
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
}