<?php

namespace App\Http\Controllers;

use App\Models\Renta;
use App\Models\MovimientoSucursal;
use App\Models\Equipo;
use App\Models\SolicitudDescuento;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class AutorizacionController extends Controller
{

    private function validarPermisoSucursal($sucursal_id_operacion)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isGerente() && session('activo_sucursal_id') == $sucursal_id_operacion) {
            return true;
        }

        abort(403, 'Acceso Denegado: No tienes permisos para autorizar operaciones de otra sucursal.');
    }


    public function index()
    {
        $sucursalId = session('activo_sucursal_id');
        $user = auth()->user();
        $isGlobalAdmin = $user->isAdmin() && $sucursalId === 'global';
        $sucursalesNombres = Sucursal::pluck('nombre', 'id');

        // 1. CONSULTAS DE PENDIENTES
        $queryRentasPendientes = \App\Models\Renta::with(['cliente', 'solicitadoPor', 'detalles', 'sucursal'])->where('autorizacion_solicitada', true)->where('estado', 'activa');
        $queryMovimientosPendientes = \App\Models\MovimientoSucursal::with(['equipo', 'sucursalOrigen', 'sucursalDestino', 'usuario'])->where('tipo', 'transferencia')->where('estado', 'pendiente');
        
        $queryVentasPendientes = \App\Models\Venta::with(['cliente', 'solicitadoPor', 'sucursal'])->where('autorizacion_solicitada', true)->where('estado', 'completada');
        $queryDescuentosPendientes = SolicitudDescuento::with('user')->where('estado', 'pendiente');

        // 2. CONSULTAS DE HISTORIAL (YA RESUELTAS)
        $queryHistorialRentas = \App\Models\Renta::with(['cliente', 'solicitadoPor', 'autorizadoPor'])->whereNotNull('autorizado_por_id');
        $queryHistorialMovimientos = \App\Models\MovimientoSucursal::with(['equipo', 'sucursalOrigen', 'sucursalDestino', 'usuario', 'confirmadoPor'])->where('tipo', 'transferencia')->whereNotNull('confirmado_por');
        $queryHistorialVentas = \App\Models\Venta::with(['cliente', 'solicitadoPor', 'autorizadoPor'])->whereNotNull('autorizado_por_id');
        $queryHistorialDescuentos = SolicitudDescuento::with(['user', 'autorizador'])->whereIn('estado', ['aprobada', 'usada', 'rechazada']);

        if (!$isGlobalAdmin) {
            $queryRentasPendientes->where('sucursal_id', $sucursalId);
            $queryMovimientosPendientes->where('sucursal_origen_id', $sucursalId);
            $queryVentasPendientes->where('sucursal_id', $sucursalId);
            $queryDescuentosPendientes->where('sucursal_id', $sucursalId);

            $queryHistorialRentas->where('sucursal_id', $sucursalId);
            $queryHistorialMovimientos->where('sucursal_origen_id', $sucursalId);
            $queryHistorialVentas->where('sucursal_id', $sucursalId);
            $queryHistorialDescuentos->where('sucursal_id', $sucursalId);
        }

        $autorizacionesRentasAll = $queryRentasPendientes->latest()->get();

        foreach ($autorizacionesRentasAll as $renta) {
            $multaCalculada = 0;
            $hoy = now()->startOfDay();
            $fechaFin = \Carbon\Carbon::parse($renta->fecha_fin)->startOfDay();

            if ($hoy > $fechaFin) {
                $diasRetraso = $fechaFin->diffInDays($hoy);
                $costoDiario = 0;
                foreach ($renta->detalles as $detalle) {
                    $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta;
                    if ($pendiente > 0) {
                        $costoDiario += ($detalle->precio_dia * $pendiente);
                    }
                }
                $multaCalculada = $diasRetraso * $costoDiario;
            }
            
            $renta->total_real = $renta->total + $multaCalculada;
            $renta->saldo_pendiente_real = $renta->saldo_pendiente + $multaCalculada;
        }
        
        // Separamos usando el prefijo mágico [CANCELACION]
        $autorizacionesRentas = $autorizacionesRentasAll->filter(function($r) {
            return !str_starts_with($r->motivo_autorizacion, '[CANCELACION]');
        });
        
        $rentasCancelacion = $autorizacionesRentasAll->filter(function($r) {
            return str_starts_with($r->motivo_autorizacion, '[CANCELACION]');
        });

        $movimientosPendientes = $queryMovimientosPendientes->latest()->get();
        $autorizacionesVentas = $queryVentasPendientes->latest()->get();
        $descuentosPendientes = $queryDescuentosPendientes->latest()->get();

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

        $historialDescuentos = $queryHistorialDescuentos->get()->map(function($desc) use ($sucursalesNombres) {
            return (object)[
                'tipo' => 'descuento',
                'id' => $desc->id,
                'venta_id' => $desc->venta_id,
                'identificador' => (($desc->tipo ?? 'descuento') === 'credito' ? 'Crédito $' : 'Desc. $') . number_format($desc->monto, 2) . (($desc->tipo ?? 'descuento') === 'credito' && $desc->dias_credito ? ' · ' . $desc->dias_credito . ' días' : ''),
                'entidad' => $desc->cliente_nombre ?: 'Público General',
                'sucursal' => $sucursalesNombres[$desc->sucursal_id] ?? null,
                'fecha' => $desc->resuelta_at ?? $desc->updated_at,
                'solicitado_por' => $desc->user->name ?? 'Desconocido',
                'autorizado_por' => $desc->autorizador->name ?? 'Desconocido',
                // 'aprobada' y 'usada' (aprobada y ya aplicada en una venta) cuentan como aprobadas
                'estado' => $desc->estado === 'rechazada' ? 'rechazada' : 'aprobada',
            ];
        });

        // 4. COMBINAR Y ORDENAR
        $historialCompleto = $historialRentas->concat($historialMovimientos)->concat($historialVentas)->concat($historialDescuentos)->sortByDesc('fecha')->values();

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

        return view('autorizaciones.index', compact('autorizacionesRentas', 'rentasCancelacion', 'movimientosPendientes', 'autorizacionesVentas', 'descuentosPendientes', 'historial', 'sucursalesNombres'));
    }


    // --- NUEVOS MÉTODOS PARA VENTAS ---
    public function aprobarVenta(\App\Models\Venta $venta)
    {
        $this->validarPermisoSucursal($venta->sucursal_id); // 🔒 Protección

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
                'observaciones' => ($venta->observaciones ? $venta->observaciones . "\n" : '') . "[CANCELADA POR AUTORIZADOR] Motivo: " . $venta->motivo_cancelacion
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
        $this->validarPermisoSucursal($venta->sucursal_id); // 🔒 Protección

        $venta->update([
            'autorizacion_solicitada' => false,
            'autorizado_por_id' => auth()->id(),
            'observaciones' => ($venta->observaciones ? $venta->observaciones . "\n" : '') . "[CANCELACIÓN DENEGADA POR AUTORIZADOR]"
        ]);
        return back()->with('success', 'Solicitud de cancelación rechazada.');
    }

    // --- SOLICITUDES DE DESCUENTO DEL PUNTO DE VENTA ---
    public function aprobarDescuento(SolicitudDescuento $solicitud)
    {
        return $this->resolverDescuento($solicitud, true);
    }

    public function rechazarDescuento(SolicitudDescuento $solicitud)
    {
        return $this->resolverDescuento($solicitud, false);
    }

    private function resolverDescuento(SolicitudDescuento $solicitud, bool $aprobar)
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isGerente(), 403);

        // Solo se puede resolver una solicitud de la sucursal activa (el admin en modo global puede todas)
        $sucursalActiva = session('activo_sucursal_id');
        $esAdminGlobal = auth()->user()->isAdmin() && $sucursalActiva === 'global';
        if (!$esAdminGlobal && $solicitud->sucursal_id && (int) $solicitud->sucursal_id !== (int) $sucursalActiva) {
            abort(403, 'Esta solicitud pertenece a otra sucursal.');
        }

        if ($solicitud->estado !== 'pendiente') {
            return back()->with('error', 'Esta solicitud de descuento ya fue resuelta o cancelada por el cajero.');
        }

        $solicitud->update([
            'estado'            => $aprobar ? 'aprobada' : 'rechazada',
            'autorizado_por_id' => auth()->id(),
            'resuelta_at'       => now(),
        ]);

        $esCredito = ($solicitud->tipo ?? 'descuento') === 'credito';

        return back()->with('success', $aprobar
            ? ($esCredito ? 'Crédito de $' : 'Descuento de $') . number_format($solicitud->monto, 2) . ' aprobado. El cajero ya puede registrar la venta.'
            : 'Solicitud de ' . ($esCredito ? 'crédito' : 'descuento') . ' rechazada.');
    }

    public function aprobarCancelacionRenta(\App\Models\Renta $renta)
    {
        $this->validarPermisoSucursal($renta->sucursal_id); // 🔒 Protección

        try {
            DB::beginTransaction();

            $renta->load('detalles.equipo');
            foreach ($renta->detalles as $detalle) {
                $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta;
                if ($pendiente > 0) {
                    $equipo = $detalle->equipo;
                    if ($renta->sucursal_id) {
                        $equipo->actualizarStockEnSucursal($renta->sucursal_id, $pendiente, 'sumar');
                    } else {
                        $equipo->stock += $pendiente;
                        $equipo->save();
                    }
                    $detalle->cantidad_devuelta = $detalle->cantidad;
                    $detalle->save();
                }
            }

            // Quitamos la etiqueta técnica para el texto visible
            $motivoLimpio = str_replace('[CANCELACION] ', '', $renta->motivo_autorizacion);

            $renta->update([
                'estado' => 'cancelada',
                'fecha_devolucion' => now(),
                'autorizacion_solicitada' => false,
                'autorizado_por_id' => auth()->id(),
                'observaciones' => ($renta->observaciones ? $renta->observaciones . "\n" : '') . "\n[CANCELACIÓN APROBADA POR AUTORIZADOR] Motivo: " . $motivoLimpio
            ]);

            DB::commit();
            return back()->with('success', 'La cancelación de la renta fue aprobada y los equipos restaurados.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar la cancelación: ' . $e->getMessage());
        }
    }

    public function rechazarCancelacionRenta(\App\Models\Renta $renta)
    {
        $this->validarPermisoSucursal($renta->sucursal_id); // 🔒 Protección

        $renta->update([
            'autorizacion_solicitada' => false,
            'motivo_autorizacion' => null,
            'autorizado_por_id' => auth()->id(),
            'observaciones' => ($renta->observaciones ? $renta->observaciones . "\n" : '') . "\n[CANCELACIÓN DENEGADA POR AUTORIZADOR]"
        ]);
        return back()->with('success', 'Solicitud de cancelación rechazada. El contrato sigue activo.');
    }


    public function aprobar(Renta $renta)
    {
        $this->validarPermisoSucursal($renta->sucursal_id); // 🔒 Protección

        try {
            DB::transaction(function() use ($renta) {
                $autorizador = auth()->user()->name;
                $cajero = $renta->solicitadoPor ? $renta->solicitadoPor->name : 'Usuario Desconocido';
                
                $registroAuditoria = "\n\n[AUTORIZACIÓN APROBADA - " . now()->format('d/m/Y H:i') . "]";
                $registroAuditoria .= "\nSolicitó: {$cajero}";
                $registroAuditoria .= "\nAutorizó: {$autorizador}";
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
        $this->validarPermisoSucursal($renta->sucursal_id); // 🔒 Protección

        $renta->load('solicitadoPor');
        $autorizador = auth()->user()->name;
        $cajero = $renta->solicitadoPor ? $renta->solicitadoPor->name : 'Usuario Desconocido';

        $registroAuditoria = "\n\n[AUTORIZACIÓN RECHAZADA - " . now()->format('d/m/Y H:i') . "]";
        $registroAuditoria .= "\nSolicitó: {$cajero}";
        $registroAuditoria .= "\nRechazó: {$autorizador}";
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
     * CONTADOR DE NOTIFICACIONES EN TIEMPO REAL (Rentas + Transferencias + Ventas + Descuentos)
     */
    public function notificaciones()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isGerente()) return response()->json(['count' => 0]);

        $sucursalId = session('activo_sucursal_id');
        $isGlobalAdmin = auth()->user()->isAdmin() && $sucursalId === 'global';

        $qRentas = \App\Models\Renta::where('autorizacion_solicitada', true)->where('estado', 'activa');
        $qMovimientos = \App\Models\MovimientoSucursal::where('tipo', 'transferencia')->where('estado', 'pendiente');
        
        $qVentas = \App\Models\Venta::where('autorizacion_solicitada', true)->where('estado', 'completada');
        $qDescuentos = SolicitudDescuento::where('estado', 'pendiente');

        // Filtro para el Gerente:
        if (!$isGlobalAdmin) {
            // NOTA: Si un admin cambia temporalmente su sesión a una sucursal específica
            // (no global), el contador también le mostrará solo los de esa sucursal. 
            // Esto es correcto a nivel visual.
            $qRentas->where('sucursal_id', $sucursalId);
            $qMovimientos->where('sucursal_origen_id', $sucursalId);
            $qVentas->where('sucursal_id', $sucursalId);
            $qDescuentos->where('sucursal_id', $sucursalId);
        }

        $totalPendientes = $qRentas->count() + $qMovimientos->count() + $qVentas->count() + $qDescuentos->count();
        return response()->json(['count' => $totalPendientes]);
    }
}