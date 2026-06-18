<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Compte administrateur uniquement ---
        Utilisateur::firstOrCreate(
            ['email' => 'admin@btp.com'],
            [
                'nom'          => 'Mensah',
                'prenom'       => 'Kofi',
                'telephone'    => '+225 07 00 00 00 01',
                'mot_de_passe' => Hash::make('password'),
                'role'         => 'admin',
                'actif'        => true,
            ]
        );
    }
}