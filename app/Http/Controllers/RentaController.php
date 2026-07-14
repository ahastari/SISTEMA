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

        $query = Renta::with('cliente')->latest();

        if (!$isGlobalAdmin) {
            $query->where('sucursal_id', $sucursalId);
        }

        $rentas = $query->paginate(10);
        
        foreach ($rentas as $renta) {
            if ($renta->estado == 'activa') {
                $hoy = now()->startOfDay();
                $fechaFin = $renta->fecha_fin->startOfDay();
                
                if ($hoy <= $fechaFin) {
                    $renta->dias_restantes = $hoy->diffInDays($fechaFin) + 1;
                } else {
                    $renta->dias_restantes = 0;
                }
            } else {
                $renta->dias_restantes = null;
            }
        }
        
        // 🔒 Obtener la tarifa de multa de la sucursal actual
        $tarifaMulta = 0;
        if ($sucursalId && $sucursalId !== 'global') {
            $sucursal = \App\Models\Sucursal::find($sucursalId);
            $tarifaMulta = $sucursal ? $sucursal->penalizacion_diaria : 0;
        }
        
        return view('rentas.index', compact('rentas', 'tarifaMulta', 'isGlobalAdmin'));
    }

    public function create()
    {
        $sucursalId = session('activo_sucursal_id');
        $isGlobalAdmin = auth()->user()->isAdmin() && $sucursalId === 'global';

        $clientes = Cliente::orderBy('nombre_completo')->get();
        
        // 🔒 Obtener solo equipos activos, para RENTA y con stock en la sucursal actual
        $equiposQuery = Equipo::where('activo', true)
                              ->whereIn('tipo_operacion', ['renta', 'ambas']); // 🔥 Filtro agregado aquí

        if (!$isGlobalAdmin) {
            $equiposQuery->whereHas('sucursales', function($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)->where('stock', '>', 0);
            });
        } else {
            $equiposQuery->where('stock', '>', 0);
        }

        $equipos = $equiposQuery->get();

        // 🔒 Sobrescribir el stock principal para que en la vista solo aparezca el de la sucursal
        if (!$isGlobalAdmin) {
            foreach ($equipos as $equipo) {
                $equipo->stock = $equipo->getStockEnSucursal($sucursalId);
            }
        }

        $folio = Renta::generarFolio();
        
        return view('rentas.create', compact('clientes', 'equipos', 'folio'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'deposito' => 'nullable|numeric|min:0',
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
            $subtotal = 0;
            $detalles = [];
            
            foreach ($request->equipos as $item) {
                $equipo = Equipo::find($item['id']);
                
                // 🔒 Validar y descontar stock independiente por sucursal
                if (!$isGlobalAdmin) {
                    $stockDisponible = $equipo->getStockEnSucursal($sucursalIdGuardar);
                    if ($stockDisponible < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente en esta sucursal para {$equipo->nombre}. Disponible: {$stockDisponible}");
                    }
                    // Descontar el stock local
                    $equipo->actualizarStockEnSucursal($sucursalIdGuardar, $item['cantidad'], 'restar');
                } else {
                    if ($equipo->stock < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente para {$equipo->nombre}. Disponible: {$equipo->stock}");
                    }
                    $equipo->stock -= $item['cantidad'];
                    $equipo->save();
                }
                
                $subtotalEquipo = $equipo->precio_dia * $item['cantidad'] * $diasTotales;
                $subtotal += $subtotalEquipo;
                
                $detalles[] = [
                    'equipo_id' => $equipo->id,
                    'cantidad' => $item['cantidad'],
                    'precio_dia' => $equipo->precio_dia,
                    'dias' => $diasTotales,
                    'subtotal' => $subtotalEquipo
                ];
            }
            
            $requiereFactura = $request->requiere_factura == '1';
            $iva = $requiereFactura ? ($subtotal * 0.16) : 0;
            $total = $subtotal + $iva;
            $deposito = $request->deposito ?? 0;
            
            $renta = Renta::create([
                'folio' => Renta::generarFolio(),
                'cliente_id' => $request->cliente_id,
                'sucursal_id' => $sucursalIdGuardar,
                'obra_id' => $request->obra_id,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'dias_totales' => $diasTotales,
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
                'deposito' => $deposito,
                'requiere_factura' => $requiereFactura,
                'observaciones' => $request->observaciones,
                'estado' => 'activa'
            ]);
            
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
                // 🔒 Calcular retraso y multa
                $diasRetraso = $fechaFin->diffInDays($hoy);
                
                // Obtener la penalización definida por el gerente (si no hay sucursal, es 0)
                $tarifaDiaria = $renta->sucursal ? $renta->sucursal->penalizacion_diaria : 0;
                
                if ($tarifaDiaria > 0) {
                    $multaCalculada = $diasRetraso * $tarifaDiaria;
                    $motivoMulta = "Penalización por retraso de {$diasRetraso} día(s) (Tarifa: $" . number_format($tarifaDiaria, 2) . "/día)";
                }
            }
        }
        
        // Utilizando los helpers profesionales que ya tienes en tu modelo User:
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
        if ($renta->estado === 'activa') {
            foreach ($renta->detalles as $detalle) {
                $equipo = $detalle->equipo;
                // 🔒 Regresar el stock a su sucursal de origen
                if ($renta->sucursal_id) {
                    $equipo->actualizarStockEnSucursal($renta->sucursal_id, $detalle->cantidad, 'sumar');
                } else {
                    $equipo->stock += $detalle->cantidad;
                    $equipo->save();
                }
            }
        }
        
        $renta->delete();
        
        return redirect()->route('rentas.index')->with('success', 'Renta eliminada correctamente');
    }
    
    public function finalizar(Renta $renta)
    {
        $renta->load('detalles.equipo');
         
        if ($renta->estado !== 'activa') {
            return back()->with('error', 'La renta ya está ' . $renta->estado);
        }
        
        foreach ($renta->detalles as $detalle) {
            $equipo = $detalle->equipo;
            // 🔒 Regresar el stock a su sucursal de origen
            if ($renta->sucursal_id) {
                $equipo->actualizarStockEnSucursal($renta->sucursal_id, $detalle->cantidad, 'sumar');
            } else {
                $equipo->stock += $detalle->cantidad;
                $equipo->save();
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
            $equipo = $detalle->equipo;
            // 🔒 Regresar el stock a su sucursal de origen
            if ($renta->sucursal_id) {
                $equipo->actualizarStockEnSucursal($renta->sucursal_id, $detalle->cantidad, 'sumar');
            } else {
                $equipo->stock += $detalle->cantidad;
                $equipo->save();
            }
        }
        
        $renta->update([
            'estado' => 'cancelada',
            'fecha_devolucion' => now()
        ]);
        
        return redirect()->route('rentas.show', $renta)
            ->with('success', 'Renta cancelada. Los equipos han sido devueltos al inventario.');
    }

    // Funciones PDF omitidas por brevedad (mantienen tu mismo código)
    public function contrato(Renta $renta) {
        $renta->load('cliente', 'detalles.equipo', 'obra');
        $pdf = Pdf::loadView('rentas.pdf_contrato', compact('renta'));
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream('Contrato_' . $renta->folio . '.pdf');
    }

    public function pagare(Renta $renta) {
        $renta->load('cliente');
        $convertirNumeroALetras = function($numero) {
            $f = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
            return ucfirst($f->format($numero));
        };
        $pdf = Pdf::loadView('rentas.pdf_pagare', compact('renta', 'convertirNumeroALetras'));
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream('Pagare_' . $renta->folio . '.pdf');
    }

    public function uploadContrato(Request $request, Renta $renta) { /* ... tu mismo código ... */ }
    public function uploadPagare(Request $request, Renta $renta) { /* ... tu mismo código ... */ }
    public function deleteDocumento(Renta $renta, $tipo) { /* ... tu mismo código ... */ }

    public function registrarPago(Request $request, Renta $renta)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:efectivo,transferencia,tarjeta'
        ]);

        if ($request->monto > $renta->saldo_pendiente) {
            return back()->with('error', 'El monto excede el saldo pendiente.');
        }

        $renta->pagos()->create([
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'tipo' => 'abono',
            'referencia' => $request->referencia,
            'fecha_pago' => now(),
        ]);

        return back()->with('success', 'Pago registrado correctamente.');
    }

    public function ampliarDias(Request $request, Renta $renta)
    {
        $request->validate([
            'dias_extra' => 'required|integer|min:1',
            'abono' => 'nullable|numeric|min:0'
        ]);

        DB::transaction(function () use ($request, $renta) {
            $diasExtra = (int)$request->dias_extra;
            $facturar = $request->has('facturar');

            $costoExtra = 0;
            foreach ($renta->detalles as $detalle) {
                $costoExtra += ($detalle->equipo->precio_dia * $detalle->cantidad * $diasExtra);
            }

            $renta->fecha_fin = $renta->fecha_fin->addDays($diasExtra);
            $renta->dias_totales += $diasExtra;
            $renta->subtotal += $costoExtra;
            $renta->facturar = $facturar;
            
            $renta->iva = $renta->facturar ? ($renta->subtotal * 0.16) : 0;
            $renta->total = $renta->subtotal + $renta->iva;
            $renta->save();

            if ($request->filled('abono') && $request->abono > 0) {
                $renta->pagos()->create([
                    'monto' => $request->abono,
                    'metodo_pago' => $request->metodo_pago ?? 'efectivo',
                    'tipo' => 'ampliacion',
                    'fecha_pago' => now(),
                    'observaciones' => $request->motivo ?? 'Abono en ampliación de días'
                ]);
            }
        });

        return back()->with('success', 'Ampliación y pago registrados.');
    }

    public function finalizarConPago(Request $request, Renta $renta)
    {
        if ($renta->estado !== 'activa') {
            return back()->with('error', 'La renta ya está ' . $renta->estado);
        }

        try {
            DB::transaction(function () use ($request, $renta) {
                
                // 1. Procesar cargos (Multa automática + Cargos por daños)
                $multa = floatval($request->input('multa_retraso', 0));
                $motivoMulta = $request->input('motivo_multa', '');
                
                $cargoManual = floatval($request->input('cargo_manual', 0));
                $motivoManual = $request->input('motivo_cargo_manual', '');
                
                $totalExtra = $multa + $cargoManual;
                
                if ($totalExtra > 0) {
                    $renta->cargos_extra = $totalExtra;
                    
                    $motivosArreglo = [];
                    if ($multa > 0) $motivosArreglo[] = $motivoMulta;
                    if ($cargoManual > 0) $motivosArreglo[] = "Daños/Pérdida: " . $motivoManual;
                    
                    $motivosFinal = implode(' | ', $motivosArreglo);
                    $renta->motivo_cargos_extra = $motivosFinal;
                    $renta->total += $totalExtra;
                    
                    // Dejar constancia en el historial de observaciones
                    $nota = "\n\n[FINALIZACIÓN] Cargos extra totales sumados a la deuda: $" . number_format($totalExtra, 2) . " - Detalle: " . $motivosFinal;
                    $renta->observaciones = $renta->observaciones ? $renta->observaciones . $nota : $nota;
                    $renta->save();
                }

                // 2. Registrar el pago final
                $montoPago = floatval($request->input('monto_pago', 0));
                
                if ($montoPago > 0) {
                    $renta->pagos()->create([
                        'monto' => $montoPago,
                        'metodo_pago' => $request->metodo_pago_final ?? 'efectivo',
                        'tipo' => 'liquidacion',
                        'referencia' => $request->referencia_final,
                        'fecha_pago' => now(),
                        'observaciones' => 'Pago de liquidación y/o cargos extra'
                    ]);
                }

                // 3. Verificar que el saldo haya quedado en 0
                $saldoFinal = $renta->fresh()->saldo_pendiente;

                if ($saldoFinal > 0.01) {
                    throw new \Exception('El pago ingresado no cubre el saldo total. Queda un adeudo de $' . number_format($saldoFinal, 2));
                }
                
                // 4. Regresar el stock a la sucursal de origen
                $renta->load('detalles.equipo');
                foreach ($renta->detalles as $detalle) {
                    $equipo = $detalle->equipo;
                    if ($renta->sucursal_id) {
                        $equipo->actualizarStockEnSucursal($renta->sucursal_id, $detalle->cantidad, 'sumar');
                    } else {
                        $equipo->stock += $detalle->cantidad;
                        $equipo->save();
                    }
                }
                
                // 5. Finalizar Renta
                $renta->estado = 'finalizada';
                $renta->fecha_devolucion = now();
                $renta->save();
            });

            return redirect()->route('rentas.show', $renta)->with('success', 'Renta finalizada correctamente.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function actualizarMulta(Request $request)
    {
        $request->validate([
            'penalizacion_diaria' => 'required|numeric|min:0'
        ]);

        // Verificar permisos de seguridad
        if (!auth()->user()->isAdmin() && !auth()->user()->isGerente()) {
            return back()->with('error', 'No tienes permisos para fijar la tarifa de multas.');
        }

        $sucursalId = session('activo_sucursal_id');
        
        if (!$sucursalId || $sucursalId === 'global') {
            return back()->with('error', 'Debes seleccionar una sucursal en específico para poder fijar su tarifa.');
        }

        $sucursal = \App\Models\Sucursal::find($sucursalId);
        if ($sucursal) {
            $sucursal->penalizacion_diaria = $request->penalizacion_diaria;
            $sucursal->save();
            return back()->with('success', 'Tarifa de penalización diaria actualizada correctamente.');
        }

        return back()->with('error', 'Error al localizar la sucursal.');
    }
}