<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'g5_group';
    protected $primaryKey = 'gr_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'gr_id',
        'gr_subject',
        'gr_device',
        'gr_admin',
        'gr_use_access',
        'gr_order',
        'gr_1_subj',
        'gr_2_subj',
        'gr_3_subj',
        'gr_4_subj',
        'gr_5_subj',
        'gr_6_subj',
        'gr_7_subj',
        'gr_8_subj',
        'gr_9_subj',
        'gr_10_subj',
        'gr_1',
        'gr_2',
        'gr_3',
        'gr_4',
        'gr_5',
        'gr_6',
        'gr_7',
        'gr_8',
        'gr_9',
        'gr_10',
    ];

    public function boards()
    {
        return $this->hasMany(Board::class, 'gr_id', 'gr_id');
    }

    public function isUseAccess()
    {
        return $this->gr_use_access == 1;
    }
}