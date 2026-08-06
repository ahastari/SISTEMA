<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Sucursal;
use App\Models\MovimientoSucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        
        $querySucursales = Sucursal::where('activa', true);
        if ($sucursalActivaId && $sucursalActivaId !== 'global') {
            $querySucursales->where('id', '!=', $sucursalActivaId);
        }
        $sucursales = $querySucursales->get();
        
        $transferenciasAprobadas = MovimientoSucursal::with(['equipo', 'sucursalOrigen'])
            ->where('tipo', 'transferencia')
            ->where('estado', 'aprobado')
            ->where('sucursal_destino_id', $sucursalActivaId)
            ->get();

        $equipos = Equipo::where('activo', true)
            ->with(['categoria', 'unidadMedida', 'sucursales'])
            ->get();
        
        $equipoSeleccionado = $request->get('equipo_id');
        
        return view('movimientos.create', compact('sucursales', 'equipos', 'sucursalActivaId', 'equipoSeleccionado', 'transferenciasAprobadas'));
    }

    /**
     * API: Consultar stock disponible
     */
    public function getStock(Request $request)
    {
        $equipoId = $request->get('equipo_id');
        $sucursalId = $request->get('sucursal_id');
        
        if (!$equipoId || !$sucursalId) {
            return response()->json(['success' => false, 'message' => 'Faltan parámetros requeridos'], 400);
        }
        
        $equipo = Equipo::find($equipoId);
        
        if (!$equipo) {
            return response()->json(['success' => false, 'message' => 'Equipo no encontrado'], 404);
        }
        
        $stockDisponible = $equipo->getStockEnSucursal($sucursalId);
        $unidad = $equipo->unidadMedida ? $equipo->unidadMedida->abreviatura : 'unidades';
        
        return response()->json([
            'success' => true,
            'stock' => $stockDisponible,
            'stock_real' => $stockDisponible,
            'stock_reservado' => 0,
            'unidad' => $unidad,
            'equipo' => $equipo->nombre
        ]);
    }

    /**
     * GUARDAR MOVIMIENTO
     */
    public function store(Request $request)
    {
        $sucursalActualId = session('activo_sucursal_id');

        if ($request->tipo === 'entrada') {
            $request->validate([
                'movimiento_autorizado_id' => 'required|exists:movimientos_sucursales,id',
                'motivo' => 'required|string|max:255',
                'descripcion' => 'nullable|string'
            ]);

            DB::beginTransaction();

            try {
                $movimientoOriginal = MovimientoSucursal::findOrFail($request->movimiento_autorizado_id);

                if ($movimientoOriginal->estado !== 'aprobado') {
                    return back()->with('error', 'Este envío no ha sido autorizado por el gerente de la sucursal de origen.');
                }

                if ($movimientoOriginal->sucursal_destino_id != $sucursalActualId) {
                    return back()->with('error', 'Este envío no está dirigido a tu sucursal.');
                }

                $equipo = Equipo::findOrFail($movimientoOriginal->equipo_id);

                $equipo->actualizarStockEnSucursal($sucursalActualId, $movimientoOriginal->cantidad, 'sumar');

                $movimientoOriginal->update([
                    'estado' => 'completado',
                    'fecha_confirmacion' => now(),
                    'confirmado_por' => auth()->id(),
                    'descripcion' => $movimientoOriginal->descripcion 
                        ? $movimientoOriginal->descripcion . " | Recepción: " . $request->descripcion 
                        : "Recepción: " . $request->descripcion,
                ]);

                DB::commit();

                return redirect()->route('movimientos.index')
                    ->with('success', 'Ingreso de mercancía confirmado. Stock actualizado.');
                    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error en ingreso: ' . $e->getMessage());
                return back()->with('error', 'Error al procesar el ingreso: ' . $e->getMessage());
            }
        } else {
            $request->validate([
                'equipo_id' => 'required|exists:equipos,id',
                'sucursal_destino_id' => 'required|exists:sucursales,id',
                'cantidad' => 'required|integer|min:1',
                'motivo' => 'required|string|max:255',
                'descripcion' => 'nullable|string'
            ]);

            $equipo = Equipo::findOrFail($request->equipo_id);
            $cantidad = $request->cantidad;
            $sucursalOrigenId = $sucursalActualId;

            $stockDisponible = $equipo->getStockEnSucursal($sucursalOrigenId);
            
            if ($cantidad > $stockDisponible) {
                return back()->with('error', 
                    "No hay suficiente stock disponible para apartar. " .
                    "Cantidad disponible en tienda: {$stockDisponible}"
                );
            }

            DB::beginTransaction();

            try {
                $equipo->actualizarStockEnSucursal($sucursalOrigenId, $cantidad, 'restar');

                MovimientoSucursal::create([
                    'equipo_id' => $equipo->id,
                    'sucursal_origen_id' => $sucursalOrigenId,
                    'sucursal_destino_id' => $request->sucursal_destino_id,
                    'usuario_id' => auth()->id(),
                    'cantidad' => $cantidad,
                    'tipo' => 'transferencia',
                    'estado' => 'pendiente',
                    'motivo' => $request->motivo,
                    'descripcion' => $request->descripcion,
                    'fecha_movimiento' => now(),
                ]);

                DB::commit();

                return redirect()->route('movimientos.index')
                    ->with('success', 'Solicitud registrada. El stock ha sido apartado y está pendiente de autorización.');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error en envío: ' . $e->getMessage());
                return back()->with('error', 'Error al procesar: ' . $e->getMessage());
            }
        }
    }

    /**
     * Mostrar detalle
     */
    public function show(MovimientoSucursal $movimiento)
    {
        $movimiento->load(['equipo', 'sucursalOrigen', 'sucursalDestino', 'usuario', 'confirmadoPor']);
        return view('movimientos.show', compact('movimiento'));
    }

    /**
     * APROBAR TRANSFERENCIA
     */
    public function aprobar(MovimientoSucursal $movimiento)
    {
        if (auth()->user()->isCajero()) {
            return back()->with('error', 'No tienes permisos para aprobar transferencias.');
        }

        $sucursalActualId = session('activo_sucursal_id');
        if ($sucursalActualId !== 'global' && $movimiento->sucursal_origen_id != $sucursalActualId) {
            return back()->with('error', 'Solo el gerente de la sucursal de origen puede aprobar esta transferencia.');
        }

        if ($movimiento->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden aprobar movimientos pendientes.');
        }

        DB::beginTransaction();
        
        try {
            $movimiento->update([
                'estado' => 'aprobado',
                'fecha_confirmacion' => now(),
                'confirmado_por' => auth()->id(),
            ]);
            
            DB::commit();
            
            Log::info('Transferencia aprobada exitosamente', ['movimiento_id' => $movimiento->id]);
            return back()->with('success', 'Transferencia aprobada. El envío ahora está en tránsito.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al aprobar: ' . $e->getMessage());
        }
    }

    /**
     * RECHAZAR TRANSFERENCIA
     */
    public function rechazar(MovimientoSucursal $movimiento)
    {
        if (auth()->user()->isCajero()) {
            return back()->with('error', 'No tienes permisos para rechazar transferencias.');
        }

        $sucursalActualId = session('activo_sucursal_id');
        if ($sucursalActualId !== 'global' && $movimiento->sucursal_origen_id != $sucursalActualId) {
            return back()->with('error', 'Solo el gerente de la sucursal de origen puede rechazar esta transferencia.');
        }
        
        if ($movimiento->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden rechazar movimientos pendientes.');
        }
        
        DB::beginTransaction();
        try {
            $equipo = Equipo::findOrFail($movimiento->equipo_id);
            $equipo->actualizarStockEnSucursal($movimiento->sucursal_origen_id, $movimiento->cantidad, 'sumar');

            $movimiento->update([
                'estado' => 'rechazado',
                'fecha_confirmacion' => now(),
                'confirmado_por' => auth()->id(),
            ]);
            
            DB::commit();
            return back()->with('success', 'Transferencia rechazada. El stock apartado ha sido devuelto a la sucursal.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al rechazar: ' . $e->getMessage());
        }
    }

    /**
     * CONFIRMAR RECEPCIÓN
     */
    public function confirmarRecepcion(MovimientoSucursal $movimiento)
    {
        if ($movimiento->estado !== 'aprobado') {
            return back()->with('error', 'Solo se pueden recibir movimientos aprobados.');
        }
        
        $sucursalActualId = session('activo_sucursal_id');
        
        if ($movimiento->sucursal_destino_id != $sucursalActualId) {
            return back()->with('error', 'Este envío no está dirigido a tu sucursal.');
        }
        
        DB::beginTransaction();
        
        try {
            $equipo = Equipo::findOrFail($movimiento->equipo_id);
            
            $equipo->actualizarStockEnSucursal($movimiento->sucursal_destino_id, $movimiento->cantidad, 'sumar');
            
            $movimiento->update([
                'estado' => 'completado',
                'fecha_confirmacion' => now(),
                'confirmado_por' => auth()->id(),
            ]);
            
            DB::commit();
            
            return back()->with('success', 'Recepción confirmada. Stock actualizado.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en recepción: ' . $e->getMessage());
            return back()->with('error', 'Error al confirmar recepción: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar movimiento
     */
    public function procesarCancelacion(MovimientoSucursal $movimiento)
    {
        if (auth()->user()->isCajero()) {
            return back()->with('error', 'No tienes permisos para cancelar movimientos.');
        }

        if (in_array($movimiento->estado, ['cancelado', 'rechazado'])) {
            return back()->with('error', 'El movimiento ya está inactivo.');
        }

        $equipo = Equipo::findOrFail($movimiento->equipo_id);

        DB::beginTransaction();

        try {
            if ($movimiento->tipo === 'transferencia') {
                if ($movimiento->estado === 'completado') {
                    if (!$equipo->disponibleEnSucursal($movimiento->sucursal_destino_id, $movimiento->cantidad)) {
                        DB::rollBack();
                        return back()->with('error', 'No hay suficiente stock en destino para revertir.');
                    }
                    $equipo->actualizarStockEnSucursal($movimiento->sucursal_destino_id, $movimiento->cantidad, 'restar');
                    $equipo->actualizarStockEnSucursal($movimiento->sucursal_origen_id, $movimiento->cantidad, 'sumar');

                } elseif ($movimiento->estado === 'aprobado') {
                    $equipo->actualizarStockEnSucursal($movimiento->sucursal_origen_id, $movimiento->cantidad, 'sumar');

                } elseif ($movimiento->estado === 'pendiente') {
                    $equipo->actualizarStockEnSucursal($movimiento->sucursal_origen_id, $movimiento->cantidad, 'sumar');
                }
            } elseif ($movimiento->tipo === 'entrada' || $movimiento->tipo === 'ajuste') {
                if (!$equipo->disponibleEnSucursal($movimiento->sucursal_destino_id, $movimiento->cantidad)) {
                    DB::rollBack();
                    return back()->with('error', 'No hay suficiente stock para revertir.');
                }
                $equipo->actualizarStockEnSucursal($movimiento->sucursal_destino_id, $movimiento->cantidad, 'restar');
            } elseif ($movimiento->tipo === 'salida') {
                $equipo->actualizarStockEnSucursal($movimiento->sucursal_origen_id, $movimiento->cantidad, 'sumar');
            }

            $movimiento->update(['estado' => 'cancelado']);

            DB::commit();

            return redirect()->route('movimientos.index')->with('success', 'Movimiento cancelado correctamente. Stock devuelto.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cancelar: ' . $e->getMessage());
            return back()->with('error', 'Error al cancelar: ' . $e->getMessage());
        }
    }
}