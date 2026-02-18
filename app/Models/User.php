<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'password_hash',
        'role',
        'operator_nume',
        'currency',
        'language',
        'country',
    ];

    /** Utilizatorul este operator (vede doar pagina „Datele mele”). */
    public function isOperator(): bool
    {
        $role = strtolower((string) ($this->role ?? ''));
        return $role === 'operator' || $role === 'operatori';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Nu folosim 'hashed' cast pentru că verificăm manual în LoginController
        ];
    }

    /**
     * Get the name of the unique identifier for the user.
     * Laravel folosește 'id' pentru a stoca utilizatorul în sesiune.
     * Folosim username doar pentru login, dar ID-ul pentru autentificare.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id'; // Laravel așteaptă 'id' pentru sesiune
    }

    /**
     * Get the password for authentication.
     * Verifică atât password_hash cât și password
     */
    public function getAuthPassword()
    {
        // Returnăm valoarea originală din baza de date, fără casting
        $attributes = $this->getAttributes();
        return $attributes['password_hash'] ?? $attributes['password'] ?? null;
    }
}
