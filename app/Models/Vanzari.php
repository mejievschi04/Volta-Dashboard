<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vanzari extends Model
{
    protected $connection = 'vanzari';
    protected $table = 'vanzari_1c';
    
    protected $fillable = [
        'operator_id',
        'data',
        'suma_fara_tva',
        'suma_cu_tva',
        'profit',
        'nr_vanzari',
    ];

    protected $casts = [
        'data' => 'date',
        'suma_fara_tva' => 'decimal:2',
        'suma_cu_tva' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    // Relații
    public function operator()
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }
}
