<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique(Categoria::class, 'nombre')
            ],
            'descripcion' => 'nullable|string',
        ]);

        $categoria = Categoria::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activa' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'categoria' => $categoria
        ]);
    }

    public function list()
    {
        return response()->json(Categoria::where('activa', true)->get());
    }
}