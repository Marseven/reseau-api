<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrateur',
                'surname' => 'Système',
                'username' => 'administrateur',
                'phone' => '+241 74 00 00 01',
                'role' => 'administrator',
                'email' => 'administrateur@eramet-comilog.com',
                'password' => Hash::make('Comilog@2026!'),
                'is_active' => true,
            ],
            [
                'name' => 'Directeur',
                'surname' => 'Réseau',
                'username' => 'directeur',
                'phone' => '+241 74 00 00 02',
                'role' => 'directeur',
                'email' => 'directeur@eramet-comilog.com',
                'password' => Hash::make('Comilog@2026!'),
                'is_active' => true,
            ],
            [
                'name' => 'Technicien',
                'surname' => 'Réseau',
                'username' => 'technicien',
                'phone' => '+241 74 00 00 03',
                'role' => 'technicien',
                'email' => 'technicien@eramet-comilog.com',
                'password' => Hash::make('Comilog@2026!'),
                'is_active' => true,
            ],
            [
                'name' => 'Utilisateur',
                'surname' => 'Standard',
                'username' => 'utilisateur',
                'phone' => '+241 74 00 00 04',
                'role' => 'user',
                'email' => 'utilisateur@eramet-comilog.com',
                'password' => Hash::make('Comilog@2026!'),
                'is_active' => true,
            ],
            [
                'name' => 'Prestataire',
                'surname' => 'Externe',
                'username' => 'prestataire',
                'phone' => '+241 74 00 00 05',
                'role' => 'prestataire',
                'email' => 'prestataire@eramet-comilog.com',
                'password' => Hash::make('Comilog@2026!'),
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
