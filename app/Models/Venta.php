<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'folio', 'cliente_id', 'cliente_nombre', 'subtotal', 'iva', 'total',
        'metodo_pago', 'estado', 'observaciones'
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public static function generarFolio()
    {
        $year = date('Y');
        $month = date('m');
        $ultimaVenta = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimaVenta) {
            $ultimoNumero = intval(substr($ultimaVenta->folio, -4));
            $nuevoNumero = str_pad($ultimoNumero + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nuevoNumero = '0001';
        }

        return 'V-' . $year . $month . '-' . $nuevoNumero;
    }
}