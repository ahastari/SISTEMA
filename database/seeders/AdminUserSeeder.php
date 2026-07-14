<?php
// database/seeders/AdminUserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear sucursal principal si no existe
        $sucursal = Sucursal::firstOrCreate(
            ['nombre' => 'Sucursal Principal'],
            [
                'direccion' => 'Dirección Principal',
                'telefono' => '000-000-0000',
                'activa' => true
            ]
        );

        // Crear usuario Administrador Global
        User::firstOrCreate(
            ['email' => 'admin@sistema.com'],
            [
                'name' => 'Administrador Global',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'sucursal_id' => null,
                'status' => 'activo',
                'email_verified_at' => now(),
            ]
        );

        // Crear usuario Gerente de prueba
        User::firstOrCreate(
            ['email' => 'gerente@sistema.com'],
            [
                'name' => 'Gerente Sucursal',
                'password' => Hash::make('gerente123'),
                'role' => 'gerente',
                'sucursal_id' => $sucursal->id,
                'status' => 'activo',
                'email_verified_at' => now(),
            ]
        );

        // Crear usuario Cajero de prueba
        User::firstOrCreate(
            ['email' => 'cajero@sistema.com'],
            [
                'name' => 'Cajero POS',
                'password' => Hash::make('cajero123'),
                'role' => 'cajero',
                'sucursal_id' => $sucursal->id,
                'status' => 'activo',
                'email_verified_at' => now(),
            ]
        );

        echo "✅ Usuarios creados exitosamente:\n";
        echo "   Admin: admin@sistema.com / admin123\n";
        echo "   Gerente: gerente@sistema.com / gerente123\n";
        echo "   Cajero: cajero@sistema.com / cajero123\n";
    }
}