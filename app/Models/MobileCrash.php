<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileCrash extends Model
{
    protected $fillable = [
        'fingerprint',
        'error_type',
        'error_message',
        'stack_trace',
        'is_fatal',
        'screen',
        'session_id',
        'mobile_user_id',
        'device_id',
        'platform',
        'app_version',
        'os_version',
        'device_model',
        'build_number',
        'ip_address',
        'user_agent',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'is_fatal' => 'boolean',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];
}
