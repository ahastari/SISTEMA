<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
            'descripcion' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        $categoria = Categoria::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'color' => $request->color ?? '#0d6efd',
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