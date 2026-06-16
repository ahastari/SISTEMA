<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Renta extends Model
{
    protected $fillable = [
        'folio', 'cliente_id', 'obra_id', 'fecha_inicio', 'fecha_fin', 'dias_totales',
        'subtotal', 'iva', 'total', 'deposito', 'estado', 'observaciones', 'fecha_devolucion'
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
}