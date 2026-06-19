<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EnsureDevUser extends Command
{
    protected $signature = 'user:ensure-dev
        {username=Mejievski : Username-ul contului dev}
        {--email= : Email optional}
        {--name= : Nume de afisare optional}
        {--password= : Parola; recomandat sa fie omisa ca sa fie ceruta ascuns}';

    protected $description = 'Creeaza sau actualizeaza un utilizator cu rol dev.';

    public function handle(): int
    {
        $username = trim((string) $this->argument('username'));
        if ($username === '') {
            $this->error('Username-ul este obligatoriu.');
            return self::FAILURE;
        }

        $password = (string) ($this->option('password') ?? '');
        if ($password === '') {
            $password = (string) $this->secret("Parola pentru {$username}");
            $confirmation = (string) $this->secret('Confirma parola');

            if ($password === '' || $password !== $confirmation) {
                $this->error('Parola lipseste sau confirmarile nu coincid.');
                return self::FAILURE;
            }
        }

        if (mb_strlen($password) < 6) {
            $this->error('Parola trebuie sa aiba minim 6 caractere.');
            return self::FAILURE;
        }

        $user = User::firstOrNew(['username' => $username]);
        $wasExisting = $user->exists;

        $user->role = 'dev';
        $user->password_hash = Hash::make($password);

        if ($this->option('email') !== null) {
            $user->email = (string) $this->option('email');
        }

        if ($this->option('name') !== null) {
            $name = trim((string) $this->option('name'));
            $user->name = $name !== '' ? $name : $username;
            $user->full_name = $name !== '' ? $name : $username;
        } elseif (! $wasExisting) {
            $user->name = $username;
            $user->full_name = $username;
        }

        $user->save();

        $this->info($wasExisting
            ? "Utilizatorul {$username} a fost actualizat cu rol dev."
            : "Utilizatorul {$username} a fost creat cu rol dev.");

        return self::SUCCESS;
    }
}
