<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Sucursal;
use App\Models\MovimientoSucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MovimientoSucursalController extends Controller
{
    /**
     * Listado de movimientos filtrados por sucursal
     */
    public function index(Request $request)
    {
        $sucursalId = session('activo_sucursal_id');
        $user = auth()->user();
        
        $query = MovimientoSucursal::with(['equipo', 'sucursalOrigen', 'sucursalDestino', 'usuario']);
        
        if ($sucursalId !== 'global' && !$user->isAdmin()) {
            $query->where(function($q) use ($sucursalId) {
                $q->where('sucursal_origen_id', $sucursalId)
                  ->orWhere('sucursal_destino_id', $sucursalId);
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('equipo_id')) {
            $query->where('equipo_id', $request->equipo_id);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_movimiento', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_movimiento', '<=', $request->fecha_hasta);
        }

        $movimientos = $query->latest('fecha_movimiento')->paginate(20);
        
        if ($sucursalId !== 'global' && !$user->isAdmin()) {
            $equipos = Equipo::where('activo', true)
                ->whereHas('sucursales', function($q) use ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                })->get();
            $sucursales = Sucursal::where('id', $sucursalId)->get();
        } else {
            $equipos = Equipo::where('activo', true)->get();
            $sucursales = Sucursal::where('activa', true)->get();
        }
        
        return view('movimientos.index', compact('movimientos', 'equipos', 'sucursales'));
    }

    /**
     * Formulario para crear un nuevo movimiento
     */
    public function create(Request $request)
    {
        $sucursalActivaId = session('activo_sucursal_id');
        
        // 1. Obtenemos TODAS las sucursales sin excluir ninguna
        $sucursales = \App\Models\Sucursal::where('activa', true)->get();
        
        // 2. Obtenemos TODOS los equipos, porque ahora el origen puede ser cualquier sucursal
        $equipos = \App\Models\Equipo::where('activo', true)
            ->with(['categoria', 'unidadMedida', 'sucursales'])
            ->get();
        
        $equipoSeleccionado = $request->get('equipo_id');
        
        return view('movimientos.create', compact('sucursales', 'equipos', 'sucursalActivaId', 'equipoSeleccionado'));
    }

    /**
     * 🔥 GUARDAR MOVIMIENTO Y ACTUALIZAR STOCK
     */
    public function store(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'sucursal_origen_id' => 'nullable|exists:sucursales,id',
            'sucursal_destino_id' => 'nullable|exists:sucursales,id',
            'cantidad' => 'required|integer|min:1',
            'tipo' => 'required|in:transferencia,entrada,salida,ajuste',
            'motivo' => 'required|string|max:255',
            'descripcion' => 'nullable|string'
        ]);

        $equipo = Equipo::findOrFail($request->equipo_id);
        $cantidad = $request->cantidad;

        // Usamos una transacción para asegurar que si algo falla, no se descuadre el inventario
        DB::beginTransaction();

        try {
            // 🔥 LÓGICA DE ACTUALIZACIÓN DE STOCK EN LA BASE DE DATOS
            if ($request->tipo === 'transferencia') {
                if (!$equipo->disponibleEnSucursal($request->sucursal_origen_id, $cantidad)) {
                    return back()->with('error', 'No hay suficiente stock en la sucursal de origen.');
                }
                // Restar a la sucursal de origen
                $equipo->actualizarStockEnSucursal($request->sucursal_origen_id, $cantidad, 'restar');
                // Sumar a la sucursal de destino
                $equipo->actualizarStockEnSucursal($request->sucursal_destino_id, $cantidad, 'sumar');

            } elseif ($request->tipo === 'entrada' || $request->tipo === 'ajuste') {
                // Sumar directamente al destino
                $equipo->actualizarStockEnSucursal($request->sucursal_destino_id, $cantidad, 'sumar');

            } elseif ($request->tipo === 'salida') {
                if (!$equipo->disponibleEnSucursal($request->sucursal_origen_id, $cantidad)) {
                    return back()->with('error', 'No hay suficiente stock en la sucursal.');
                }
                // Restar directamente del origen
                $equipo->actualizarStockEnSucursal($request->sucursal_origen_id, $cantidad, 'restar');
            }

            // Registrar el movimiento en el historial
            MovimientoSucursal::create([
                'equipo_id' => $equipo->id,
                'sucursal_origen_id' => in_array($request->tipo, ['transferencia', 'salida']) ? $request->sucursal_origen_id : null,
                'sucursal_destino_id' => in_array($request->tipo, ['transferencia', 'entrada', 'ajuste']) ? $request->sucursal_destino_id : null,
                'usuario_id' => auth()->id(),
                'cantidad' => $cantidad,
                'tipo' => $request->tipo,
                'estado' => 'completado', // Se completa en automático
                'motivo' => $request->motivo,
                'descripcion' => $request->descripcion,
                'fecha_movimiento' => now(),
                'fecha_confirmacion' => now(),
                'confirmado_por' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('movimientos.index')->with('success', 'Movimiento registrado exitosamente y cantidades actualizadas.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar el movimiento: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalle de un movimiento
     */
    public function show(MovimientoSucursal $movimiento)
    {
        $movimiento->load(['equipo', 'sucursalOrigen', 'sucursalDestino', 'usuario', 'confirmadoPor']);
        return view('movimientos.show', compact('movimiento'));
    }

    /**
     * Cancelar movimiento
     */
    public function cancelar(MovimientoSucursal $movimiento)
    {
        if ($movimiento->estado === 'cancelado') {
            return back()->with('error', 'El movimiento ya está cancelado.');
        }

        if ($movimiento->estado === 'pendiente') {
            $movimiento->cancelar();
            return redirect()->route('movimientos.index')
                ->with('success', 'Movimiento cancelado.');
        }

        return view('movimientos.cancelar', compact('movimiento'));
    }

    /**
     * Procesar cancelación y revertir stock
     */
    /**
     * Cancela un movimiento y revierte el stock
     */
    public function procesarCancelacion(\App\Models\MovimientoSucursal $movimiento)
    {
        if ($movimiento->estado !== 'completado') {
            return back()->with('error', 'Solo se pueden cancelar movimientos completados.');
        }

        $equipo = \App\Models\Equipo::findOrFail($movimiento->equipo_id);

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // 🔥 REVERTIR EL STOCK EN LA BASE DE DATOS SEGÚN EL TIPO DE MOVIMIENTO
            if ($movimiento->tipo === 'transferencia') {
                // Al cancelar una transferencia, sumamos al origen y restamos al destino
                
                // Primero validamos si hay stock en el destino para poder restarlo
                if (!$equipo->disponibleEnSucursal($movimiento->sucursal_destino_id, $movimiento->cantidad)) {
                    return back()->with('error', 'No hay suficiente stock en el destino para revertir la transferencia.');
                }

                $equipo->actualizarStockEnSucursal($movimiento->sucursal_origen_id, $movimiento->cantidad, 'sumar');
                $equipo->actualizarStockEnSucursal($movimiento->sucursal_destino_id, $movimiento->cantidad, 'restar');

            } elseif ($movimiento->tipo === 'entrada' || $movimiento->tipo === 'ajuste') {
                // Al cancelar entrada/ajuste, restamos del destino
                if (!$equipo->disponibleEnSucursal($movimiento->sucursal_destino_id, $movimiento->cantidad)) {
                    return back()->with('error', 'No hay suficiente stock para revertir este movimiento.');
                }
                $equipo->actualizarStockEnSucursal($movimiento->sucursal_destino_id, $movimiento->cantidad, 'restar');

            } elseif ($movimiento->tipo === 'salida') {
                // Al cancelar salida, sumamos de vuelta al origen
                $equipo->actualizarStockEnSucursal($movimiento->sucursal_origen_id, $movimiento->cantidad, 'sumar');
            }

            // Cambiamos el estado a cancelado
            $movimiento->estado = 'cancelado';
            $movimiento->save();

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('movimientos.index')->with('success', 'Movimiento cancelado y stock revertido correctamente.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Ocurrió un error al cancelar el movimiento: ' . $e->getMessage());
        }
    }

    /**
     * API: Obtener stock de un producto en una sucursal
     */
    public function getStock(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'sucursal_id' => 'required|exists:sucursales,id',
        ]);

        $equipo = Equipo::findOrFail($request->equipo_id);
        $stock = $equipo->getStockEnSucursal($request->sucursal_id);

        return response()->json([
            'success' => true,
            'stock' => $stock,
            'equipo' => $equipo->nombre,
            'codigo' => $equipo->codigo,
            'unidad' => $equipo->unidadMedida ? $equipo->unidadMedida->abreviatura : 'uds',
            'stock_total' => $equipo->stock
        ]);
    }

    /**
     * API: Sucursales disponibles para un producto
     */
    public function getSucursalesDisponibles(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
        ]);

        $equipo = Equipo::findOrFail($request->equipo_id);
        $sucursales = Sucursal::where('activa', true)->get();
        
        $resultado = $sucursales->map(function ($sucursal) use ($equipo) {
            $stock = $equipo->getStockEnSucursal($sucursal->id);
            return [
                'id' => $sucursal->id,
                'nombre' => $sucursal->nombre,
                'stock' => $stock,
                'disponible' => $stock > 0
            ];
        });

        return response()->json([
            'success' => true,
            'sucursales' => $resultado
        ]);
    }
}