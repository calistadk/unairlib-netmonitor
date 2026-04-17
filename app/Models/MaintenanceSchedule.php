<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    protected $fillable = [
        'device_id',
        'device_name',
        'scheduled_date',
        'next_maintenance',
        'interval_days',       
        'is_done',
        'done_at',
        'done_by',
        'notes',
    ];

    protected $casts = [
        'scheduled_date'   => 'date',
        'next_maintenance' => 'date',
        'done_at'          => 'datetime',
        'is_done'          => 'boolean',
        'interval_days'    => 'integer',   // ← BARU
    ];

    public function doneBy()
    {
        return $this->belongsTo(User::class, 'done_by');
    }
}