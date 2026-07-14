<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $sucursalId = session('activo_sucursal_id');
        $isGlobalAdmin = auth()->user()->isAdmin() && $sucursalId === 'global';

        $query = Cliente::latest();

        if (!$isGlobalAdmin) {
            $query->where('sucursal_id', $sucursalId);
        }

        // Búsqueda que ya tenías en la vista
        if ($request->has('search') && $request->search) {
            $query->where('nombre_completo', 'like', '%' . $request->search . '%')
                  ->orWhere('rfc', 'like', '%' . $request->search . '%')
                  ->orWhere('empresa', 'like', '%' . $request->search . '%');
        }

        $clientes = $query->paginate(10);
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'rfc' => 'required|string|max:20',
            'curp' => 'required|string|max:20',
            'ine_numero' => 'nullable|string|max:20',
            'ine_documento' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'contrato_firmado' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'comprobante_deposito' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'telefono_alternativo' => 'nullable|string|max:20',
            'empresa' => 'required|string|max:255',
            'direccion' => 'required|string',
            'ciudad' => 'required|string|max:100',
            'estado' => 'required|string|max:100',
            'codigo_postal' => 'required|string|max:10',
            'observaciones' => 'nullable|string',
        ]);

        $sucursalId = session('activo_sucursal_id');
        $sucursalIdGuardar = ($sucursalId && $sucursalId !== 'global') ? $sucursalId : null;

        $cliente = new Cliente();
        $cliente->fill($validated);
        $cliente->sucursal_id = $sucursalIdGuardar; // 🔒 Asignar a la sucursal activa

        if ($request->hasFile('ine_documento')) {
            $cliente->ine_documento = $request->file('ine_documento')->store('clientes/ine', 'public');
        }
        if ($request->hasFile('contrato_firmado')) {
            $cliente->contrato_firmado = $request->file('contrato_firmado')->store('clientes/contratos', 'public');
        }
        if ($request->hasFile('comprobante_deposito')) {
            $cliente->comprobante_deposito = $request->file('comprobante_deposito')->store('clientes/comprobantes', 'public');
        }

        $cliente->save();

        return redirect()->route('clientes.index')->with('success', 'Cliente creado exitosamente');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load([
            'rentas' => function($query) {
                $query->latest()->with('detalles.equipo');
            },
            'obras'
        ]);
        
        $rentasActivas = $cliente->rentas->where('estado', 'activa');
        $rentasFinalizadas = $cliente->rentas->where('estado', 'finalizada');
        
        return view('clientes.show', compact('cliente', 'rentasActivas', 'rentasFinalizadas'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'rfc' => 'required|string|max:20',
            'curp' => 'required|string|max:20',
            'ine_numero' => 'nullable|string|max:20',
            'ine_documento' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'contrato_firmado' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'comprobante_deposito' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'telefono_alternativo' => 'nullable|string|max:20',
            'empresa' => 'required|string|max:255',
            'direccion' => 'required|string',
            'ciudad' => 'required|string|max:100',
            'estado' => 'required|string|max:100',
            'codigo_postal' => 'required|string|max:10',
            'observaciones' => 'nullable|string',
        ]);

        $cliente->fill($validated);

        if ($request->hasFile('ine_documento')) {
            if ($cliente->ine_documento && Storage::disk('public')->exists($cliente->ine_documento)) Storage::disk('public')->delete($cliente->ine_documento);
            $cliente->ine_documento = $request->file('ine_documento')->store('clientes/ine', 'public');
        }
        if ($request->hasFile('contrato_firmado')) {
            if ($cliente->contrato_firmado && Storage::disk('public')->exists($cliente->contrato_firmado)) Storage::disk('public')->delete($cliente->contrato_firmado);
            $cliente->contrato_firmado = $request->file('contrato_firmado')->store('clientes/contratos', 'public');
        }
        if ($request->hasFile('comprobante_deposito')) {
            if ($cliente->comprobante_deposito && Storage::disk('public')->exists($cliente->comprobante_deposito)) Storage::disk('public')->delete($cliente->comprobante_deposito);
            $cliente->comprobante_deposito = $request->file('comprobante_deposito')->store('clientes/comprobantes', 'public');
        }

        $cliente->save();

        return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy(Cliente $cliente)
    {
        if ($cliente->ine_documento && Storage::disk('public')->exists($cliente->ine_documento)) Storage::disk('public')->delete($cliente->ine_documento);
        if ($cliente->contrato_firmado && Storage::disk('public')->exists($cliente->contrato_firmado)) Storage::disk('public')->delete($cliente->contrato_firmado);
        if ($cliente->comprobante_deposito && Storage::disk('public')->exists($cliente->comprobante_deposito)) Storage::disk('public')->delete($cliente->comprobante_deposito);

        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado exitosamente');
    }
}