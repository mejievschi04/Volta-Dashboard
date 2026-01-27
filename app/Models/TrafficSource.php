<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrafficSource extends Model
{
    protected $connection = 'trafic';
    protected $table = 'traffic_sources';
    
    // Dezactivăm timestamps pentru că tabelul nu are coloanele created_at și updated_at
    public $timestamps = false;
    
    protected $fillable = [
        'source',
        'date',
        'visits',
        'new_users',
        'returning_users',
    ];

    protected $casts = [
        'date' => 'date',
        'visits' => 'integer',
        'new_users' => 'integer',
        'returning_users' => 'integer',
    ];
}
