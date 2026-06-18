<?php

namespace Database\Seeders;

use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;

class UnidadMedidaSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            ['nombre' => 'Pieza', 'abreviatura' => 'pz'],
            ['nombre' => 'Metro', 'abreviatura' => 'm'],
            ['nombre' => 'Kilogramo', 'abreviatura' => 'kg'],
            ['nombre' => 'Litro', 'abreviatura' => 'L'],
            ['nombre' => 'Juego', 'abreviatura' => 'jgo'],
            ['nombre' => 'Par', 'abreviatura' => 'par'],
            ['nombre' => 'Rollo', 'abreviatura' => 'rollo'],
            ['nombre' => 'Caja', 'abreviatura' => 'caja'],
        ];

        foreach ($unidades as $unidad) {
            UnidadMedida::create($unidad);
        }
    }
}