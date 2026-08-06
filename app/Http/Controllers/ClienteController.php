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
        $user = auth()->user();
        
        $isGlobalAdmin = $user->isAdmin() && $sucursalId === 'global';
        $puedeVerInactivos = $user->isAdmin() || $user->isGerente();

        $query = Cliente::latest();

        // 1. Filtrar por Sucursal
        if (!$isGlobalAdmin) {
            $query->where('sucursal_id', $sucursalId);
        }

        // 2. Control de visibilidad por Rol: Cajero solo ve clientes ACTIVOS
        if (!$puedeVerInactivos) {
            $query->where('activo', true);
        }

        // 3. Filtro de Búsqueda
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nombre_completo', 'like', '%' . $request->search . '%')
                  ->orWhere('rfc', 'like', '%' . $request->search . '%')
                  ->orWhere('empresa', 'like', '%' . $request->search . '%')
                  ->orWhere('telefono', 'like', '%' . $request->search . '%');
            });
        }

        // 4. Filtro opcional por Estado de Actividad (Solo para Admin / Gerente)
        if ($puedeVerInactivos && $request->has('estado_cliente') && $request->estado_cliente !== '') {
            if ($request->estado_cliente === 'activos') {
                $query->where('activo', true);
            } elseif ($request->estado_cliente === 'inactivos') {
                $query->where('activo', false);
            }
        }

        $clientes = $query->paginate(10);
        return view('clientes.index', compact('clientes', 'puedeVerInactivos'));
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
            'email' => 'nullable|email|max:255',
            'rfc' => 'nullable|string|max:20',
            'curp' => 'nullable|string|max:20',
            'ine_numero' => 'nullable|string|max:20',
            'ine_documento' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'contrato_firmado' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'comprobante_deposito' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'telefono_alternativo' => 'nullable|string|max:20',
            'empresa' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'observaciones' => 'nullable|string',
        ]);

        $sucursalId = session('activo_sucursal_id');
        $sucursalIdGuardar = ($sucursalId && $sucursalId !== 'global') ? $sucursalId : null;

        $cliente = new Cliente();
        $cliente->fill($validated);
        $cliente->sucursal_id = $sucursalIdGuardar;
        $cliente->activo = true;
        $cliente->fecha_ultima_actividad = now();

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
        // Si el usuario es cajero y el cliente está deshabilitado, denegar acceso
        $user = auth()->user();
        if (!$cliente->activo && !($user->isAdmin() || $user->isGerente())) {
            abort(403, 'No tienes permisos para ver este cliente inactivo.');
        }

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
            'email' => 'nullable|email|max:255',
            'rfc' => 'nullable|string|max:20',
            'curp' => 'nullable|string|max:20',
            'ine_numero' => 'nullable|string|max:20',
            'ine_documento' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'contrato_firmado' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'comprobante_deposito' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'telefono_alternativo' => 'nullable|string|max:20',
            'empresa' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'observaciones' => 'nullable|string',
        ]);

        $cliente->fill($validated);

        // 1. Verificar si se solicitó eliminar el INE actual
        if ($request->input('eliminar_ine') == '1' && !$request->hasFile('ine_documento')) {
            if ($cliente->ine_documento && Storage::disk('public')->exists($cliente->ine_documento)) {
                Storage::disk('public')->delete($cliente->ine_documento);
            }
            $cliente->ine_documento = null;
        }

        // 2. Si se subió un nuevo INE, eliminar el anterior y guardar el nuevo
        if ($request->hasFile('ine_documento')) {
            if ($cliente->ine_documento && Storage::disk('public')->exists($cliente->ine_documento)) {
                Storage::disk('public')->delete($cliente->ine_documento);
            }
            $cliente->ine_documento = $request->file('ine_documento')->store('clientes/ine', 'public');
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

    // ACCIÓN PARA REACTIVAR CLIENTE (Solo Admin / Gerente)
    public function reactivar(Cliente $cliente)
    {
        $user = auth()->user();
        if (!($user->isAdmin() || $user->isGerente())) {
            return redirect()->back()->with('error', 'No tienes permisos para reactivar clientes.');
        }

        $cliente->update([
            'activo' => true,
            'fecha_ultima_actividad' => now()
        ]);

        return redirect()->back()->with('success', 'El cliente fue reactivado exitosamente.');
    }
}