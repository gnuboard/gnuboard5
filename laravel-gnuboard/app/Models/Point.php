<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $table = 'g5_point';
    protected $primaryKey = 'po_id';
    public $timestamps = false;

    protected $fillable = [
        'mb_id',
        'po_point',
        'po_content',
        'po_rel_table',
        'po_rel_id',
        'po_rel_action',
        'po_expired',
        'po_expire_date',
        'po_mb_point',
        'po_datetime',
    ];

    protected $casts = [
        'po_datetime' => 'datetime',
        'po_expire_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(User::class, 'mb_id', 'mb_id');
    }

    public function scopeActive($query)
    {
        return $query->where('po_expired', 0)
                    ->where('po_expire_date', '>=', now()->format('Y-m-d'));
    }

    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('po_expired', 1)
              ->orWhere('po_expire_date', '<', now()->format('Y-m-d'));
        });
    }
}