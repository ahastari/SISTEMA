<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ObraController extends Controller
{
    public function index()
    {
        $obras = Obra::with('cliente')->latest()->paginate(10);
        return view('obras.index', compact('obras'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nombre_completo')->get();
        return view('obras.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string',
            'cliente_id' => 'required|exists:clientes,id',
            'colonia' => 'nullable|string',
            'ciudad' => 'nullable|string',
            'estado' => 'nullable|string',
            'codigo_postal' => 'nullable|string',
            'telefono_obra' => 'nullable|string',
            'contacto_obra' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        // Convertir el valor 'on' a 1 o 0
        $activa = $request->has('activa') ? 1 : 0;

        Obra::create([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'cliente_id' => $request->cliente_id,
            'colonia' => $request->colonia,
            'ciudad' => $request->ciudad,
            'estado' => $request->estado,
            'codigo_postal' => $request->codigo_postal,
            'telefono_obra' => $request->telefono_obra,
            'contacto_obra' => $request->contacto_obra,
            'observaciones' => $request->observaciones,
            'activa' => $activa,
        ]);

        return redirect()->route('obras.index')
            ->with('success', 'Obra creada exitosamente');
    }

    public function show(Obra $obra)
    {
        $obra->load('cliente', 'rentas');
        return view('obras.show', compact('obra'));
    }

    public function edit(Obra $obra)
    {
        $clientes = Cliente::orderBy('nombre_completo')->get();
        return view('obras.edit', compact('obra', 'clientes'));
    }

    public function update(Request $request, Obra $obra)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string',
            'cliente_id' => 'required|exists:clientes,id',
        ]);

        // Convertir el valor 'on' a 1 o 0
        $activa = $request->has('activa') ? 1 : 0;

        $obra->update([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'cliente_id' => $request->cliente_id,
            'colonia' => $request->colonia,
            'ciudad' => $request->ciudad,
            'estado' => $request->estado,
            'codigo_postal' => $request->codigo_postal,
            'telefono_obra' => $request->telefono_obra,
            'contacto_obra' => $request->contacto_obra,
            'observaciones' => $request->observaciones,
            'activa' => $activa,
        ]);

        return redirect()->route('obras.show', $obra)
            ->with('success', 'Obra actualizada exitosamente');
    }

    public function destroy(Obra $obra)
    {
        $obra->delete();
        return redirect()->route('obras.index')
            ->with('success', 'Obra eliminada');
    }

    // API para obtener obras por cliente (para usar en selects dinámicos)
    public function getObrasByCliente($clienteId)
    {
        $obras = Obra::where('cliente_id', $clienteId)->where('activa', true)->get();
        return response()->json($obras);
    }
}