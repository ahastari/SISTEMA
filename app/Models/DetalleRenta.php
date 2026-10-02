<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleRenta extends Model
{
    protected $table = 'detalles_rentas';

    protected $fillable = [
        'renta_id',
        'equipo_id',
        'cantidad',
        'cantidad_devuelta',
        'precio_dia',
        'tipo_tarifa',
        'dias',
        'subtotal'
    ];

    /**
     * ¿Este renglón se cobra por m² (una sola vez) en lugar de por día?
     */
    public function esPorM2(): bool
    {
        return $this->tipo_tarifa === 'm2';
    }

    /**
     * Costo por día de lo que sigue pendiente de devolver.
     * Los renglones por m² NO generan cobro diario (ni multas por retraso, ni ampliaciones).
     */
    public function costoDiarioPendiente(): float
    {
        $pendiente = $this->cantidad - $this->cantidad_devuelta;

        if ($pendiente <= 0 || $this->esPorM2()) {
            return 0.0;
        }

        return (float) $this->precio_dia * $pendiente;
    }

    public function getEtiquetaTarifaAttribute(): string
    {
        return $this->esPorM2() ? 'm²' : 'día';
    }

    public function renta()
    {
        return $this->belongsTo(Renta::class);
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }
}