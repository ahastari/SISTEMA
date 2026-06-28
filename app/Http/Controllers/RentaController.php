<?php

namespace App\Http\Controllers;

use App\Models\Renta;
use App\Models\DetalleRenta;
use App\Models\Cliente;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; 

class RentaController extends Controller
{
    public function index()
    {
        $rentas = Renta::with('cliente')->latest()->paginate(10);
        
        // Calcular días restantes para cada renta activa
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
        
        return view('rentas.index', compact('rentas'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nombre_completo')->get();
        $equipos = Equipo::where('activo', true)->where('stock', '>', 0)->get();
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
            'equipos' => 'required|array|min:1',
            'equipos.*.id' => 'required|exists:equipos,id',
            'equipos.*.cantidad' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $diasTotales = Renta::calcularDias($request->fecha_inicio, $request->fecha_fin);
            
            $subtotal = 0;
            $detalles = [];
            
            foreach ($request->equipos as $item) {
                $equipo = Equipo::find($item['id']);
                
                if ($equipo->stock < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para {$equipo->nombre}. Disponible: {$equipo->stock}");
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
                
                $equipo->stock -= $item['cantidad'];
                $equipo->save();
            }
            
            $iva = $subtotal * 0.16;
            $total = $subtotal + $iva;
            $deposito = $request->deposito ?? 0;
            
            $renta = Renta::create([
                'folio' => Renta::generarFolio(),
                'cliente_id' => $request->cliente_id,
                'obra_id' => $request->obra_id,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'dias_totales' => $diasTotales,
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
                'deposito' => $deposito,
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
        $renta->load('cliente', 'detalles.equipo', 'obra');
        
        // Calcular días restantes
        $diasRestantes = 0;
        if ($renta->estado == 'activa') {
            $hoy = now()->startOfDay();
            $fechaFin = $renta->fecha_fin->startOfDay();
            
            if ($hoy <= $fechaFin) {
                $diasRestantes = $hoy->diffInDays($fechaFin) + 1; // +1 para incluir el día actual
            } else {
                $diasRestantes = 0; // Ya pasó la fecha de fin
            }
        }
        
        return view('rentas.show', compact('renta', 'diasRestantes'));
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
        if ($renta->estado !== 'activa') {
            return back()->with('error', 'No se puede editar una renta finalizada o cancelada');
        }
        
        return back()->with('info', 'Función en desarrollo');
    }

    public function destroy(Renta $renta)
    {
        // Si la renta está activa, devolver el stock antes de eliminar
        if ($renta->estado === 'activa') {
            foreach ($renta->detalles as $detalle) {
                $equipo = $detalle->equipo;
                $equipo->stock += $detalle->cantidad;
                $equipo->save();
            }
        }
        
        $renta->delete();
        
        return redirect()->route('rentas.index')
            ->with('success', 'Renta eliminada correctamente');
    }
    
    public function finalizar(Renta $renta)
    {
        $renta->load('detalles.equipo');
         
        if ($renta->estado !== 'activa') {
            return back()->with('error', 'La renta ya está ' . $renta->estado);
        }
        
        // Devolver equipos al inventario
        foreach ($renta->detalles as $detalle) {
            $equipo = $detalle->equipo;
            $equipo->stock += $detalle->cantidad;
            $equipo->save();
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
        
        // Devolver equipos al inventario
        $renta->load('detalles.equipo');
        foreach ($renta->detalles as $detalle) {
            $equipo = $detalle->equipo;
            $equipo->stock += $detalle->cantidad;
            $equipo->save();
        }
        
        $renta->update([
            'estado' => 'cancelada',
            'fecha_devolucion' => now()
        ]);
        
        return redirect()->route('rentas.show', $renta)
            ->with('success', 'Renta cancelada. Los equipos han sido devueltos al inventario.');
    }
    

    // Generar contrato PDF
    public function contrato(Renta $renta)
    {
        $renta->load('cliente', 'detalles.equipo', 'obra');  // ← Agrega 'obra'
        
        $pdf = Pdf::loadView('rentas.pdf_contrato', compact('renta'));
        $pdf->setPaper('letter', 'portrait');
        
        return $pdf->stream('Contrato_' . $renta->folio . '.pdf');
    }

    // Generar pagaré PDF
    public function pagare(Renta $renta)
    {
        $renta->load('cliente');
        
        // Pasar el helper a la vista
        $convertirNumeroALetras = function($numero) {
            $f = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
            return ucfirst($f->format($numero));
        };
        
        $pdf = Pdf::loadView('rentas.pdf_pagare', compact('renta', 'convertirNumeroALetras'));
        $pdf->setPaper('letter', 'portrait');
        
        return $pdf->stream('Pagare_' . $renta->folio . '.pdf');
    }

    private function convertirNumeroALetras($numero)
    {
        $f = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
        return ucfirst($f->format($numero));
    }

    public function uploadContrato(Request $request, Renta $renta)
    {
        $request->validate([
            'contrato_firmado' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        if ($request->hasFile('contrato_firmado')) {
            // Eliminar archivo anterior si existe
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

    // Ampliar días de renta
    public function ampliarDias(Request $request, Renta $renta)
    {
        $request->validate([
            'dias_extra' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255'
        ]);
        
        if ($renta->estado !== 'activa') {
            return back()->with('error', 'Solo se pueden ampliar rentas activas');
        }
        
        // Cargar detalles con equipos
        $renta->load('detalles.equipo');
        
        // Calcular nuevo subtotal por los días extra
        $diasExtra = $request->dias_extra;
        $nuevoSubtotal = 0;
        
        foreach ($renta->detalles as $detalle) {
            $subtotalExtra = $detalle->equipo->precio_dia * $detalle->cantidad * $diasExtra;
            $nuevoSubtotal += $subtotalExtra;
        }
        
        // Actualizar fechas y días
        $nuevaFechaFin = $renta->fecha_fin->copy()->addDays($diasExtra);
        $renta->fecha_fin = $nuevaFechaFin;
        $renta->dias_totales += $diasExtra;
        $renta->dias_ampliados += $diasExtra;
        $renta->fecha_ampliacion = now();
        
        // Actualizar totales
        $renta->subtotal += $nuevoSubtotal;
        $renta->iva = $renta->subtotal * 0.16;
        $renta->total = $renta->subtotal + $renta->iva;
        
        // Registrar en observaciones
        $renta->observaciones = ($renta->observaciones ? $renta->observaciones . "\n" : '') . 
            "Ampliación de {$diasExtra} días. Motivo: {$request->motivo}";
        
        $renta->save();
        
        // Recalcular saldo pendiente
        $renta->calcularSaldoPendiente();
        
        return redirect()->route('rentas.show', $renta)
            ->with('success', "Renta ampliada en {$diasExtra} días. Nuevo total: $" . number_format($renta->total, 2));
    }

    // Finalizar renta con pago
    public function finalizarConPago(Request $request, Renta $renta)
    {
        if ($renta->estado !== 'activa') {
            return back()->with('error', 'La renta ya está ' . $renta->estado);
        }
        
        // Cargar detalles con equipos
        $renta->load('detalles.equipo');
        
        // Calcular saldo pendiente real (total - deposito - pagos)
        $saldoPendiente = $renta->total - ($renta->deposito ?? 0) - $renta->total_pagado;
        
        // Registrar pago final
        if ($request->has('monto_pago') && $request->monto_pago > 0) {
            Pago::create([
                'renta_id' => $renta->id,
                'monto' => $request->monto_pago,
                'metodo_pago' => $request->metodo_pago_final,
                'referencia' => $request->referencia_final,
                'fecha_pago' => now(),
                'tipo' => 'finalizacion',
                'observaciones' => 'Pago de finalización de renta'
            ]);
            
            // Actualizar total pagado
            $renta->total_pagado += $request->monto_pago;
        }
        
        // Devolver equipos al inventario
        foreach ($renta->detalles as $detalle) {
            $equipo = $detalle->equipo;
            $equipo->stock += $detalle->cantidad;
            $equipo->save();
        }
        
        // Actualizar estado
        $renta->estado = 'finalizada';
        $renta->fecha_devolucion = now();
        $renta->saldo_pendiente = $renta->total - ($renta->deposito ?? 0) - $renta->total_pagado;
        $renta->save();
        
        return redirect()->route('rentas.show', $renta)
            ->with('success', 'Renta finalizada correctamente. Saldo pendiente: $' . number_format($renta->saldo_pendiente, 2));
    }

    // Método para calcular saldo pendiente (corregido)
    public function calcularSaldoPendiente($rentaId)
    {
        $renta = Renta::find($rentaId);
        if (!$renta) return 0;
        
        $totalPagado = $renta->pagos()->sum('monto');
        $renta->total_pagado = $totalPagado;
        
        // El saldo pendiente es: total - depósito - total_pagado
        $renta->saldo_pendiente = $renta->total - ($renta->deposito ?? 0) - $totalPagado;
        
        // Si el saldo es negativo, ponerlo en 0 (sobrepago)
        if ($renta->saldo_pendiente < 0) {
            $renta->saldo_pendiente = 0;
        }
        
        $renta->save();
        return $renta->saldo_pendiente;
    }

}