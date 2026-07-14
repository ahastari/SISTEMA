<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Llamar a otros seeders
        $this->call([
            AdminUserSeeder::class,
            CategoriaSeeder::class,
            UnidadMedidaSeeder::class,
        ]);
    }
}