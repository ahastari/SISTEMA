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

        $query = Cliente::with('sucursal')->latest();
        
        $filtro = $request->get('filtro', 'activos');

        // TODOS los usuarios pueden ver la lista de bloqueados
        if ($filtro === 'bloqueados') {
            $query->where('bloqueado', true);
        } else {
            if (!$isGlobalAdmin) {
                $query->where('sucursal_id', $sucursalId);
            }
            
            if ($filtro === 'inactivos' && $puedeVerInactivos) {
                $query->where('activo', false)->where('bloqueado', false);
            } else {
                $query->where('activo', true)->where('bloqueado', false);
                $filtro = 'activos';
            }
        }

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nombre_completo', 'like', '%' . $request->search . '%')
                  ->orWhere('rfc', 'like', '%' . $request->search . '%')
                  ->orWhere('empresa', 'like', '%' . $request->search . '%')
                  ->orWhere('telefono', 'like', '%' . $request->search . '%');
            });
        }

        $clientes = $query->paginate(10);
        return view('clientes.index', compact('clientes', 'puedeVerInactivos', 'filtro'));
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
            'rfc' => 'required|string|max:20',
            'curp' => 'required|string|max:20',
            'ine_numero' => 'nullable|string|max:20',
            'ine_documento' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'comprobante_domicilio_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'contrato_firmado' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'comprobante_deposito' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'telefono_alternativo' => 'nullable|string|max:20',
            'empresa' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'colonia' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'observaciones' => 'nullable|string',
        ]);

        $clienteBloqueado = Cliente::with('sucursal')
            ->where('bloqueado', true)
            ->where(function ($query) use ($request) {
                $query->where('rfc', $request->rfc)
                    ->orWhere('curp', $request->curp)
                    ->orWhere('nombre_completo', $request->nombre_completo);
            })
            ->first();

        if ($clienteBloqueado) {

            $sucursalNombre = $clienteBloqueado->sucursal
                ? $clienteBloqueado->sucursal->nombre
                : 'Otra sucursal';

            $motivo = $clienteBloqueado->motivo_bloqueo
                ?? 'Sin motivo especificado';

            return back()->withErrors([
                'global_block' => "ATENCIÓN: Cliente en LISTA NEGRA. Fue bloqueado en la sucursal '{$sucursalNombre}'. Motivo del bloqueo: {$motivo}"
            ])->withInput();
        }

        $sucursalId = session('activo_sucursal_id');
        $sucursalIdGuardar = ($sucursalId && $sucursalId !== 'global')
            ? $sucursalId
            : null;

        $cliente = new Cliente();
        $cliente->fill($validated);
        $cliente->sucursal_id = $sucursalIdGuardar;
        $cliente->activo = true;
        $cliente->bloqueado = false;
        $cliente->fecha_ultima_actividad = now();

        if ($request->hasFile('ine_documento')) {
            $cliente->ine_documento = $request->file('ine_documento')
                ->store('clientes/ine', 'public');
        }

        if ($request->hasFile('comprobante_domicilio_path')) {
            $cliente->comprobante_domicilio_path = $request->file('comprobante_domicilio_path')
                ->store('clientes/comprobantes_domicilio', 'public');
        }

        if ($request->hasFile('contrato_firmado')) {
            $cliente->contrato_firmado = $request->file('contrato_firmado')
                ->store('clientes/contratos', 'public');
        }

        if ($request->hasFile('comprobante_deposito')) {
            $cliente->comprobante_deposito = $request->file('comprobante_deposito')
                ->store('clientes/comprobantes', 'public');
        }

        $cliente->save();

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente');
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
            'rfc' => 'required|string|max:20',
            'curp' => 'required|string|max:20',
            'ine_numero' => 'nullable|string|max:20',
            'ine_documento' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'comprobante_domicilio_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'contrato_firmado' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'comprobante_deposito' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'telefono_alternativo' => 'nullable|string|max:20',
            'empresa' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'colonia' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'observaciones' => 'nullable|string',
        ]);

        $clienteBloqueado = Cliente::with('sucursal')
            ->where('bloqueado', true)
            ->where('id', '!=', $cliente->id)
            ->where(function ($query) use ($request) {
                $query->where('rfc', $request->rfc)
                    ->orWhere('curp', $request->curp)
                    ->orWhere('nombre_completo', $request->nombre_completo);
            })
            ->first();

        if ($clienteBloqueado) {

            $sucursalNombre = $clienteBloqueado->sucursal
                ? $clienteBloqueado->sucursal->nombre
                : 'Otra sucursal';

            $motivo = $clienteBloqueado->motivo_bloqueo
                ?? 'Sin motivo especificado';

            return back()->withErrors([
                'global_block' => "ATENCIÓN: Cliente en LISTA NEGRA. Fue bloqueado en la sucursal '{$sucursalNombre}'. Motivo del bloqueo: {$motivo}"
            ])->withInput();
        }

        $cliente->fill($validated);

        // Eliminar INE
        if (
            $request->input('eliminar_ine') == '1' &&
            !$request->hasFile('ine_documento')
        ) {
            if (
                $cliente->ine_documento &&
                Storage::disk('public')->exists($cliente->ine_documento)
            ) {
                Storage::disk('public')->delete($cliente->ine_documento);
            }

            $cliente->ine_documento = null;
        }

        // Reemplazar INE
        if ($request->hasFile('ine_documento')) {

            if (
                $cliente->ine_documento &&
                Storage::disk('public')->exists($cliente->ine_documento)
            ) {
                Storage::disk('public')->delete($cliente->ine_documento);
            }

            $cliente->ine_documento = $request->file('ine_documento')
                ->store('clientes/ine', 'public');
        }

        // Eliminar comprobante domicilio
        if (
            $request->input('eliminar_comprobante') == '1' &&
            !$request->hasFile('comprobante_domicilio_path')
        ) {
            if (
                $cliente->comprobante_domicilio_path &&
                Storage::disk('public')->exists($cliente->comprobante_domicilio_path)
            ) {
                Storage::disk('public')->delete($cliente->comprobante_domicilio_path);
            }

            $cliente->comprobante_domicilio_path = null;
        }

        // Reemplazar comprobante domicilio
        if ($request->hasFile('comprobante_domicilio_path')) {

            if (
                $cliente->comprobante_domicilio_path &&
                Storage::disk('public')->exists($cliente->comprobante_domicilio_path)
            ) {
                Storage::disk('public')->delete($cliente->comprobante_domicilio_path);
            }

            $cliente->comprobante_domicilio_path = $request->file('comprobante_domicilio_path')
                ->store('clientes/comprobantes_domicilio', 'public');
        }

        $cliente->save();

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('success', 'Cliente actualizado exitosamente');
    }

    public function bloquear(Request $request, Cliente $cliente)
    {
        // 1. Quitamos la restricción de rol. Todos los usuarios logueados pueden bloquear.
        $request->validate([
            'motivo_bloqueo' => 'required|string|max:500'
        ]);

        Cliente::where('rfc', $cliente->rfc)
               ->orWhere('curp', $cliente->curp)
               ->orWhere('nombre_completo', $cliente->nombre_completo)
               ->update([
                   'bloqueado' => true,
                   'motivo_bloqueo' => $request->motivo_bloqueo
               ]);

        return redirect()->back()->with('error', 'El cliente ha sido BLOQUEADO globalmente. Motivo registrado.');
    }

    public function desbloquear(Cliente $cliente)
    {
        // 2. Mantenemos la restricción estricta. Solo Admin/Gerente pueden desbloquear.
        $user = auth()->user();
        if (!($user->isAdmin() || $user->isGerente())) {
            return redirect()->back()->with('error', 'Seguridad: Solo los gerentes o administradores pueden desbloquear clientes.');
        }

        Cliente::where('rfc', $cliente->rfc)
               ->orWhere('curp', $cliente->curp)
               ->orWhere('nombre_completo', $cliente->nombre_completo)
               ->update([
                   'bloqueado' => false,
                   'motivo_bloqueo' => null
               ]);

        return redirect()->back()->with('success', 'El cliente ha sido DESBLOQUEADO a nivel global.');
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