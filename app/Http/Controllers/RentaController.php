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
        $renta->load('cliente', 'detalles.equipo');
        return view('rentas.show', compact('renta'));
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
        if ($renta->estado === 'activa') {
            foreach ($renta->detalles as $detalle) {
                $equipo = $detalle->equipo;
                $equipo->stock += $detalle->cantidad;
                $equipo->save();
            }
        }
        
        $renta->delete();
        
        return redirect()->route('rentas.index')
            ->with('success', 'Renta eliminada');
    }
    
    public function finalizar(Renta $renta)
    {
        if ($renta->estado !== 'activa') {
            return back()->with('error', 'La renta ya está ' . $renta->estado);
        }
        
        $renta->update([
            'estado' => 'finalizada',
            'fecha_devolucion' => now()
        ]);
        
        return redirect()->route('rentas.show', $renta)
            ->with('success', 'Renta finalizada correctamente');
    }
    

    // Generar contrato PDF
    public function contrato(Renta $renta)
    {
        $renta->load('cliente', 'detalles.equipo');
        
        $pdf = Pdf::loadView('rentas.pdf_contrato', compact('renta'));
        $pdf->setPaper('letter', 'portrait');
        
        return $pdf->download('Contrato_' . $renta->folio . '.pdf');
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
        
        return $pdf->download('Pagare_' . $renta->folio . '.pdf');
    }

    private function convertirNumeroALetras($numero)
    {
        $f = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
        return ucfirst($f->format($numero));
    }

}