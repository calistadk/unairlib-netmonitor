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
    ];

    public function doneBy()
    {
        return $this->belongsTo(User::class, 'done_by');
    }
}