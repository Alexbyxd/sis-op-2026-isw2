<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with the 2 police systems.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'sistema1'],
            [
                'name' => 'Sistema de Correspondencia',
                'email' => 'sistema1@policia.local',
                'system_code' => 'sistema1',
                'password' => Hash::make(env('SISTEMA1_PASSWORD', 'Correspondencia2026!')),
            ]
        );

        User::updateOrCreate(
            ['username' => 'sistema2'],
            [
                'name' => 'Sistema de Manejo de Oficiales',
                'email' => 'sistema2@policia.local',
                'system_code' => 'sistema2',
                'password' => Hash::make(env('SISTEMA2_PASSWORD', 'Oficiales2026!')),
            ]
        );
    }
}
