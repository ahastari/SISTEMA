<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:unidades_medida,nombre',
            'abreviatura' => 'required|string|max:10',
        ]);

        $unidad = UnidadMedida::create([
            'nombre' => $request->nombre,
            'abreviatura' => $request->abreviatura,
            'activa' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Unidad de medida creada exitosamente',
            'unidad' => $unidad
        ]);
    }

    public function list()
    {
        return response()->json(UnidadMedida::where('activa', true)->get());
    }
}