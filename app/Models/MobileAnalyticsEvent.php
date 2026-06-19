<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileAnalyticsEvent extends Model
{
    protected $fillable = [
        'event_name',
        'session_id',
        'mobile_user_id',
        'device_id',
        'platform',
        'app_version',
        'page',
        'previous_page',
        'duration_ms',
        'checkout_step',
        'cart_total',
        'items_count',
        'banner_id',
        'banner_title',
        'order_id',
        'ip_address',
        'user_agent',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'duration_ms' => 'integer',
        'checkout_step' => 'integer',
        'cart_total' => 'decimal:2',
        'items_count' => 'integer',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];
}
