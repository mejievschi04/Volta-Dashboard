<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oferte extends Model
{
    protected $table = 'oferte';
    
    protected $fillable = [
        'operator_id',
        'operator', // Păstrăm pentru compatibilitate
        'status',
        'data_trimisa',
        'data_finalizata',
        'valoare',
        'observatii',
    ];

    protected $casts = [
        'data_trimisa' => 'date',
        'data_finalizata' => 'date',
        'valoare' => 'decimal:2',
    ];

    // Relații
    public function operator()
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }
}
