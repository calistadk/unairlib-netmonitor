<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/BrokenDevice.php
class BrokenDevice extends Model
{
    protected $fillable = [
        'hostid', 'host_name', 'ip', 'groups',
        'reason', 'broken_date'
    ];

    protected $casts = ['broken_date' => 'date'];

}
