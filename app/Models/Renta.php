<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Renta extends Model
{
    protected $fillable = [
        'folio', 'cliente_id', 'obra_id', 'fecha_inicio', 'fecha_fin', 'dias_totales',
        'subtotal', 'iva', 'total', 'deposito', 'estado', 'observaciones', 
        'fecha_devolucion', 'contrato_firmado_path', 'pagare_firmado_path'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_devolucion' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function obra()  // ← Verifica que esta relación exista
    {
        return $this->belongsTo(Obra::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleRenta::class);
    }

    public static function generarFolio()
    {
        $year = date('Y');
        $ultimaRenta = self::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        
        if ($ultimaRenta) {
            $ultimoNumero = intval(substr($ultimaRenta->folio, -4));
            $nuevoNumero = str_pad($ultimoNumero + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nuevoNumero = '0001';
        }
        
        return 'R-' . $year . '-' . $nuevoNumero;
    }

    public static function calcularDias($fechaInicio, $fechaFin)
    {
        $inicio = new \DateTime($fechaInicio);
        $fin = new \DateTime($fechaFin);
        $diferencia = $inicio->diff($fin);
        return $diferencia->days + 1;
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function abonos()
    {
        return $this->hasMany(Abono::class);
    }

    // Método para calcular saldo pendiente
    public function calcularSaldoPendiente()
    {
        $totalAbonado = $this->pagos()->sum('monto');
        $this->total_pagado = $totalAbonado;
        $this->saldo_pendiente = $this->total - $totalAbonado - ($this->deposito ?? 0);
        $this->save();
        
        return $this->saldo_pendiente;
    }

    // Método para ampliar días
    public function ampliarDias($diasExtra)
    {
        $nuevaFechaFin = $this->fecha_fin->addDays($diasExtra);
        $this->fecha_fin = $nuevaFechaFin;
        $this->dias_totales += $diasExtra;
        $this->dias_ampliados += $diasExtra;
        $this->fecha_ampliacion = now();
        
        // Recalcular total
        $nuevoSubtotal = 0;
        foreach ($this->detalles as $detalle) {
            $nuevoSubtotal += $detalle->equipo->precio_dia * $detalle->cantidad * $diasExtra;
        }
        $this->subtotal += $nuevoSubtotal;
        $this->iva = $this->subtotal * 0.16;
        $this->total = $this->subtotal + $this->iva;
        
        $this->save();
        $this->calcularSaldoPendiente();
        
        return $this;
    }
}