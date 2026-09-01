<?php

namespace App\Http\Controllers;

use App\Models\Renta;
use App\Models\MovimientoSucursal;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class AutorizacionController extends Controller
{
    public function index()
    {
        $sucursalId = session('activo_sucursal_id');
        $user = auth()->user();
        $isGlobalAdmin = $user->isAdmin() && $sucursalId === 'global';

        // 1. CONSULTAS DE PENDIENTES
        $queryRentasPendientes = \App\Models\Renta::with(['cliente', 'solicitadoPor'])->where('autorizacion_solicitada', true)->where('estado', 'activa');
        $queryMovimientosPendientes = \App\Models\MovimientoSucursal::with(['equipo', 'sucursalOrigen', 'sucursalDestino', 'usuario'])->where('tipo', 'transferencia')->where('estado', 'pendiente');
        $queryVentasPendientes = \App\Models\Venta::with(['cliente', 'solicitadoPor'])->where('autorizacion_solicitada', true)->where('estado', 'completada');

        // 2. CONSULTAS DE HISTORIAL (YA RESUELTAS)
        $queryHistorialRentas = \App\Models\Renta::with(['cliente', 'solicitadoPor', 'autorizadoPor'])->whereNotNull('autorizado_por_id');
        $queryHistorialMovimientos = \App\Models\MovimientoSucursal::with(['equipo', 'sucursalOrigen', 'sucursalDestino', 'usuario', 'confirmadoPor'])->where('tipo', 'transferencia')->whereNotNull('confirmado_por');
        $queryHistorialVentas = \App\Models\Venta::with(['cliente', 'solicitadoPor', 'autorizadoPor'])->whereNotNull('autorizado_por_id');

        if (!$isGlobalAdmin) {
            $queryRentasPendientes->where('sucursal_id', $sucursalId);
            $queryMovimientosPendientes->where('sucursal_origen_id', $sucursalId);
            $queryVentasPendientes->where('sucursal_id', $sucursalId);

            $queryHistorialRentas->where('sucursal_id', $sucursalId);
            $queryHistorialMovimientos->where('sucursal_origen_id', $sucursalId);
            $queryHistorialVentas->where('sucursal_id', $sucursalId);
        }

        $autorizacionesRentas = $queryRentasPendientes->latest()->get();
        $movimientosPendientes = $queryMovimientosPendientes->latest()->get();
        $autorizacionesVentas = $queryVentasPendientes->latest()->get();

        // 3. MAPEAR HISTORIALES A UN FORMATO COMÚN
        $historialRentas = $queryHistorialRentas->get()->map(function($renta) {
            return (object)[
                'tipo' => 'renta',
                'id' => $renta->id,
                'identificador' => $renta->folio,
                'entidad' => $renta->cliente->nombre_completo ?? 'Cliente General',
                'fecha' => $renta->updated_at,
                'solicitado_por' => $renta->solicitadoPor->name ?? 'Desconocido',
                'autorizado_por' => $renta->autorizadoPor->name ?? 'Desconocido',
                'estado' => $renta->estado === 'finalizada' ? 'aprobada' : 'rechazada',
            ];
        });

        $historialMovimientos = $queryHistorialMovimientos->get()->map(function($mov) {
            $estadoStr = in_array($mov->estado, ['aprobado', 'completado']) ? 'aprobada' : 'rechazada';
            return (object)[
                'tipo' => 'movimiento',
                'id' => $mov->id,
                'identificador' => 'Mov. ' . ($mov->equipo->codigo ?? 'Eq'),
                'entidad' => ($mov->sucursalOrigen->nombre ?? 'Origen') . ' ➔ ' . ($mov->sucursalDestino->nombre ?? 'Destino'),
                'fecha' => $mov->fecha_confirmacion ?? $mov->updated_at,
                'solicitado_por' => $mov->usuario->name ?? 'Desconocido',
                'autorizado_por' => $mov->confirmadoPor->name ?? 'Desconocido',
                'estado' => $estadoStr,
            ];
        });

        $historialVentas = $queryHistorialVentas->get()->map(function($venta) {
            // Si la venta está cancelada, el gerente APROBÓ la solicitud de cancelación.
            $estadoStr = $venta->estado === 'cancelada' ? 'aprobada' : 'rechazada';
            return (object)[
                'tipo' => 'venta',
                'id' => $venta->id,
                'identificador' => $venta->folio,
                'entidad' => $venta->cliente_nombre ?? 'Público General',
                'fecha' => $venta->updated_at,
                'solicitado_por' => $venta->solicitadoPor->name ?? 'Desconocido',
                'autorizado_por' => $venta->autorizadoPor->name ?? 'Desconocido',
                'estado' => $estadoStr,
            ];
        });

        // 4. COMBINAR Y ORDENAR
        $historialCompleto = $historialRentas->concat($historialMovimientos)->concat($historialVentas)->sortByDesc('fecha')->values();

        // 5. PAGINACIÓN MANUAL ESTÁNDAR
        $page = request()->get('page', 1);
        $perPage = 20;
        $historial = new \Illuminate\Pagination\LengthAwarePaginator(
            $historialCompleto->forPage($page, $perPage),
            $historialCompleto->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('autorizaciones.index', compact('autorizacionesRentas', 'movimientosPendientes', 'autorizacionesVentas', 'historial'));
    }

    // --- NUEVOS MÉTODOS PARA VENTAS ---
    public function aprobarVenta(\App\Models\Venta $venta)
    {
        try {
            DB::beginTransaction();

            // 1. Devolver el stock
            foreach ($venta->detalles as $detalle) {
                $producto = \App\Models\Equipo::find($detalle->equipo_id);
                if ($producto) {
                    if ($venta->sucursal_id) {
                        $producto->actualizarStockEnSucursal($venta->sucursal_id, $detalle->cantidad, 'sumar');
                    } else {
                        $producto->increment('stock', $detalle->cantidad);
                    }
                }
            }

            // 2. Descontar de la caja donde se realizó (solo si sigue abierta)
            $corte = \App\Models\CorteCaja::find($venta->corte_caja_id);
            if ($corte && $corte->estado === 'abierto') {
                $corte->decrement('total_ventas', $venta->total);
                if ($venta->metodo_pago === 'efectivo') $corte->decrement('total_efectivo', $venta->total);
                elseif ($venta->metodo_pago === 'transferencia') $corte->decrement('total_transferencias', $venta->total);
                elseif ($venta->metodo_pago === 'tarjeta') $corte->decrement('total_tarjetas', $venta->total);
                elseif ($venta->metodo_pago === 'mixto' && is_array($venta->pagos_mixtos)) {
                    foreach ($venta->pagos_mixtos as $pago) {
                        if ($pago['metodo'] === 'efectivo') $corte->decrement('total_efectivo', $pago['monto']);
                        elseif ($pago['metodo'] === 'transferencia') $corte->decrement('total_transferencias', $pago['monto']);
                        elseif ($pago['metodo'] === 'tarjeta') $corte->decrement('total_tarjetas', $pago['monto']);
                    }
                }
                $corte->save();
            }

            $venta->update([
                'estado' => 'cancelada',
                'autorizacion_solicitada' => false,
                'autorizado_por_id' => auth()->id(),
                'observaciones' => ($venta->observaciones ? $venta->observaciones . "\n" : '') . "[CANCELADA POR GERENTE] Motivo: " . $venta->motivo_cancelacion
            ]);

            DB::commit();
            return back()->with('success', 'La cancelación de la venta fue aprobada y los fondos/stock se han restaurado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar la cancelación: ' . $e->getMessage());
        }
    }

    public function rechazarVenta(\App\Models\Venta $venta)
    {
        $venta->update([
            'autorizacion_solicitada' => false,
            'autorizado_por_id' => auth()->id(),
            'observaciones' => ($venta->observaciones ? $venta->observaciones . "\n" : '') . "[CANCELACIÓN DENEGADA POR GERENTE]"
        ]);
        return back()->with('success', 'Solicitud de cancelación rechazada.');
    }

    public function aprobar(Renta $renta)
    {
        try {
            DB::transaction(function() use ($renta) {
                $gerente = auth()->user()->name;
                $cajero = $renta->solicitadoPor ? $renta->solicitadoPor->name : 'Usuario Desconocido';
                
                $registroAuditoria = "\n\n[AUTORIZACIÓN APROBADA - " . now()->format('d/m/Y H:i') . "]";
                $registroAuditoria .= "\nSolicitó: {$cajero}";
                $registroAuditoria .= "\nAutorizó: {$gerente}";
                $registroAuditoria .= "\nAcción: Permiso concedido para finalizar el contrato con adeudo.";

                $renta->observaciones = $renta->observaciones ? $renta->observaciones . $registroAuditoria : $registroAuditoria;

                $renta->autorizado_por_id = auth()->id();
                $renta->autorizacion_solicitada = false;
                $renta->autorizacion_aprobada = true;
                $renta->save();
            });

            return back()->with('success', 'Finalización con adeudo aprobada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar la autorización: ' . $e->getMessage());
        }
    }

    public function rechazar(Renta $renta)
    {
        $renta->load('solicitadoPor');
        $gerente = auth()->user()->name;
        $cajero = $renta->solicitadoPor ? $renta->solicitadoPor->name : 'Usuario Desconocido';

        $registroAuditoria = "\n\n[AUTORIZACIÓN RECHAZADA - " . now()->format('d/m/Y H:i') . "]";
        $registroAuditoria .= "\nSolicitó: {$cajero}";
        $registroAuditoria .= "\nRechazó: {$gerente}";
        $registroAuditoria .= "\nAcción: Se denegó la petición de finalizar la renta con adeudo.";

        $renta->update([
            'autorizacion_solicitada' => false,
            'autorizacion_aprobada' => false,
            'datos_pendientes_finalizacion' => null,
            'motivo_autorizacion' => null,
            'autorizado_por_id' => auth()->id(),
            'observaciones' => $renta->observaciones ? $renta->observaciones . $registroAuditoria : $registroAuditoria
        ]);

        return back()->with('success', 'Solicitud rechazada.');
    }

    /**
     * CONTADOR DE NOTIFICACIONES EN TIEMPO REAL (Rentas + Transferencias)
     */
    public function notificaciones()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isGerente()) return response()->json(['count' => 0]);

        $sucursalId = session('activo_sucursal_id');
        $isGlobalAdmin = auth()->user()->isAdmin() && $sucursalId === 'global';

        $qRentas = \App\Models\Renta::where('autorizacion_solicitada', true)->where('estado', 'activa');
        $qMovimientos = \App\Models\MovimientoSucursal::where('tipo', 'transferencia')->where('estado', 'pendiente');
        $qVentas = \App\Models\Venta::where('autorizacion_solicitada', true)->where('estado', 'completada'); // <- Agregado

        if (!$isGlobalAdmin) {
            $qRentas->where('sucursal_id', $sucursalId);
            $qMovimientos->where('sucursal_origen_id', $sucursalId);
            $qVentas->where('sucursal_id', $sucursalId);
        }

        $totalPendientes = $qRentas->count() + $qMovimientos->count() + $qVentas->count();
        return response()->json(['count' => $totalPendientes]);
    }
}