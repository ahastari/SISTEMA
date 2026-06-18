<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Andamios', 'color' => '#0d6efd'],
            ['nombre' => 'Ruedas', 'color' => '#198754'],
            ['nombre' => 'Flete', 'color' => '#ffc107'],
            ['nombre' => 'Madera', 'color' => '#6f42c1'],
            ['nombre' => 'Herramientas', 'color' => '#dc3545'],
            ['nombre' => 'Equipo de Seguridad', 'color' => '#fd7e14'],
            ['nombre' => 'Maquinaria', 'color' => '#0dcaf0'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}