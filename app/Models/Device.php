<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'device_id',
        'type',
        'brand_model',
        'serial_number',
        'ip_address',
        'mac_address',
        'location',
        'status',
        'purchase_date',
        'warranty_expiry',
    ];

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}