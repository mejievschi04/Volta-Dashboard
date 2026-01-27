<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produs extends Model
{
    protected $connection = 'produse';
    protected $table = 'produse';
    
    protected $fillable = [
        'nume',
        'cod',
        'pret',
        'categorie',
    ];
}
