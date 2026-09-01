<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\Renta;
use App\Models\Obra;
use App\Models\Sucursal;
use App\Models\Venta;
use App\Models\CorteCaja;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $sucursalId = session('activo_sucursal_id');
        
        // 1. Determinar el rol y alcance visual
        $isGlobalAdmin = $user->isAdmin() && $sucursalId === 'global';
        $sucursalNombre = 'Consola / Matriz General';

        if (!$isGlobalAdmin && $sucursalId) {
            $sucursal = Sucursal::find($sucursalId);
            $sucursalNombre = $sucursal ? $sucursal->nombre : 'Sucursal Desconocida';
        }

        // ==========================================
        // 2. INDICADORES DE CAJA POS DEL USUARIO
        // ==========================================
        $corteAbierto = CorteCaja::where('estado', 'abierto')
            ->where('user_id', $user->id)
            ->first();
        $cajaAbierta = $corteAbierto ? true : false;

        // ==========================================
        // 3. KPIs FINANCIEROS Y OPERATIVOS (Mes y Hoy)
        // ==========================================
        // Ventas del mes actual
        $ventasMesQuery = Venta::where('estado', 'completada')->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'));
        if (!$isGlobalAdmin) $ventasMesQuery->where('sucursal_id', $sucursalId);
        $ingresoVentasMes = $ventasMesQuery->sum('total');

        // Pagos de rentas del mes actual
        $pagosRentasMesQuery = Pago::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))
            ->whereHas('renta', function($q) use ($isGlobalAdmin, $sucursalId) {
                if (!$isGlobalAdmin) $q->where('sucursal_id', $sucursalId);
            });
        $ingresoRentasMes = $pagosRentasMesQuery->sum('monto');
        
        $ingresoTotalMes = $ingresoVentasMes + $ingresoRentasMes;

        // Ventas exclusivas de Hoy
        $ventasHoyQuery = Venta::where('estado', 'completada')->whereDate('created_at', date('Y-m-d'));
        if (!$isGlobalAdmin) $ventasHoyQuery->where('sucursal_id', $sucursalId);
        $ventasHoy = $ventasHoyQuery->count();
        $ingresosVentasHoy = $ventasHoyQuery->sum('total');


        // ==========================================
        // 4. MÉTRICAS DE CLIENTES Y OBRAS
        // ==========================================
        $queryClientes = Cliente::query();
        if (!$isGlobalAdmin) $queryClientes->where('sucursal_id', $sucursalId);
        $totalClientes = $queryClientes->count();

        $queryObras = Obra::query();
        if (!$isGlobalAdmin && \Illuminate\Support\Facades\Schema::hasColumn('obras', 'sucursal_id')) {
            $queryObras->where('sucursal_id', $sucursalId);
        }
        $totalObras = $queryObras->count();


        // ==========================================
        // 5. MÉTRICAS DE INVENTARIO (EQUIPOS Y STOCK)
        // ==========================================
        if ($isGlobalAdmin) {
            $totalEquipos = Equipo::where('activo', true)->count();
            $totalStock = Equipo::where('activo', true)->sum('stock');
            $stockBajo = Equipo::where('activo', true)->where('stock', '>', 0)->where('stock', '<=', 5)->count();
            $stockAgotado = Equipo::where('activo', true)->where('stock', '<=', 0)->count();
        } else {
            $equiposSucursal = DB::table('equipo_sucursal')->where('sucursal_id', $sucursalId)->get();
            $totalEquipos = $equiposSucursal->count();
            $totalStock = $equiposSucursal->sum('stock');
            $stockBajo = $equiposSucursal->where('stock', '>', 0)->where('stock', '<=', 5)->count();
            $stockAgotado = $equiposSucursal->where('stock', '<=', 0)->count();
        }


        // ==========================================
        // 6. MÉTRICAS DE RENTAS Y ALERTAS DE VENCIMIENTO
        // ==========================================
        $queryRentas = Renta::query();
        if (!$isGlobalAdmin) $queryRentas->where('sucursal_id', $sucursalId);

        $rentasTotales = (clone $queryRentas)->count();
        $rentasCanceladas = (clone $queryRentas)->where('estado', 'cancelada')->count();
        $rentasFinalizadas = (clone $queryRentas)->where('estado', 'finalizada')->count();
        $rentasNoCanceladas = $rentasTotales - $rentasCanceladas;

        $rentasActivasQuery = (clone $queryRentas)->where('estado', 'activa');
        $rentasActivas = $rentasActivasQuery->count();
        
        // Calcular manualmente cuántas rentas activas están vencidas
        $rentasVencidas = 0;
        foreach ($rentasActivasQuery->get() as $renta) {
            if ($renta->estaVencida()) {
                $rentasVencidas++;
            }
        }
        
        $ultimasRentas = (clone $queryRentas)->where('estado', '!=', 'cancelada')->with('cliente')->latest()->take(6)->get();


        // ==========================================
        // 7. RANKING: TOP CLIENTES (Lealtad)
        // ==========================================
        $queryTop = Renta::select('cliente_id', DB::raw('count(*) as total_rentas'))
            ->with('cliente')
            ->whereNotNull('cliente_id')
            ->where('estado', '!=', 'cancelada')
            ->groupBy('cliente_id')
            ->orderByDesc('total_rentas')
            ->take(5);
        
        if (!$isGlobalAdmin) {
            $queryTop->where('sucursal_id', $sucursalId);
        }
        
        $topClientes = $queryTop->get()->map(function($item) {
            return (object)[
                'cliente_nombre' => $item->cliente ? $item->cliente->nombre_completo : 'Público General',
                'total_rentas' => $item->total_rentas
            ];
        });


        // ==========================================
        // 8. GRÁFICOS: RENDIMIENTO MENSUAL (Rentas vs Ventas)
        // ==========================================
        $mesesNombres = [1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'];
        
        // RENTAS
        $queryRentasMes = Renta::select(DB::raw('MONTH(created_at) as mes'), DB::raw('count(*) as total'))
            ->whereYear('created_at', date('Y'))->where('estado', '!=', 'cancelada')->groupBy('mes')->orderBy('mes');
        if (!$isGlobalAdmin) $queryRentasMes->where('sucursal_id', $sucursalId);
        
        $rentasMesAgrupadas = $queryRentasMes->get()->keyBy('mes');
        $rentasPorMes = collect();
        
        // VENTAS (Monto monetario de ventas por mes)
        $queryVentasMes = Venta::select(DB::raw('MONTH(created_at) as mes'), DB::raw('SUM(total) as monto'))
            ->whereYear('created_at', date('Y'))->where('estado', 'completada')->groupBy('mes')->orderBy('mes');
        if (!$isGlobalAdmin) $queryVentasMes->where('sucursal_id', $sucursalId);

        $ventasMesAgrupadas = $queryVentasMes->get()->keyBy('mes');
        $ventasPorMes = collect();

        // Rellenar array de 12 meses
        for ($i = 1; $i <= 12; $i++) {
            $rentasPorMes->push((object)[
                'mes_nombre' => $mesesNombres[$i],
                'total' => isset($rentasMesAgrupadas[$i]) ? $rentasMesAgrupadas[$i]->total : 0
            ]);

            $ventasPorMes->push((object)[
                'mes' => $mesesNombres[$i],
                'monto' => isset($ventasMesAgrupadas[$i]) ? $ventasMesAgrupadas[$i]->monto : 0
            ]);
        }

        return view('dashboard', compact(
            'isGlobalAdmin', 'sucursalNombre', 'cajaAbierta', 'corteAbierto',
            'ingresoTotalMes', 'ventasHoy', 'ingresosVentasHoy', 
            'totalClientes', 'totalEquipos', 'totalStock', 'stockBajo', 'stockAgotado',
            'rentasActivas', 'rentasVencidas', 'rentasTotales', 'rentasNoCanceladas', 'rentasFinalizadas', 'rentasCanceladas',
            'totalObras', 'ultimasRentas', 'topClientes', 'rentasPorMes', 'ventasPorMes'
        ));
    }
}