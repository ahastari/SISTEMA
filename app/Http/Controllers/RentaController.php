<?php

namespace App\Http\Controllers;

use App\Models\Renta;
use App\Models\DetalleRenta;
use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class RentaController extends Controller
{
    public function index()
    {
        $sucursalId = session('activo_sucursal_id');
        $isGlobalAdmin = auth()->user()->isAdmin() && $sucursalId === 'global';

        // 1. CALCULAR ESTADÍSTICAS GLOBALES DE DINERO (Excluyendo canceladas)
        $queryStats = Renta::query()->where('estado', '!=', 'cancelada');
        
        if (!$isGlobalAdmin) {
            $queryStats->where('sucursal_id', $sucursalId);
        }
        
        $totalFacturado = $queryStats->sum('total');
        $totalDepositos = $queryStats->sum('deposito');
        
        $rentasIds = $queryStats->pluck('id');
        $totalPagos = Pago::whereIn('renta_id', $rentasIds)->sum('monto');
        
        $totalPagado = $totalDepositos + $totalPagos;
        $totalPendiente = $totalFacturado - $totalPagado;

        // 2. OBTENER LISTA DE RENTAS PARA LA TABLA
        $query = Renta::with(['cliente', 'detalles'])->latest();

        if (!$isGlobalAdmin) {
            $query->where('sucursal_id', $sucursalId);
        }

        $rentas = $query->paginate(10);

        foreach ($rentas as $renta) {
            $renta->total_real = $renta->total;

            if ($renta->estado == 'activa') {
                $hoy = now()->startOfDay();
                $fechaFin = $renta->fecha_fin->startOfDay();

                if ($hoy <= $fechaFin) {
                    $renta->dias_restantes = $hoy->diffInDays($fechaFin) + 1;
                } else {
                    $renta->dias_restantes = 0;
                    $diasRetraso = $fechaFin->diffInDays($hoy);
                    
                    $costoDiarioRenta = 0;
                    foreach ($renta->detalles as $detalle) {
                        $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta;
                        if ($pendiente > 0) {
                            $costoDiarioRenta += ($detalle->precio_dia * $pendiente);
                        }
                    }
                    $renta->total_real += ($diasRetraso * $costoDiarioRenta);
                }
            } else {
                $renta->dias_restantes = null;
            }
        }

        return view('rentas.index', compact('rentas', 'isGlobalAdmin', 'totalFacturado', 'totalPagado', 'totalPendiente'));
    }

    public function create()
    {
        $sucursalId = session('activo_sucursal_id');
        $isGlobalAdmin = auth()->user()->isAdmin() && $sucursalId === 'global';

        $clientes = Cliente::orderBy('nombre_completo')->get();

        $equiposQuery = Equipo::where('activo', true)
            ->whereIn('tipo_operacion', ['renta', 'ambas']);

        if (!$isGlobalAdmin) {
            $equiposQuery->whereHas('sucursales', function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)->where('stock', '>', 0);
            });
        } else {
            $equiposQuery->where('stock', '>', 0);
        }

        $equipos = $equiposQuery->get();

        if (!$isGlobalAdmin) {
            foreach ($equipos as $equipo) {
                $equipo->stock = $equipo->getStockEnSucursal($sucursalId);
            }
        }

        $folio = Renta::generarFolio($sucursalId !== 'global' ? $sucursalId : null);

        return view('rentas.create', compact('clientes', 'equipos', 'folio'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'deposito' => 'nullable|numeric|min:0',
            'flete' => 'nullable|numeric|min:0',
            'mano_obra' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
            'requiere_factura' => 'required|in:0,1',
            'equipos' => 'required|array|min:1',
            'equipos.*.id' => 'required|exists:equipos,id',
            'equipos.*.cantidad' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $sucursalId = session('activo_sucursal_id');
            $isGlobalAdmin = auth()->user()->isAdmin() && $sucursalId === 'global';
            $sucursalIdGuardar = ($sucursalId && $sucursalId !== 'global') ? $sucursalId : null;

            $diasTotales = Renta::calcularDias($request->fecha_inicio, $request->fecha_fin);

            // 🔥 CORRECCIÓN: Inicializamos la variable correcta aquí
            $subtotalEquipos = 0;
            $detalles = [];

            foreach ($request->equipos as $item) {
                $equipo = Equipo::find($item['id']);

                if (!$isGlobalAdmin) {
                    $stockDisponible = $equipo->getStockEnSucursal($sucursalIdGuardar);
                    if ($stockDisponible < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente en esta sucursal para {$equipo->nombre}. Disponible: {$stockDisponible}");
                    }
                    $equipo->actualizarStockEnSucursal($sucursalIdGuardar, $item['cantidad'], 'restar');
                } else {
                    if ($equipo->stock < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente para {$equipo->nombre}. Disponible: {$equipo->stock}");
                    }
                    $equipo->stock -= $item['cantidad'];
                    $equipo->save();
                }

                $subtotalEquipo = $equipo->precio_dia * $item['cantidad'] * $diasTotales;

                // 🔥 Sumamos el costo de los equipos a la variable correcta
                $subtotalEquipos += $subtotalEquipo;

                $detalles[] = [
                    'equipo_id' => $equipo->id,
                    'cantidad' => $item['cantidad'],
                    'precio_dia' => $equipo->precio_dia,
                    'dias' => $diasTotales,
                    'subtotal' => $subtotalEquipo
                ];
            }

            // 🔥 Obtenemos flete y mano de obra
            $flete = $request->flete ?? 0;
            $manoObra = $request->mano_obra ?? 0;

            // Sumamos el costo de los equipos + flete + mano de obra para el subtotal real
            $subtotal = $subtotalEquipos + $flete + $manoObra;

            $requiereFactura = $request->requiere_factura == '1';
            $iva = $requiereFactura ? ($subtotal * 0.16) : 0;
            $total = $subtotal + $iva;
            $deposito = $request->deposito ?? 0;

            $folioGenerado = Renta::generarFolio($sucursalIdGuardar);

            $renta = Renta::create([
                'folio' => $folioGenerado,
                'cliente_id' => $request->cliente_id,
                'sucursal_id' => $sucursalIdGuardar,
                'obra_id' => $request->obra_id,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'dias_totales' => $diasTotales,
                'subtotal' => $subtotal,
                'flete' => $flete,
                'mano_obra' => $manoObra,
                'iva' => $iva,
                'total' => $total,
                'deposito' => $deposito,
                'facturar' => $requiereFactura,
                'observaciones' => $request->observaciones,
                'estado' => 'activa'
            ]);

            if ($sucursalIdGuardar && $sucursalIdGuardar !== 'global') {
                $sucursal = \App\Models\Sucursal::find($sucursalIdGuardar);
                if ($sucursal) {
                    $sucursal->siguiente_folio_rentas = $sucursal->siguiente_folio_rentas >= 4000 
                        ? $sucursal->siguiente_folio_rentas + 1 
                        : 4001;
                    $sucursal->save();
                }
            } else {
                $folioGlobal = \App\Models\Configuracion::where('key', 'folio_global_rentas')->first();
                if ($folioGlobal) {
                    $folioGlobal->value = $folioGlobal->value + 1;
                    $folioGlobal->save();
                    \Illuminate\Support\Facades\Cache::forget('config_folio_global_rentas');
                } else {
                    \App\Models\Configuracion::set('folio_global_rentas', 4001);
                }
            }

            foreach ($detalles as $detalle) {
                $detalle['renta_id'] = $renta->id;
                DetalleRenta::create($detalle);
            }

            DB::commit();

            return redirect()->route('rentas.show', $renta)
                ->with('success', 'Renta creada exitosamente. Folio: ' . $renta->folio);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Renta $renta)
    {
        $renta->load('cliente', 'detalles.equipo', 'obra', 'pagos', 'sucursal');

        $diasRestantes = 0;
        $diasRetraso = 0;
        $multaCalculada = 0;
        $motivoMulta = '';

        if ($renta->estado == 'activa') {
            $hoy = now()->startOfDay();
            $fechaFin = $renta->fecha_fin->startOfDay();

            if ($hoy <= $fechaFin) {
                $diasRestantes = $hoy->diffInDays($fechaFin) + 1;
            } else {
                $diasRetraso = $fechaFin->diffInDays($hoy);

                $costoDiarioRenta = 0;
                foreach ($renta->detalles as $detalle) {
                    // Multa SOLO por artículos pendientes
                    $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta;
                    if ($pendiente > 0) {
                        $costoDiarioRenta += ($detalle->precio_dia * $pendiente);
                    }
                }

                if ($costoDiarioRenta > 0) {
                    $multaCalculada = $diasRetraso * $costoDiarioRenta;
                    $motivoMulta = "Cobro por {$diasRetraso} día(s) extra de renta (Costo equipo: $" . number_format($costoDiarioRenta, 2) . "/día)";
                }
            }
        }

        $puedeEditarMulta = auth()->user()->isAdmin() || auth()->user()->isGerente();

        return view('rentas.show', compact('renta', 'diasRestantes', 'diasRetraso', 'multaCalculada', 'motivoMulta', 'puedeEditarMulta'));
    }

    public function edit(Renta $renta)
    {
        if ($renta->estado !== 'activa') {
            return redirect()->route('rentas.index')->with('error', 'Solo se pueden editar rentas activas');
        }

        $clientes = Cliente::orderBy('nombre_completo')->get();
        $equipos = Equipo::where('activo', true)->get();

        return view('rentas.edit', compact('renta', 'clientes', 'equipos'));
    }

    public function update(Request $request, Renta $renta)
    {
        return back()->with('info', 'Función en desarrollo');
    }

    public function destroy(Renta $renta)
    {
        return back()->with('error', 'Por motivos de seguridad e historial contable, no está permitido eliminar registros de rentas.');
    }

    public function finalizar(Renta $renta)
    {
        $renta->load('detalles.equipo');

        if ($renta->estado !== 'activa') {
            return back()->with('error', 'La renta ya está ' . $renta->estado);
        }

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

        $renta->update([
            'estado' => 'finalizada',
            'fecha_devolucion' => now()
        ]);

        return redirect()->route('rentas.show', $renta)
            ->with('success', 'Renta finalizada correctamente. Los equipos han sido devueltos al inventario.');
    }

    public function cancelar(Renta $renta)
    {
        if ($renta->estado !== 'activa') {
            return back()->with('error', 'La renta ya está ' . $renta->estado);
        }

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

        $renta->update([
            'estado' => 'cancelada',
            'fecha_devolucion' => now()
        ]);

        return redirect()->route('rentas.show', $renta)
            ->with('success', 'Renta cancelada. Los equipos han sido devueltos al inventario.');
    }

    public function contrato(Renta $renta)
    {
        $renta->load('cliente', 'detalles.equipo', 'obra');
        $pdf = Pdf::loadView('rentas.pdf_contrato', compact('renta'));
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream('Contrato_' . $renta->folio . '.pdf');
    }

    public function pagare(Renta $renta)
    {
        $renta->load('cliente');
        $convertirNumeroALetras = function ($numero) {
            $f = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
            return ucfirst($f->format($numero));
        };
        $pdf = Pdf::loadView('rentas.pdf_pagare', compact('renta', 'convertirNumeroALetras'));
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream('Pagare_' . $renta->folio . '.pdf');
    }

    public function uploadContrato(Request $request, Renta $renta)
    {
        $request->validate([
            'contrato_firmado' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        if ($request->hasFile('contrato_firmado')) {
            if ($renta->contrato_firmado_path && Storage::disk('public')->exists($renta->contrato_firmado_path)) {
                Storage::disk('public')->delete($renta->contrato_firmado_path);
            }

            $path = $request->file('contrato_firmado')->store('rentas/contratos', 'public');
            $renta->contrato_firmado_path = $path;
            $renta->save();

            return back()->with('success', 'Contrato firmado subido correctamente');
        }

        return back()->with('error', 'Error al subir el contrato');
    }

    public function uploadPagare(Request $request, Renta $renta)
    {
        $request->validate([
            'pagare_firmado' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        if ($request->hasFile('pagare_firmado')) {
            if ($renta->pagare_firmado_path && Storage::disk('public')->exists($renta->pagare_firmado_path)) {
                Storage::disk('public')->delete($renta->pagare_firmado_path);
            }

            $path = $request->file('pagare_firmado')->store('rentas/pagares', 'public');
            $renta->pagare_firmado_path = $path;
            $renta->save();

            return back()->with('success', 'Pagaré firmado subido correctamente');
        }

        return back()->with('error', 'Error al subir el pagaré');
    }

    public function deleteDocumento(Renta $renta, $tipo)
    {
        if ($tipo === 'contrato' && $renta->contrato_firmado_path) {
            Storage::disk('public')->delete($renta->contrato_firmado_path);
            $renta->contrato_firmado_path = null;
            $renta->save();
            return back()->with('success', 'Contrato eliminado');
        }

        if ($tipo === 'pagare' && $renta->pagare_firmado_path) {
            Storage::disk('public')->delete($renta->pagare_firmado_path);
            $renta->pagare_firmado_path = null;
            $renta->save();
            return back()->with('success', 'Pagaré eliminado');
        }

        return back()->with('error', 'Documento no encontrado');
    }

    public function registrarPago(Request $request, Renta $renta)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,transferencia,tarjeta',
            'devolver_final' => 'nullable|array',
            'devolver_final.*' => 'numeric|min:0',
            'costo_faltante' => 'nullable|array',
            'costo_faltante.*' => 'numeric|min:0',
        ]);

        try {
            $nuevoPagoId = null;

            DB::transaction(function () use ($request, $renta, &$nuevoPagoId) {
                
                // 1. PROCESAR DEVOLUCIÓN DE EQUIPOS (SIEMPRE DISPONIBLE)
                $renta->load('detalles.equipo');
                $articulosDevueltos = [];

                if ($request->has('devolver_final') && is_array($request->devolver_final)) {
                    foreach ($renta->detalles as $detalle) {
                        $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta;
                        $devueltosHoy = isset($request->devolver_final[$detalle->id]) ? (int)$request->devolver_final[$detalle->id] : 0;

                        if ($devueltosHoy > $pendiente) {
                            throw new \Exception("No puedes devolver más de lo pendiente en {$detalle->equipo->nombre}");
                        }

                        if ($devueltosHoy > 0) {
                            $equipo = $detalle->equipo;
                            if ($renta->sucursal_id) {
                                $equipo->actualizarStockEnSucursal($renta->sucursal_id, $devueltosHoy, 'sumar');
                            } else {
                                $equipo->stock += $devueltosHoy;
                                $equipo->save();
                            }
                            $detalle->cantidad_devuelta += $devueltosHoy;
                            $detalle->save();

                            $articulosDevueltos[] = "{$devueltosHoy}x {$equipo->nombre}";
                        }
                    }
                }

                // 2. PROCESAR CARGOS FALTANTES (OPCIONAL EN CUALQUIER PAGO)
                $costoFaltantesTotal = 0;
                $motivoFaltantes = [];
                if ($request->has('costo_faltante') && is_array($request->costo_faltante)) {
                    foreach ($request->costo_faltante as $detalleId => $montoFaltante) {
                        $montoF = floatval($montoFaltante);
                        if ($montoF > 0) {
                            $costoFaltantesTotal += $montoF;
                            $det = $renta->detalles->firstWhere('id', $detalleId);
                            $nombreEq = ($det && $det->equipo) ? $det->equipo->nombre : 'Equipo';
                            $cantPend = $det ? ($det->cantidad - $det->cantidad_devuelta) : 0; // Lo pendiente DESPUÉS de devolver
                            $motivoFaltantes[] = "{$cantPend}x {$nombreEq} no devuelto ($" . number_format($montoF, 2) . ")";
                        }
                    }
                }

                if ($costoFaltantesTotal > 0) {
                    $renta->cargos_extra = ($renta->cargos_extra ?? 0) + $costoFaltantesTotal;
                    $motivoStr = "Faltantes: " . implode(', ', $motivoFaltantes);
                    $renta->motivo_cargos_extra = $renta->motivo_cargos_extra ? $renta->motivo_cargos_extra . ' | ' . $motivoStr : $motivoStr;
                    $renta->subtotal += $costoFaltantesTotal;
                    $renta->total += $costoFaltantesTotal;
                }

                // 3. SI LA RENTA FUE APROBADA PARA FINALIZACIÓN POR EL GERENTE
                if ($renta->autorizacion_aprobada) {
                    $articulosPerdidos = [];
                    foreach ($renta->detalles as $detalle) {
                        $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta;
                        if ($pendiente > 0) {
                            $articulosPerdidos[] = "{$pendiente}x {$detalle->equipo->nombre}";
                        }
                    }

                    if (!empty($articulosPerdidos)) {
                        $notaPerdidos = "\n[ALERTA] Artículos NO devueltos al liquidar: " . implode(', ', $articulosPerdidos);
                        $renta->observaciones = $renta->observaciones ? $renta->observaciones . $notaPerdidos : $notaPerdidos;
                    }

                    $renta->estado = 'finalizada';
                    $renta->fecha_devolucion = now();
                    $renta->autorizacion_aprobada = false;
                    $renta->autorizacion_solicitada = false;
                }

                // Escribir nota de retorno en el historial de la renta
                if (!empty($articulosDevueltos)) {
                    $notaDev = "\n[RETORNO DE EQUIPO - " . now()->format('d/m/Y') . "] Se devolvió: " . implode(', ', $articulosDevueltos);
                    $renta->observaciones = $renta->observaciones ? $renta->observaciones . $notaDev : $notaDev;
                }

                $renta->save();

                // 4. CREAR EL PAGO (Y guardar qué equipo se devolvió en este recibo)
                if ($request->monto > 0) {
                    $textoRetornoTicket = !empty($articulosDevueltos) ? implode(', ', $articulosDevueltos) : "Ninguno";
                    $obsPago = 'Registro de pago' . ($renta->estado === 'finalizada' ? ' / liquidación final' : '');
                    $obsPago .= " | Equipo devuelto: " . $textoRetornoTicket;

                    $pago = $renta->pagos()->create([
                        'monto' => $request->monto,
                        'metodo_pago' => $request->metodo_pago,
                        'tipo' => $renta->estado === 'finalizada' ? 'liquidacion' : 'abono',
                        'referencia' => $request->referencia,
                        'fecha_pago' => now(),
                        'observaciones' => $obsPago
                    ]);
                    $nuevoPagoId = $pago->id;
                }
            });

            if ($nuevoPagoId) {
                return back()->with('success', 'Operación y cobro procesados correctamente.')->with('imprimir_ticket_pago', $nuevoPagoId);
            }

            return back()->with('success', 'Operación y registro de productos procesados correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar la operación: ' . $e->getMessage());
        }
    }

    public function finalizarConPago(Request $request, Renta $renta)
    {
        if ($renta->estado !== 'activa') return back()->with('error', 'La renta ya está ' . $renta->estado);
        if (empty($renta->contrato_firmado_path) || empty($renta->pagare_firmado_path)) return back()->with('error', 'Primero debes subir el Contrato y el Pagaré firmados.');

        try {
            $nuevoPagoId = null;

            $resultado = DB::transaction(function () use ($request, $renta, &$nuevoPagoId) {
                $multa = floatval($request->input('multa_retraso', 0));
                $cargoManual = floatval($request->input('cargo_manual', 0));
                $totalExtra = $multa + $cargoManual;

                if ($totalExtra > 0) {
                    $renta->cargos_extra = ($renta->cargos_extra ?? 0) + $totalExtra;
                    $motivosArreglo = [];
                    if ($multa > 0) $motivosArreglo[] = $request->input('motivo_multa', '');
                    if ($cargoManual > 0) $motivosArreglo[] = "Daños/Otros: " . $request->input('motivo_cargo_manual', '');

                    $motivosFinal = implode(' | ', $motivosArreglo);
                    $renta->motivo_cargos_extra = $renta->motivo_cargos_extra ? $renta->motivo_cargos_extra . ' | ' . $motivosFinal : $motivosFinal;
                    $renta->subtotal += $totalExtra;
                    $renta->total += $totalExtra;
                    
                    $renta->observaciones = $renta->observaciones ? $renta->observaciones . "\n[FINALIZACIÓN] Cargos extra: $" . number_format($totalExtra, 2) . " - " . $motivosFinal : "[FINALIZACIÓN] Cargos extra: $" . number_format($totalExtra, 2) . " - " . $motivosFinal;
                    $renta->save();
                }

                $montoPago = floatval($request->input('monto_pago', 0));
                if ($montoPago > 0) {
                    $pago = $renta->pagos()->create([
                        'monto' => $montoPago,
                        'metodo_pago' => $request->metodo_pago_final ?? 'efectivo',
                        'tipo' => 'liquidacion',
                        'referencia' => $request->referencia_final,
                        'fecha_pago' => now(),
                        'observaciones' => 'Abono en intento de finalización'
                    ]);
                    $nuevoPagoId = $pago->id;
                }

                $saldoFinal = $renta->fresh()->saldo_pendiente;
                $esGerente = auth()->user()->isAdmin() || auth()->user()->isGerente();

                if ($saldoFinal > 0.01 && !$esGerente) {
                    $renta->update([
                        'autorizacion_solicitada' => true,
                        'autorizacion_aprobada' => false,
                        'motivo_autorizacion' => "Finalización con adeudo pendiente de $" . number_format($saldoFinal, 2),
                        'solicitado_por_id' => auth()->id()
                    ]);
                    return 'autorizacion';
                }

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

                $renta->estado = 'finalizada';
                $renta->fecha_devolucion = now();
                $renta->autorizacion_aprobada = false;
                $renta->autorizacion_solicitada = false;
                $renta->save();
                
                return 'finalizado';
            });

            if ($resultado === 'autorizacion') {
                return redirect()->route('rentas.show', $renta)->with('warning', 'Pago registrado. Se envió la solicitud al gerente para cerrar con adeudo.');
            }

            if ($nuevoPagoId) {
                return redirect()->route('rentas.show', $renta)->with('success', 'Renta finalizada correctamente.')->with('imprimir_ticket_pago', $nuevoPagoId);
            }

            return redirect()->route('rentas.show', $renta)->with('success', 'Renta finalizada correctamente.');
            
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function ampliarDias(Request $request, Renta $renta)
    {
        $request->validate([
            'dias_extra' => 'required|integer|min:1',
            'abono' => 'nullable|numeric|min:0',
            'metodo_pago' => 'nullable|in:efectivo,transferencia,tarjeta',
            'devolver_final' => 'nullable|array',
            'devolver_final.*' => 'numeric|min:0'
        ]);

        try {
            $nuevoPagoId = null;

            DB::transaction(function () use ($request, $renta, &$nuevoPagoId) {
                $diasExtra = (int)$request->dias_extra;
                $aplicaIvaAmpliacion = (bool)$renta->facturar;

                // 1. PROCESAR DEVOLUCIÓN DE EQUIPOS PRIMERO
                $articulosDevueltos = [];
                $renta->load('detalles.equipo');

                if ($request->has('devolver_final') && is_array($request->devolver_final)) {
                    foreach ($renta->detalles as $detalle) {
                        $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta;
                        $devueltosHoy = isset($request->devolver_final[$detalle->id]) ? (int)$request->devolver_final[$detalle->id] : 0;

                        if ($devueltosHoy > $pendiente) {
                            throw new \Exception("No puedes devolver más de lo pendiente en {$detalle->equipo->nombre}");
                        }

                        if ($devueltosHoy > 0) {
                            $equipo = $detalle->equipo;
                            if ($renta->sucursal_id) {
                                $equipo->actualizarStockEnSucursal($renta->sucursal_id, $devueltosHoy, 'sumar');
                            } else {
                                $equipo->stock += $devueltosHoy;
                                $equipo->save();
                            }
                            $detalle->cantidad_devuelta += $devueltosHoy;
                            $detalle->save();

                            $articulosDevueltos[] = "{$devueltosHoy}x {$equipo->nombre}";
                        }
                    }
                }

                // 2. CALCULAR LOS DÍAS EXTRA BASÁNDOSE SOLO EN EL EQUIPO QUE AÚN QUEDA PENDIENTE
                $subtotalAmpliacion = 0;
                foreach ($renta->detalles as $detalle) {
                    $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta;
                    
                    if ($pendiente > 0) {
                        $costoDetalleExtra = ($detalle->precio_dia * $pendiente * $diasExtra);
                        $subtotalAmpliacion += $costoDetalleExtra;

                        $detalle->subtotal += $costoDetalleExtra;
                        $detalle->dias += $diasExtra; 
                        $detalle->save();
                    }
                }

                if ($subtotalAmpliacion <= 0 && empty($articulosDevueltos)) {
                    throw new \Exception("No hay equipos pendientes para ampliar, ni se devolvió equipo.");
                }

                $ivaAmpliacion = $aplicaIvaAmpliacion ? ($subtotalAmpliacion * 0.16) : 0;
                $totalAmpliacion = $subtotalAmpliacion + $ivaAmpliacion;

                $renta->fecha_fin = $renta->fecha_fin->addDays($diasExtra);
                $renta->dias_totales += $diasExtra;
                $renta->dias_ampliados += $diasExtra;
                $renta->fecha_ampliacion = now();

                $renta->subtotal += $subtotalAmpliacion;
                $renta->iva += $ivaAmpliacion;
                $renta->total += $totalAmpliacion;

                $notaAmpliacion = "\n[AMPLIACIÓN - " . now()->format('d/m/Y') . "] +" . $diasExtra . " días extra.";
                if (!empty($articulosDevueltos)) {
                    $notaAmpliacion .= " | Se devolvió equipo parcial: " . implode(', ', $articulosDevueltos);
                }
                if ($request->filled('motivo')) {
                    $notaAmpliacion .= " | Motivo: " . $request->motivo;
                }
                $renta->observaciones = $renta->observaciones ? $renta->observaciones . $notaAmpliacion : $notaAmpliacion;

                $renta->save();

                // 3. CREAR PAGO SI HUBIERA
                if ($request->filled('abono') && $request->abono > 0) {
                    $textoRetornoTicket = !empty($articulosDevueltos) ? implode(', ', $articulosDevueltos) : "Ninguno";
                    $obsPago = ($request->motivo ?? 'Abono en ampliación de días') . " | Equipo devuelto: " . $textoRetornoTicket;

                    $pago = $renta->pagos()->create([
                        'monto' => $request->abono,
                        'metodo_pago' => $request->metodo_pago ?? 'efectivo',
                        'tipo' => 'ampliacion',
                        'fecha_pago' => now(),
                        'observaciones' => $obsPago
                    ]);
                    $nuevoPagoId = $pago->id;
                }
            });

            if ($nuevoPagoId) {
                return back()->with('success', 'Ampliación registrada correctamente.')->with('imprimir_ticket_pago', $nuevoPagoId);
            }

            return back()->with('success', 'Ampliación y montos actualizados correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function devolucionParcial(Request $request, Renta $renta)
    {
        if ($renta->estado !== 'activa') {
            return back()->with('error', 'La renta no está activa.');
        }

        $request->validate([
            'devolver' => 'required|array',
            'devolver.*' => 'numeric|min:0'
        ]);

        try {
            DB::transaction(function () use ($request, $renta) {
                $renta->load('detalles.equipo');
                $huboDevolucion = false;
                $detallesDevolucion = [];

                foreach ($request->devolver as $detalleId => $cantidadADevolver) {
                    if ($cantidadADevolver > 0) {
                        $detalle = $renta->detalles->where('id', $detalleId)->first();
                        $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta;

                        if ($cantidadADevolver > $pendiente) {
                            throw new \Exception("No puedes devolver más de lo pendiente en {$detalle->equipo->nombre}");
                        }

                        $detalle->cantidad_devuelta += $cantidadADevolver;
                        $detalle->save();

                        $equipo = $detalle->equipo;
                        if ($renta->sucursal_id) {
                            $equipo->actualizarStockEnSucursal($renta->sucursal_id, $cantidadADevolver, 'sumar');
                        } else {
                            $equipo->stock += $cantidadADevolver;
                            $equipo->save();
                        }

                        $huboDevolucion = true;
                        $detallesDevolucion[] = "{$cantidadADevolver}x {$equipo->nombre}";
                    }
                }

                if ($huboDevolucion) {
                    $nota = "\n[DEV. PARCIAL - " . now()->format('d/m/Y') . "] Se devolvió al inventario: " . implode(', ', $detallesDevolucion);
                    $renta->observaciones = $renta->observaciones ? $renta->observaciones . $nota : $nota;
                    $renta->save();
                }
            });

            return back()->with('success', 'Devolución parcial registrada. El stock ha sido actualizado.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function ticketPago(\App\Models\Pago $pago)
    {
        $pago->load('renta.cliente');
        return view('rentas.ticket_pago', compact('pago'));
    }
}
