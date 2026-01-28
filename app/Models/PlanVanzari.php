<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanVanzari extends Model
{
    protected $table = 'plan_vanzari';
    
    protected $fillable = [
        'an',
        'luna',
        'valoare',
    ];

    public $timestamps = false;
}
