<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livrare extends Model
{
    protected $table = 'livrari';

    protected $fillable = [
        'numar_comanda',
        'data',
        'adresa_livrarii',
        'localitate',
        'nr_client',
        'data_livrarii',
        'in_chisinau',
        'user_id',
    ];

    protected $casts = [
        'data' => 'date',
        'data_livrarii' => 'date',
        'in_chisinau' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
