<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClienteController extends Controller
{
    /**
     * Mostrar lista de clientes
     */
    public function index()
    {
        $clientes = Cliente::latest()->paginate(10);
        return view('clientes.index', compact('clientes'));
    }

    /**
     * Mostrar formulario para crear cliente
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Guardar nuevo cliente
     */
    public function store(Request $request)
    {
        // Validar datos
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

        // Crear cliente
        $cliente = new Cliente();
        $cliente->fill($validated);

        // Subir archivo INE si existe
        if ($request->hasFile('ine_documento')) {
            $path = $request->file('ine_documento')->store('clientes/ine', 'public');
            $cliente->ine_documento = $path;
        }

        // Subir contrato firmado si existe
        if ($request->hasFile('contrato_firmado')) {
            $path = $request->file('contrato_firmado')->store('clientes/contratos', 'public');
            $cliente->contrato_firmado = $path;
        }

        // Subir comprobante de depósito si existe
        if ($request->hasFile('comprobante_deposito')) {
            $path = $request->file('comprobante_deposito')->store('clientes/comprobantes', 'public');
            $cliente->comprobante_deposito = $path;
        }

        $cliente->save();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente');
    }

    /**
     * Mostrar un cliente específico
     */
    public function show(Cliente $cliente)
    {
        // Cargar rentas y obras relacionadas
        $cliente->load([
            'rentas' => function($query) {
                $query->latest()->with('detalles.equipo');
            },
            'obras'
        ]);
        
        // Rentas activas
        $rentasActivas = $cliente->rentas->where('estado', 'activa');
        
        // Rentas finalizadas
        $rentasFinalizadas = $cliente->rentas->where('estado', 'finalizada');
        
        return view('clientes.show', compact('cliente', 'rentasActivas', 'rentasFinalizadas'));
    }

    /**
     * Mostrar formulario para editar cliente
     */
    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Actualizar cliente
     */
    public function update(Request $request, Cliente $cliente)
    {
        // Validar datos
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

        // Actualizar datos básicos
        $cliente->fill($validated);

        // Subir nuevo archivo INE si existe (y eliminar el anterior)
        if ($request->hasFile('ine_documento')) {
            if ($cliente->ine_documento && Storage::disk('public')->exists($cliente->ine_documento)) {
                Storage::disk('public')->delete($cliente->ine_documento);
            }
            $path = $request->file('ine_documento')->store('clientes/ine', 'public');
            $cliente->ine_documento = $path;
        }

        // Subir nuevo contrato si existe
        if ($request->hasFile('contrato_firmado')) {
            if ($cliente->contrato_firmado && Storage::disk('public')->exists($cliente->contrato_firmado)) {
                Storage::disk('public')->delete($cliente->contrato_firmado);
            }
            $path = $request->file('contrato_firmado')->store('clientes/contratos', 'public');
            $cliente->contrato_firmado = $path;
        }

        // Subir nuevo comprobante si existe
        if ($request->hasFile('comprobante_deposito')) {
            if ($cliente->comprobante_deposito && Storage::disk('public')->exists($cliente->comprobante_deposito)) {
                Storage::disk('public')->delete($cliente->comprobante_deposito);
            }
            $path = $request->file('comprobante_deposito')->store('clientes/comprobantes', 'public');
            $cliente->comprobante_deposito = $path;
        }

        $cliente->save();

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cliente actualizado exitosamente');
    }

    /**
     * Eliminar cliente
     */
    public function destroy(Cliente $cliente)
    {
        // Eliminar archivos asociados
        if ($cliente->ine_documento && Storage::disk('public')->exists($cliente->ine_documento)) {
            Storage::disk('public')->delete($cliente->ine_documento);
        }
        if ($cliente->contrato_firmado && Storage::disk('public')->exists($cliente->contrato_firmado)) {
            Storage::disk('public')->delete($cliente->contrato_firmado);
        }
        if ($cliente->comprobante_deposito && Storage::disk('public')->exists($cliente->comprobante_deposito)) {
            Storage::disk('public')->delete($cliente->comprobante_deposito);
        }

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado exitosamente');
    }
}