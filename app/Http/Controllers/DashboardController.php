<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\Renta;
use App\Models\DetalleRenta;
use App\Models\Obra;
use App\Models\Sucursal;
use App\Models\Venta;
use App\Models\CorteCaja;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
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
        $ventasMesQuery = Venta::where('estado', 'completada')->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'));
        if (!$isGlobalAdmin) $ventasMesQuery->where('sucursal_id', $sucursalId);
        $ingresoVentasMes = $ventasMesQuery->sum('total');

        $pagosRentasMesQuery = Pago::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))
            ->whereHas('renta', function($q) use ($isGlobalAdmin, $sucursalId) {
                if (!$isGlobalAdmin) $q->where('sucursal_id', $sucursalId);
            });
        $ingresoRentasMes = $pagosRentasMesQuery->sum('monto');

        $ingresoTotalMes = $ingresoVentasMes + $ingresoRentasMes;

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
        if (!$isGlobalAdmin && Schema::hasColumn('obras', 'sucursal_id')) {
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

        $rentasActivasLista = (clone $queryRentas)->where('estado', 'activa')->get();
        $rentasActivas = $rentasActivasLista->count();

        // Vencidas (se calcula una sola vez y se reutiliza por sucursal)
        $vencidasLista = $rentasActivasLista->filter(fn($r) => $r->estaVencida());
        $rentasVencidas = $vencidasLista->count();

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

        $queryRentasMes = Renta::select(DB::raw('MONTH(created_at) as mes'), DB::raw('count(*) as total'))
            ->whereYear('created_at', date('Y'))->where('estado', '!=', 'cancelada')->groupBy('mes')->orderBy('mes');
        if (!$isGlobalAdmin) $queryRentasMes->where('sucursal_id', $sucursalId);

        $rentasMesAgrupadas = $queryRentasMes->get()->keyBy('mes');
        $rentasPorMes = collect();

        $queryVentasMes = Venta::select(DB::raw('MONTH(created_at) as mes'), DB::raw('SUM(total) as monto'))
            ->whereYear('created_at', date('Y'))->where('estado', 'completada')->groupBy('mes')->orderBy('mes');
        if (!$isGlobalAdmin) $queryVentasMes->where('sucursal_id', $sucursalId);

        $ventasMesAgrupadas = $queryVentasMes->get()->keyBy('mes');
        $ventasPorMes = collect();

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

        // ==========================================
        // 9. DATOS EXTRA: COMPARATIVOS Y GRÁFICAS NUEVAS
        // ==========================================
        // Período del filtro (por defecto: mes en curso hasta hoy)
        try {
            $inicio = Carbon::parse($request->input('fecha_inicio', date('Y-m-01')))->startOfDay();
            $fin    = Carbon::parse($request->input('fecha_fin', date('Y-m-d')))->endOfDay();
        } catch (\Exception $e) {
            $inicio = Carbon::now()->startOfMonth();
            $fin    = Carbon::now()->endOfDay();
        }
        if ($inicio->gt($fin)) { [$inicio, $fin] = [$fin->copy()->startOfDay(), $inicio->copy()->endOfDay()]; }

        $extra = $this->datosExtra($isGlobalAdmin, $sucursalId, $vencidasLista, $inicio, $fin);

        return view('dashboard', array_merge(compact(
            'isGlobalAdmin', 'sucursalNombre', 'cajaAbierta', 'corteAbierto',
            'ingresoTotalMes', 'ventasHoy', 'ingresosVentasHoy',
            'totalClientes', 'totalEquipos', 'totalStock', 'stockBajo', 'stockAgotado',
            'rentasActivas', 'rentasVencidas', 'rentasTotales', 'rentasNoCanceladas', 'rentasFinalizadas', 'rentasCanceladas',
            'totalObras', 'ultimasRentas', 'topClientes', 'rentasPorMes', 'ventasPorMes'
        ), $extra));
    }

    // ------------------------------------------------------------------
    // Helpers de consulta
    // ------------------------------------------------------------------

    /** Ventas completadas en un rango, filtradas por sucursal si no es admin global. */
    private function ventasQ($desde, $hasta, $isGlobal, $sucursalId)
    {
        $q = Venta::where('estado', 'completada')->whereBetween('created_at', [$desde, $hasta]);
        if (!$isGlobal) $q->where('sucursal_id', $sucursalId);
        return $q;
    }

    /** Pagos de rentas en un rango, con sucursal tomada de la renta (alias sucursal_id). */
    private function pagosQ($desde, $hasta, $isGlobal, $sucursalId)
    {
        $pago = new Pago;
        $tp = $pago->getTable();
        $fk = $pago->renta()->getForeignKeyName();
        $q = DB::table($tp)
            ->join('rentas', 'rentas.id', '=', "$tp.$fk")
            ->whereBetween("$tp.created_at", [$desde, $hasta]);
        if (!$isGlobal) $q->where('rentas.sucursal_id', $sucursalId);
        return [$q, $tp];
    }

    private function datosExtra(bool $isGlobal, $sucursalId, $vencidasLista, Carbon $inicio, Carbon $fin): array
    {
        $nombresMes = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];

        // ---- Período anterior de igual duración (para comparar)
        $dias      = $inicio->diffInDays($fin) + 1;
        $finAnt    = $inicio->copy()->subSecond();
        $inicioAnt = $inicio->copy()->subDays($dias)->startOfDay();

        // ---- KPIs del período
        $ventasPeriodoMonto = (float) $this->ventasQ($inicio, $fin, $isGlobal, $sucursalId)->sum('total');
        $ventasPeriodoN     = (int) $this->ventasQ($inicio, $fin, $isGlobal, $sucursalId)->count();
        [$pq, $tp] = $this->pagosQ($inicio, $fin, $isGlobal, $sucursalId);
        $rentasPeriodoMonto = (float) $pq->sum("$tp.monto");
        $ingresosPeriodo    = $ventasPeriodoMonto + $rentasPeriodoMonto;
        $ticketPromedio     = $ventasPeriodoN > 0 ? $ventasPeriodoMonto / $ventasPeriodoN : 0;

        [$pAnt, $tp] = $this->pagosQ($inicioAnt, $finAnt, $isGlobal, $sucursalId);
        $ingresosPeriodoAnterior = (float) $this->ventasQ($inicioAnt, $finAnt, $isGlobal, $sucursalId)->sum('total')
                                 + (float) $pAnt->sum("$tp.monto");

        // ---- Flujo (diario; mensual si el rango supera ~3 meses)
        $porMes = $dias > 92;
        $fmtSql = $porMes ? '%Y-%m' : '%Y-%m-%d';
        $vSerie = $this->ventasQ($inicio, $fin, $isGlobal, $sucursalId)
            ->selectRaw("DATE_FORMAT(created_at,'$fmtSql') d, SUM(total) t")->groupBy('d')->pluck('t', 'd');
        [$pq, $tp] = $this->pagosQ($inicio, $fin, $isGlobal, $sucursalId);
        $rSerie = $pq->selectRaw("DATE_FORMAT($tp.created_at,'$fmtSql') d, SUM($tp.monto) t")->groupBy('d')->pluck('t', 'd');

        $labels = []; $ventasS = []; $rentasS = [];
        if ($porMes) {
            for ($c = $inicio->copy()->startOfMonth(); $c->lte($fin); $c->addMonthNoOverflow()) {
                $k = $c->format('Y-m');
                $labels[]  = $nombresMes[$c->month] . ' ' . $c->format('y');
                $ventasS[] = (float) ($vSerie[$k] ?? 0);
                $rentasS[] = (float) ($rSerie[$k] ?? 0);
            }
        } else {
            for ($c = $inicio->copy()->startOfDay(); $c->lte($fin); $c->addDay()) {
                $k = $c->format('Y-m-d');
                $labels[]  = $c->format('d/m');
                $ventasS[] = (float) ($vSerie[$k] ?? 0);
                $rentasS[] = (float) ($rSerie[$k] ?? 0);
            }
        }
        $flujo = ['labels' => $labels, 'ventas' => $ventasS, 'rentas' => $rentasS, 'por_mes' => $porMes];

        // ---- Métodos de pago (solo si existe la columna)
        $ventasPorMetodoPago = collect();
        if (Schema::hasColumn((new Venta)->getTable(), 'metodo_pago')) {
            $ventasPorMetodoPago = $this->ventasQ($inicio, $fin, $isGlobal, $sucursalId)
                ->selectRaw("COALESCE(metodo_pago, 'Sin definir') as metodo, SUM(total) as total")
                ->groupBy('metodo_pago')->orderByDesc('total')->get()
                ->map(fn($r) => (object)['metodo' => ucfirst((string) $r->metodo), 'total' => (float) $r->total]);
        }

        // ---- Equipos más rentados (suma de piezas en rentas iniciadas en el período; excluye canceladas)
        $tabla = (new DetalleRenta)->getTable();
        $nombreCol = Schema::hasColumn('equipos', 'nombre') ? 'nombre' : 'descripcion';
        $q = DB::table($tabla)
            ->join('rentas', 'rentas.id', '=', "$tabla.renta_id")
            ->join('equipos', 'equipos.id', '=', "$tabla.equipo_id")
            ->where('rentas.estado', '!=', 'cancelada')
            ->whereBetween('rentas.fecha_inicio', [$inicio->toDateString(), $fin->toDateString()]);
        if (Schema::hasColumn('rentas', 'deleted_at')) $q->whereNull('rentas.deleted_at');
        if (!$isGlobal) $q->where('rentas.sucursal_id', $sucursalId);
        $topEquipos = $q->selectRaw("equipos.$nombreCol as nombre, SUM($tabla.cantidad) as veces")
            ->groupBy('equipos.id', "equipos.$nombreCol")->orderByDesc('veces')->limit(6)->get();

        $extra = compact(
            'inicio', 'fin', 'flujo', 'ingresosPeriodo', 'ingresosPeriodoAnterior',
            'ventasPeriodoN', 'ventasPeriodoMonto', 'rentasPeriodoMonto', 'ticketPromedio',
            'ventasPorMetodoPago', 'topEquipos'
        );

        // ---- Comparativo entre sucursales (solo admin global)
        if ($isGlobal) {
            $vMes = $this->ventasQ($inicio, $fin, true, null)->selectRaw('sucursal_id, SUM(total) t')->groupBy('sucursal_id')->pluck('t', 'sucursal_id');
            [$pq, $tp] = $this->pagosQ($inicio, $fin, true, null);
            $rMes = $pq->selectRaw("rentas.sucursal_id, SUM($tp.monto) t")->groupBy('rentas.sucursal_id')->pluck('t', 'sucursal_id');

            $vAnt = $this->ventasQ($inicioAnt, $finAnt, true, null)->selectRaw('sucursal_id, SUM(total) t')->groupBy('sucursal_id')->pluck('t', 'sucursal_id');
            [$pq, $tp] = $this->pagosQ($inicioAnt, $finAnt, true, null);
            $rAnt = $pq->selectRaw("rentas.sucursal_id, SUM($tp.monto) t")->groupBy('rentas.sucursal_id')->pluck('t', 'sucursal_id');

            $vencidas = $vencidasLista->groupBy('sucursal_id')->map->count();

            $stock = DB::table('equipo_sucursal')
                ->selectRaw('sucursal_id,
                    SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) agotados,
                    SUM(CASE WHEN stock > 0 AND stock <= 5 THEN 1 ELSE 0 END) bajos')
                ->groupBy('sucursal_id')->get()->keyBy('sucursal_id');

            $sucursales = Sucursal::select('id', 'nombre')->orderBy('nombre')->get();

            $sucursalesComparativo = $sucursales->map(fn($s) => [
                'nombre'        => $s->nombre,
                'ventas'        => (float) ($vMes[$s->id] ?? 0),
                'rentas'        => (float) ($rMes[$s->id] ?? 0),
                'mes_anterior'  => (float) (($vAnt[$s->id] ?? 0) + ($rAnt[$s->id] ?? 0)), // período anterior
                'vencidas'      => (int) ($vencidas[$s->id] ?? 0),
                'stock_bajo'    => (int) ($stock[$s->id]->bajos ?? 0),
                'stock_agotado' => (int) ($stock[$s->id]->agotados ?? 0),
            ])->values();

            // Tendencia: 6 meses terminando en el mes del filtro
            $meses = collect(range(5, 0))->map(fn($i) => $fin->copy()->startOfMonth()->subMonthsNoOverflow($i));
            $ini6  = $meses->first()->copy()->startOfMonth();
            $tv = $this->ventasQ($ini6, $fin, true, null)
                ->selectRaw("sucursal_id, DATE_FORMAT(created_at,'%Y-%m') m, SUM(total) t")->groupBy('sucursal_id', 'm')->get();
            [$pq, $tp] = $this->pagosQ($ini6, $fin, true, null);
            $tr = $pq->selectRaw("rentas.sucursal_id, DATE_FORMAT($tp.created_at,'%Y-%m') m, SUM($tp.monto) t")->groupBy('rentas.sucursal_id', 'm')->get();

            $sucursalesTendencia = [
                'labels' => $meses->map(fn($m) => $nombresMes[$m->month] . ' ' . $m->format('y'))->all(),
                'series' => $sucursales->map(fn($s) => [
                    'nombre' => $s->nombre,
                    'data'   => $meses->map(fn($m) => (float) (
                        $tv->where('sucursal_id', $s->id)->where('m', $m->format('Y-m'))->sum('t') +
                        $tr->where('sucursal_id', $s->id)->where('m', $m->format('Y-m'))->sum('t')
                    ))->all(),
                ])->values()->all(),
            ];

            $extra += compact('sucursalesComparativo', 'sucursalesTendencia');
        }

        return $extra;
    }
}