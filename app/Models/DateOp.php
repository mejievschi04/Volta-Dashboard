<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DateOp extends Model
{
    protected $table = 'date_op';
    public $timestamps = false;
    
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
