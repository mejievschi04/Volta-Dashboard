<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanVanzari extends Model
{
    protected $connection = 'vanzari';
    protected $table = 'plan_vanzari';
    
    protected $fillable = [
        'an',
        'luna',
        'valoare',
    ];

    public $timestamps = false;
}
