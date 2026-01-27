<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    protected $table = 'operatori';
    
    protected $fillable = [
        'nume',
        'email',
        'telefon',
        'data_angajare',
        'adresa',
        'departament',
        'functie',
        'observatii',
        'activ',
    ];

    protected $casts = [
        'data_angajare' => 'date',
        'activ' => 'boolean',
    ];

    // Relații
    public function oferte()
    {
        return $this->hasMany(Oferte::class, 'operator_id');
    }

    public function vanzari()
    {
        return $this->hasMany(Vanzari::class, 'operator_id');
    }
}
