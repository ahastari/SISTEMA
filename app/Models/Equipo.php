<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipos';

    protected $fillable = [
        'codigo', 
        'codigo_barras', 
        'nombre', 
        'categoria_id', 
        'unidad_medida_id', 
        'tipo_operacion',
        'precio_dia', 
        'precio_venta', 
        'stock', 
        'stock_minimo', 
        'imagen', 
        'descripcion', 
        'activo'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'equipo_sucursal')
                    ->withPivot('stock', 'stock_minimo')
                    ->withTimestamps();
    }

    /**
     * 🔥 OBTENER STOCK EN UNA SUCURSAL ESPECÍFICA
     */
    public function getStockEnSucursal($sucursalId): int
    {
        $pivot = DB::table('equipo_sucursal')
            ->where('equipo_id', $this->id)
            ->where('sucursal_id', $sucursalId)
            ->first();
        
        return $pivot ? (int) $pivot->stock : 0;
    }

    /**
     * 🔥 ACTUALIZAR STOCK EN UNA SUCURSAL (SUMAR, RESTAR O ESTABLECER)
     * Y SINCRONIZAR EL STOCK TOTAL DEL EQUIPO
     */
    public function actualizarStockEnSucursal($sucursalId, $cantidad, $operacion = 'sumar'): void
    {
        // Obtener stock actual en la sucursal
        $stockActual = $this->getStockEnSucursal($sucursalId);
        
        // Calcular nuevo stock dependiendo de la operación
        if ($operacion === 'establecer') {
            $nuevoStock = $cantidad;
        } else {
            $nuevoStock = $operacion === 'sumar' 
                ? $stockActual + $cantidad 
                : $stockActual - $cantidad;
        }
        
        // Asegurar que el stock no sea negativo
        $nuevoStock = max(0, $nuevoStock);
        
        // Verificar si ya existe un registro en la tabla pivote
        $existe = DB::table('equipo_sucursal')
            ->where('equipo_id', $this->id)
            ->where('sucursal_id', $sucursalId)
            ->exists();
        
        if ($existe) {
            // ACTUALIZAR registro existente
            DB::table('equipo_sucursal')
                ->where('equipo_id', $this->id)
                ->where('sucursal_id', $sucursalId)
                ->update([
                    'stock' => $nuevoStock,
                    'updated_at' => now(),
                ]);
        } else {
            // INSERTAR nuevo registro
            DB::table('equipo_sucursal')->insert([
                'equipo_id' => $this->id,
                'sucursal_id' => $sucursalId,
                'stock' => $nuevoStock,
                'stock_minimo' => $this->stock_minimo ?? 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // 🔥 SINCRONIZAR STOCK TOTAL DEL EQUIPO INMEDIATAMENTE
        $this->sincronizarStockTotal();
    }

    /**
     * 🔥 VERIFICAR DISPONIBILIDAD EN UNA SUCURSAL
     */
    public function disponibleEnSucursal($sucursalId, $cantidad): bool
    {
        return $this->getStockEnSucursal($sucursalId) >= $cantidad;
    }

    /**
     * 🔥 SINCRONIZAR STOCK TOTAL (suma de todas las sucursales)
     */
    public function sincronizarStockTotal(): void
    {
        $stockTotal = DB::table('equipo_sucursal')
            ->where('equipo_id', $this->id)
            ->sum('stock');
        
        $this->stock = (int) $stockTotal;
        $this->save();
        
        \Log::info("📊 Stock total sincronizado", [
            'equipo_id' => $this->id,
            'equipo' => $this->nombre,
            'stock_total' => $this->stock,
        ]);
    }

    /**
     * 🔥 OBTENER TODOS LOS STOCKS POR SUCURSAL
     */
    public function getStocksPorSucursal(): array
    {
        $stocks = DB::table('equipo_sucursal')
            ->join('sucursales', 'equipo_sucursal.sucursal_id', '=', 'sucursales.id')
            ->where('equipo_sucursal.equipo_id', $this->id)
            ->select(
                'sucursales.id',
                'sucursales.nombre',
                'equipo_sucursal.stock',
                'equipo_sucursal.stock_minimo'
            )
            ->get();
        
        return $stocks->toArray();
    }
}