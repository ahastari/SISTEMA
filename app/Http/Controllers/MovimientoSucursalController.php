<?php
// app/Http/Controllers/MovimientoSucursalController.php

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
     * Display a listing of movements.
     */
    public function index(Request $request)
    {
        $sucursalId = session('activo_sucursal_id');
        
        $query = MovimientoSucursal::with(['equipo', 'sucursalOrigen', 'sucursalDestino', 'usuario'])
            ->when($sucursalId !== 'global', function ($q) use ($sucursalId) {
                return $q->where('sucursal_origen_id', $sucursalId)
                         ->orWhere('sucursal_destino_id', $sucursalId);
            });

        // Filtros
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
        
        $equipos = Equipo::where('activo', true)->get();
        $sucursales = Sucursal::where('activa', true)->get();
        
        return view('movimientos.index', compact('movimientos', 'equipos', 'sucursales'));
    }

    /**
     * Show the form for creating a new movement.
     */
    public function create(Request $request)
    {
        $sucursalOrigenId = session('activo_sucursal_id');
        
        if ($sucursalOrigenId === 'global') {
            // Si es admin, puede seleccionar origen
            $sucursales = Sucursal::where('activa', true)->get();
            $sucursalOrigen = null;
        } else {
            $sucursales = Sucursal::where('activa', true)
                                  ->where('id', '!=', $sucursalOrigenId)
                                  ->get();
            $sucursalOrigen = Sucursal::find($sucursalOrigenId);
        }
        
        $equipos = Equipo::where('activo', true)->get();
        
        // Preseleccionar equipo si viene por parámetro
        $equipoSeleccionado = $request->get('equipo_id');
        
        return view('movimientos.create', compact('sucursales', 'equipos', 'sucursalOrigen', 'equipoSeleccionado'));
    }

    /**
     * Store a newly created movement.
     */
    public function store(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'sucursal_origen_id' => 'required|exists:sucursales,id',
            'sucursal_destino_id' => 'required|exists:sucursales,id|different:sucursal_origen_id',
            'cantidad' => 'required|integer|min:1',
            'tipo' => 'required|in:entrada,salida,transferencia,ajuste',
            'motivo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $sucursalOrigenId = $request->sucursal_origen_id;
        $sucursalDestinoId = $request->sucursal_destino_id;
        $equipoId = $request->equipo_id;
        $cantidad = $request->cantidad;

        // Verificar que el origen tiene suficiente stock
        $equipo = Equipo::find($equipoId);
        $stockOrigen = $equipo->getStockEnSucursal($sucursalOrigenId);
        
        if ($stockOrigen < $cantidad) {
            return back()->withErrors([
                'cantidad' => 'No hay suficiente stock en la sucursal de origen. Disponible: ' . $stockOrigen
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            // Crear el movimiento
            $movimiento = MovimientoSucursal::create([
                'equipo_id' => $equipoId,
                'sucursal_origen_id' => $sucursalOrigenId,
                'sucursal_destino_id' => $sucursalDestinoId,
                'usuario_id' => Auth::id(),
                'cantidad' => $cantidad,
                'tipo' => $request->tipo,
                'estado' => 'completado',
                'motivo' => $request->motivo,
                'descripcion' => $request->descripcion,
                'fecha_movimiento' => now(),
                'fecha_confirmacion' => now(),
                'confirmado_por' => Auth::id(),
            ]);

            // Actualizar stock en origen (restar)
            $equipo->actualizarStockEnSucursal($sucursalOrigenId, $cantidad, 'restar');

            // Actualizar stock en destino (sumar)
            $equipo->actualizarStockEnSucursal($sucursalDestinoId, $cantidad, 'sumar');

            DB::commit();

            return redirect()->route('movimientos.index')
                           ->with('success', 'Movimiento registrado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el movimiento: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Display the specified movement.
     */
    public function show(MovimientoSucursal $movimiento)
    {
        $movimiento->load(['equipo', 'sucursalOrigen', 'sucursalDestino', 'usuario', 'confirmadoPor']);
        return view('movimientos.show', compact('movimiento'));
    }

    /**
     * Show form to cancel a movement.
     */
    public function cancelar(MovimientoSucursal $movimiento)
    {
        if ($movimiento->estado === 'cancelado') {
            return back()->with('error', 'El movimiento ya está cancelado');
        }

        if ($movimiento->estado === 'pendiente') {
            $movimiento->cancelar();
            return redirect()->route('movimientos.index')
                           ->with('success', 'Movimiento cancelado exitosamente');
        }

        return view('movimientos.cancelar', compact('movimiento'));
    }

    /**
     * Process cancellation of a completed movement.
     */
    public function procesarCancelacion(Request $request, MovimientoSucursal $movimiento)
    {
        $request->validate([
            'motivo_cancelacion' => 'required|string|max:255',
        ]);

        if ($movimiento->estado !== 'completado') {
            return back()->with('error', 'Solo se pueden cancelar movimientos completados');
        }

        DB::beginTransaction();

        try {
            // Revertir stock
            $equipo = Equipo::find($movimiento->equipo_id);
            
            // Devolver stock al origen
            $equipo->actualizarStockEnSucursal(
                $movimiento->sucursal_origen_id, 
                $movimiento->cantidad, 
                'sumar'
            );

            // Quitar stock del destino
            $equipo->actualizarStockEnSucursal(
                $movimiento->sucursal_destino_id, 
                $movimiento->cantidad, 
                'restar'
            );

            // Actualizar movimiento
            $movimiento->estado = 'cancelado';
            $movimiento->descripcion = ($movimiento->descripcion ? $movimiento->descripcion . "\n" : '') 
                                     . 'Cancelado por: ' . Auth::user()->name . "\n"
                                     . 'Motivo: ' . $request->motivo_cancelacion;
            $movimiento->save();

            // Registrar movimiento de ajuste (para auditoría)
            MovimientoSucursal::create([
                'equipo_id' => $movimiento->equipo_id,
                'sucursal_origen_id' => $movimiento->sucursal_destino_id,
                'sucursal_destino_id' => $movimiento->sucursal_origen_id,
                'usuario_id' => Auth::id(),
                'cantidad' => $movimiento->cantidad,
                'tipo' => 'ajuste',
                'estado' => 'completado',
                'motivo' => 'CANCELACIÓN: ' . $request->motivo_cancelacion,
                'descripcion' => 'Reversión del movimiento #' . $movimiento->id,
                'fecha_movimiento' => now(),
                'fecha_confirmacion' => now(),
                'confirmado_por' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('movimientos.index')
                           ->with('success', 'Movimiento cancelado y stock revertido exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al cancelar el movimiento: ' . $e->getMessage()]);
        }
    }

    /**
     * Get stock of a product in a specific branch (API).
     */
    public function getStock(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
            'sucursal_id' => 'required|exists:sucursales,id',
        ]);

        $equipo = Equipo::find($request->equipo_id);
        $stock = $equipo->getStockEnSucursal($request->sucursal_id);

        return response()->json([
            'success' => true,
            'stock' => $stock,
            'equipo' => $equipo->nombre,
            'unidad' => $equipo->unidadMedida ? $equipo->unidadMedida->abreviatura : 'uds'
        ]);
    }

    /**
     * Get available branches for a product (API).
     */
    public function getSucursalesDisponibles(Request $request)
    {
        $request->validate([
            'equipo_id' => 'required|exists:equipos,id',
        ]);

        $sucursales = Sucursal::where('activa', true)->get();
        
        $resultado = $sucursales->map(function ($sucursal) use ($request) {
            $equipo = Equipo::find($request->equipo_id);
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