<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileFeedbackReport extends Model
{
    protected $fillable = [
        'message',
        'reporter_name',
        'reporter_email',
        'screenshot_filename',
        'screenshot_mime',
        'screenshot_base64',
        'has_screenshot',
        'status',
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
        'has_screenshot' => 'boolean',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];
}
