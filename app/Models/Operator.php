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
        'photo_profil',
        'photo_coperta',
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

    /** URL pentru poza de profil (storage). */
    public function getPhotoProfilUrlAttribute(): ?string
    {
        if (empty($this->photo_profil)) {
            return null;
        }
        return asset('storage/' . ltrim($this->photo_profil, '/'));
    }

    /** URL pentru poza de copertă (storage). */
    public function getPhotoCopertaUrlAttribute(): ?string
    {
        if (empty($this->photo_coperta)) {
            return null;
        }
        return asset('storage/' . ltrim($this->photo_coperta, '/'));
    }
}
