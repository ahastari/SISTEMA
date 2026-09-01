<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\Renta;
use App\Models\Obra;
use App\Models\Venta;
use App\Models\CorteCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $sucursalId = session('activo_sucursal_id');

        $isGlobalAdmin = $user->isAdmin() && $sucursalId === 'global';

        // =====================================================
        // CLIENTES
        // =====================================================
        $totalClientes = $isGlobalAdmin
            ? Cliente::count()
            : Cliente::where('sucursal_id', $sucursalId)->count();

        // =====================================================
        // INVENTARIO
        // =====================================================
        if ($isGlobalAdmin) {
            $totalEquipos = Equipo::where('activo', true)->count();
            $totalStock = DB::table('equipo_sucursal')->sum('stock');
            $stockBajo = DB::table('equipo_sucursal')
                ->where('stock', '<=', 5)
                ->where('stock', '>', 0)
                ->count();
            $stockAgotado = DB::table('equipo_sucursal')
                ->where('stock', 0)
                ->count();
        } else {
            $totalEquipos = Equipo::where('activo', true)
                ->whereHas('sucursales', function ($q) use ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                })
                ->count();

            $totalStock = DB::table('equipo_sucursal')
                ->where('sucursal_id', $sucursalId)
                ->sum('stock');

            $stockBajo = DB::table('equipo_sucursal')
                ->where('sucursal_id', $sucursalId)
                ->where('stock', '<=', 5)
                ->where('stock', '>', 0)
                ->count();

            $stockAgotado = DB::table('equipo_sucursal')
                ->where('sucursal_id', $sucursalId)
                ->where('stock', 0)
                ->count();
        }

        // =====================================================
        // OBRAS
        // =====================================================
        $totalObras = $isGlobalAdmin
            ? Obra::where('activa', true)->count()
            : Obra::where('activa', true)->where('sucursal_id', $sucursalId)->count();

        // =====================================================
        // RENTAS
        // =====================================================
        $rentasQuery = Renta::query();

        if (!$isGlobalAdmin) {
            $rentasQuery->where('sucursal_id', $sucursalId);
        }

        $rentasActivas = (clone $rentasQuery)->where('estado', 'activa')->count();
        $rentasFinalizadas = (clone $rentasQuery)->where('estado', 'finalizada')->count();
        $rentasCanceladas = (clone $rentasQuery)->where('estado', 'cancelada')->count();
        $rentasTotales = (clone $rentasQuery)->count();

        // Universo real para "tasa de cierre": activa + finalizada (las canceladas no cuentan como pendientes)
        $rentasNoCanceladas = $rentasTotales - $rentasCanceladas;

        // Contratos vencidos: usa el accessor del modelo Renta::estaVencida()
        // (activa === true y fecha_fin ya pasó), para mantener una sola fuente de verdad
        $rentasVencidas = (clone $rentasQuery)
            ->where('estado', 'activa')
            ->whereDate('fecha_fin', '<', now())
            ->count();

        // Ingresos por rentas del mes actual (excluye canceladas, igual criterio que en Ventas)
        $ingresosRentasMes = (clone $rentasQuery)
            ->where('estado', '!=', 'cancelada')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $rentasPorMes = (clone $rentasQuery)
            ->select(
                DB::raw("EXTRACT(MONTH FROM created_at) as mes"),
                DB::raw("EXTRACT(YEAR FROM created_at) as año"),
                DB::raw("COUNT(*) as total")
            )
            ->whereRaw("EXTRACT(YEAR FROM created_at) >= ?", [now()->subYear()->year])
            ->groupBy('año', 'mes')
            ->orderBy('año', 'asc')
            ->orderBy('mes', 'asc')
            ->get()
            ->map(function ($item) {
                $item->mes_nombre = Carbon::create()->month($item->mes)->locale('es')->monthName;
                return $item;
            });

<<<<<<< HEAD
        
=======
>>>>>>> e1c7d27 (Agregar la generación de comprobantes de pago y mejorar los campos de autorización)
        $topClientes = (clone $rentasQuery)
            ->with('cliente')
            ->select('cliente_id', DB::raw('COUNT(*) as total_rentas'))
            ->groupBy('cliente_id')
            ->orderBy('total_rentas', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $item->cliente_nombre = $item->cliente->nombre_completo ?? 'N/A';
                return $item;
            });

        $ultimasRentas = (clone $rentasQuery)
            ->with('cliente')
            ->latest()
            ->limit(5)
            ->get();

        // =====================================================
        // VENTAS (PUNTO DE VENTA)
        // estado 'completada' = venta válida, 'cancelada' = anulada (confirmado en PuntoVentaController)
        // =====================================================
        $ventasQuery = Venta::where('estado', 'completada');

        if (!$isGlobalAdmin) {
            $ventasQuery->where('sucursal_id', $sucursalId);
        }

        $ventasHoyQuery = (clone $ventasQuery)->whereDate('created_at', now()->toDateString());

        $ventasHoy = (clone $ventasHoyQuery)->count();
        $ingresosVentasHoy = (clone $ventasHoyQuery)->sum('total');

        $ingresosVentasMes = (clone $ventasQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $ventasPorMes = (clone $ventasQuery)
            ->select(
                DB::raw('MONTH(created_at) as mes'),
                DB::raw('YEAR(created_at) as año'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(total) as monto')
            )
            ->whereYear('created_at', '>=', now()->subYear()->year)
            ->groupBy('año', 'mes')
            ->orderBy('año', 'asc')
            ->orderBy('mes', 'asc')
            ->get();

        // Ingreso combinado del mes (lo que más le importa a un directivo)
        $ingresoTotalMes = $ingresosVentasMes + $ingresosRentasMes;

        // Estado de caja del cajero actual (CorteCaja: 1 corte "abierto" por user_id a la vez)
        $corteAbierto = CorteCaja::where('user_id', $user->id)
            ->where('estado', 'abierto')
            ->latest('fecha_apertura')
            ->first();

        $cajaAbierta = $corteAbierto !== null;

        $sucursalNombre = session('activo_sucursal_nombre', 'Todas las sucursales');
        $isAdmin = $user->isAdmin();

        return view('dashboard', compact(
            'totalClientes',
            'totalEquipos',
            'totalStock',
            'totalObras',
            'rentasActivas',
            'rentasFinalizadas',
            'rentasCanceladas',
            'rentasNoCanceladas',
            'rentasTotales',
            'rentasVencidas',
            'stockBajo',
            'stockAgotado',
            'rentasPorMes',
            'ventasPorMes',
            'topClientes',
            'ultimasRentas',
            'ventasHoy',
            'ingresosVentasHoy',
            'ingresoTotalMes',
            'cajaAbierta',
            'corteAbierto',
            'sucursalNombre',
            'isAdmin',
            'isGlobalAdmin'
        ));
    }
}