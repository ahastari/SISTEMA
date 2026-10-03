<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use Illuminate\Http\Request;

class ObraController extends Controller
{
    /**
     * Registra la obra de UNA renta.
     * Se usa solo desde el modal de rentas.create (respuesta JSON).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'        => 'required|string|max:255',
            'direccion'     => 'required|string',
            'cliente_id'    => 'required|exists:clientes,id',
            'colonia'       => 'required|string|max:255',
            'ciudad'        => 'required|string|max:100',
            'estado'        => 'required|string|max:100',
            'codigo_postal' => 'required|digits:5',
            'telefono_obra' => 'required|digits:10',
            'contacto_obra' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
        ], [
            'nombre.required'        => 'El nombre de la obra es obligatorio.',
            'direccion.required'     => 'La calle y número son obligatorios.',
            'colonia.required'       => 'La colonia es obligatoria.',
            'ciudad.required'        => 'La ciudad o municipio es obligatoria.',
            'estado.required'        => 'El estado es obligatorio.',
            'codigo_postal.required' => 'El código postal es obligatorio.',
            'codigo_postal.digits'   => 'El código postal debe tener exactamente 5 dígitos.',
            'telefono_obra.required' => 'El teléfono de la obra es obligatorio.',
            'telefono_obra.digits'   => 'El teléfono de la obra debe tener exactamente 10 dígitos.',
            'contacto_obra.required' => 'El contacto o encargado de la obra es obligatorio.',
        ]);

        $sucursalId = session('activo_sucursal_id');
        $validated['sucursal_id'] = ($sucursalId && $sucursalId !== 'global') ? $sucursalId : null;
        $validated['activa'] = true;

        $obra = Obra::create($validated);

        return response()->json(['success' => true, 'obra' => $obra], 201);
    }

    /**
     * Solo borra obras que aún no están ligadas a ninguna renta
     * (cuando el usuario pulsa "Quitar" antes de guardar la renta).
     */
    public function destroy(Obra $obra)
    {
        if ($obra->rentas()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'La obra ya pertenece a una renta.',
            ], 422);
        }

        $obra->delete();

        return response()->json(['success' => true]);
    }
}