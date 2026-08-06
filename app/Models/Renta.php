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
        'autorizacion_solicitada',
        'autorizacion_aprobada',
        'motivo_autorizacion',
        'datos_pendientes_finalizacion',
        'solicitado_por_id',
        'autorizado_por_id',
        'facturar',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_devolucion' => 'date',
        'fecha_ampliacion' => 'date',
        'autorizacion_solicitada' => 'boolean',
        'autorizacion_aprobada' => 'boolean',
        'datos_pendientes_finalizacion' => 'array',
    ];

    protected static function booted(): void
    {
        static::created(function (Renta $renta) {
            if ($renta->cliente) {
                $renta->cliente->registrarActividad();
            }
        });
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function solicitadoPor()
    {
        return $this->belongsTo(User::class, 'solicitado_por_id');
    }

    public function autorizadoPor()
    {
        return $this->belongsTo(User::class, 'autorizado_por_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleRenta::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function abonos()
    {
        return $this->hasMany(Abono::class);
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

    public function getSaldoPendienteAttribute()
    {
        if ($this->estado === 'cancelada') {
            return 0; 
        }

        $totalPagado = $this->pagos()->sum('monto');
        
        $saldo = $this->total - ($this->deposito ?? 0) - $totalPagado;
        
        return max(0, $saldo); // Nunca devolver un saldo negativo
    }

    public function ampliarDias($diasExtra, $motivo = null, $conIva = false)
    {
        $nuevaFechaFin = $this->fecha_fin->copy()->addDays($diasExtra);
        $this->fecha_fin = $nuevaFechaFin;
        $this->dias_totales += $diasExtra;
        $this->dias_ampliados += $diasExtra;
        $this->fecha_ampliacion = now();
        
        $subtotalAmpliacion = 0;
        foreach ($this->detalles as $detalle) {
            // CORRECCIÓN: Solo tomar en cuenta los que NO se han retornado
            $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta;
            if ($pendiente > 0) {
                $costoExtra = $detalle->precio_dia * $pendiente * $diasExtra;
                $subtotalAmpliacion += $costoExtra;

                $detalle->subtotal += $costoExtra;
                $detalle->dias += $diasExtra;
                $detalle->save();
            }
        }

        $ivaExtra = $conIva ? ($subtotalAmpliacion * 0.16) : 0;
        
        $this->subtotal += $subtotalAmpliacion;
        $this->iva += $ivaExtra;
        $this->total += ($subtotalAmpliacion + $ivaExtra);
        
        if ($motivo) {
            $this->observaciones = ($this->observaciones ? $this->observaciones . "\n" : '') 
                . "Ampliación de {$diasExtra} días. Motivo: {$motivo}";
        }
        
        $this->save();
        
        return $this;
    }

    public function calcularTotales($facturar = false)
    {
        $this->factura = $facturar;
        $this->iva = $facturar ? ($this->subtotal * 0.16) : 0;
        $this->total = $this->subtotal + $this->iva;
        $this->save();
    }

    public function estaVencida(): bool
    {
        return $this->estado === 'activa' && $this->fecha_fin->isPast();
    }

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