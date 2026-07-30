<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Renta extends Model
{
    protected $fillable = [
        'folio',
        'cliente_id',
        'sucursal_id',
        'obra_id',
        'fecha_inicio',
        'fecha_fin',
        'dias_totales',
        'dias_ampliados',
        'subtotal',
        'flete',
        'mano_obra',
        'iva',
        'total',
        'cargos_extra',
        'motivo_cargos_extra',
        'total_pagado',
        'saldo_pendiente',
        'deposito',
        'estado',
        'observaciones',
        'contrato_firmado_path',
        'pagare_firmado_path',
        'fecha_devolucion',
        'fecha_ampliacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_devolucion' => 'date',
        'fecha_ampliacion' => 'date',
    ];

    /**
     * Relación con Cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relación con Obra
     */
    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    /**
     * 🔒 NUEVA: Relación con Sucursal
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Relación con Detalles de Renta
     */
    public function detalles()
    {
        return $this->hasMany(DetalleRenta::class);
    }

    /**
     * Relación con Pagos
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    /**
     * Relación con Abonos
     */
    public function abonos()
    {
        return $this->hasMany(Abono::class);
    }

    /**
     * Generar folio único para la renta
     */
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

    /**
     * Calcular días entre dos fechas
     */
    public static function calcularDias($fechaInicio, $fechaFin)
    {
        $inicio = new \DateTime($fechaInicio);
        $fin = new \DateTime($fechaFin);
        $diferencia = $inicio->diff($fin);
        return $diferencia->days + 1; // +1 para incluir el día de inicio
    }

    /**
     * Método para calcular saldo pendiente
     */
    public function getSaldoPendienteAttribute()
    {
        // Si la renta está cancelada, la deuda se anula automáticamente
        if ($this->estado === 'cancelada') {
            return 0; 
        }

        // Sumamos todos los pagos realizados a esta renta
        $totalPagado = $this->pagos()->sum('monto');
        
        // El saldo es: Total de la renta - Depósito - Total Pagado
        $saldo = $this->total - ($this->deposito ?? 0) - $totalPagado;
        
        return max(0, $saldo); // Nunca devolver un saldo negativo
    }

    /**
     * Método para ampliar días de renta
     */
    public function ampliarDias($diasExtra, $motivo = null)
    {
        $nuevaFechaFin = $this->fecha_fin->copy()->addDays($diasExtra);
        $this->fecha_fin = $nuevaFechaFin;
        $this->dias_totales += $diasExtra;
        $this->dias_ampliados += $diasExtra;
        $this->fecha_ampliacion = now();
        
        // Recalcular total basado en los días extra
        $nuevoSubtotal = 0;
        foreach ($this->detalles as $detalle) {
            $nuevoSubtotal += $detalle->equipo->precio_dia * $detalle->cantidad * $diasExtra;
        }
        
        $this->subtotal += $nuevoSubtotal;
        $this->iva = $this->subtotal * 0.16;
        $this->total = $this->subtotal + $this->iva;
        
        // Registrar motivo en observaciones
        if ($motivo) {
            $this->observaciones = ($this->observaciones ? $this->observaciones . "\n" : '') 
                . "Ampliación de {$diasExtra} días. Motivo: {$motivo}";
        }
        
        $this->save();
        $this->calcularSaldoPendiente();
        
        return $this;
    }

    public function calcularTotales($facturar = false)
    {
        $this->factura = $facturar;
        $this->iva = $facturar ? ($this->subtotal * 0.16) : 0;
        $this->total = $this->subtotal + $this->iva;
        $this->save();
    }

    /**
     * Verificar si la renta está vencida
     */
    public function estaVencida(): bool
    {
        return $this->estado === 'activa' && $this->fecha_fin->isPast();
    }

    /**
     * Obtener días restantes
     */
    public function getDiasRestantesAttribute(): int
    {
        if ($this->estado !== 'activa') {
            return 0;
        }
        
        $hoy = now()->startOfDay();
        $fechaFin = $this->fecha_fin->startOfDay();
        
        if ($hoy > $fechaFin) {
            return 0;
        }
        
        return (int) $hoy->diffInDays($fechaFin) + 1;
    }

    /**
     * Obtener días de retraso
     */
    public function getDiasRetrasoAttribute(): int
    {
        if ($this->estado !== 'activa') {
            return 0;
        }
        
        $hoy = now()->startOfDay();
        $fechaFin = $this->fecha_fin->startOfDay();
        
        if ($hoy <= $fechaFin) {
            return 0;
        }
        
        return (int) $fechaFin->diffInDays($hoy);
    }
}