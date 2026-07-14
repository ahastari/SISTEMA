<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    // 🔥 AQUÍ AGREGAMOS SUCURSAL_ID Y LOS DEMÁS CAMPOS DEL PUNTO DE VENTA
    protected $fillable = [
        'folio', 'corte_caja_id', 'sucursal_id', 'cliente_id', 'cliente_nombre', 
        'subtotal', 'iva', 'total', 'metodo_pago', 'estado', 'observaciones',
        'requiere_factura', 'rfc_cliente'
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // 🔒 Relación con la sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
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