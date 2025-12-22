<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_name',
        'device_type',
        'platform',
        'browser',
        'ip',
        'user_agent',
        'device_hash',
        'logged_in_at',
        'last_activity_at',
        'location_city',
        'location_country',
        'status',
        'session_id',
    ];
}
