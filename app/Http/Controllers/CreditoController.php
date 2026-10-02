<?php

namespace App\Http\Controllers;

use App\Models\AbonoVenta;
use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use App\Models\Sucursal;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Cartera de ventas a crédito: consulta por sucursal, detalle, saldos, vencimientos y abonos.
 */
class CreditoController extends Controller
{
    /** Carpeta de vistas (resources/views/<VISTA>). Cámbiala si guardaste las vistas en otra carpeta. */
    private const VISTA = 'credito';

    private function esAdminGlobal(): bool
    {
        return auth()->user()->isAdmin() && session('activo_sucursal_id') === 'global';
    }

    /** El usuario solo ve/cobra créditos de su sucursal activa (el admin global ve todos). */
    private function puedeAccederVenta(Venta $venta): bool
    {
        return $this->esAdminGlobal()
            || (int) $venta->sucursal_id === (int) session('activo_sucursal_id');
    }

    private function cajaAbierta(): bool
    {
        return CorteCaja::where('estado', 'abierto')->where('user_id', auth()->id())->exists();
    }

    /**
     * Desglose real de un abono: un abono normal devuelve 1 renglón; uno MIXTO devuelve
     * sus componentes (efectivo / transferencia / tarjeta). Úsalo para reportes y tickets.
     */
    public static function desglosePago(AbonoVenta $a): array
    {
        if ($a->metodo === 'mixto') {
            $p = $a->pagos_mixtos;
            if (is_string($p)) $p = json_decode($p, true);
            if (is_array($p) && count($p)) {
                return array_values(array_map(fn ($x) => [
                    'metodo' => $x['metodo'],
                    'monto'  => round((float) $x['monto'], 2),
                ], $p));
            }
        }
        return [['metodo' => $a->metodo, 'monto' => round((float) $a->monto, 2)]];
    }

    /** "Efectivo $500.00 + Tarjeta $300.00" (vacío si el abono no es mixto). */
    public static function textoDesglose(AbonoVenta $a): string
    {
        if ($a->metodo !== 'mixto') return '';
        return collect(self::desglosePago($a))
            ->map(fn ($p) => ucfirst($p['metodo']) . ' $' . number_format($p['monto'], 2))
            ->implode(' + ');
    }

    /** Calcula saldo, vencimiento, estado y lista de abonos de un crédito. */
    private function armarCredito(Venta $v, Collection $abonos, Carbon $hoy): object
    {
        $abonado = round((float) $abonos->sum('monto'), 2);
        $total   = round((float) $v->total, 2);
        $saldo   = round($total - $abonado, 2);
        $vence   = $v->created_at->copy()->addDays((int) $v->dias_credito)->startOfDay();
        $dias    = (int) round(($vence->timestamp - $hoy->timestamp) / 86400); // + faltan, - atraso

        if ($v->estado !== 'completada')  $estado = 'cancelado';
        elseif ($saldo <= 0.009)          $estado = 'liquidado';
        elseif ($dias < 0)                $estado = 'vencido';
        elseif ($abonado > 0)             $estado = 'parcial';
        else                              $estado = 'pendiente';

        $corrido = $total;
        $lista = $abonos->values()->map(function ($a, $i) use (&$corrido) {
            $corrido = round($corrido - (float) $a->monto, 2);
            return [
                'id'       => $a->id,
                'n'        => $i + 1,
                'fecha'    => $a->created_at->format('d/m/Y H:i'),
                'monto'    => (float) $a->monto,
                'metodo'   => $a->metodo,
                'desglose' => self::textoDesglose($a),
                'ref'      => $a->referencia,
                'obs'      => $a->observaciones,
                'usuario'  => $a->usuario->name ?? '—',
                'saldo_despues' => max(0, $corrido),
                'ticket'   => route('puntoventa.creditos.ticket', $a->id),
            ];
        })->all();

        return (object) [
            'id'             => $v->id,
            'folio'          => $v->folio,
            'cliente'        => $v->cliente_nombre ?: ($v->cliente->nombre_completo ?? 'Público General'),
            'sucursal'       => $v->sucursal->nombre ?? 'Sin sucursal',
            'fecha'          => $v->created_at,
            'dias_credito'   => (int) $v->dias_credito,
            'vence'          => $vence,
            'dias_restantes' => $dias,
            'total'          => $total,
            'abonado'        => $abonado,
            'saldo'          => $estado === 'cancelado' ? 0 : max(0, $saldo),
            'porcentaje'     => $total > 0 ? min(100, (int) round($abonado / $total * 100)) : 0,
            'estado'         => $estado,
            'abonos'         => $lista,
        ];
    }

    public function index(Request $request)
    {
        $esGlobal = $this->esAdminGlobal();
        $hoy = now()->startOfDay();

        $q = Venta::with(['cliente', 'sucursal'])
            ->where('metodo_pago', 'credito')
            ->where('estado', 'completada');

        if (!$esGlobal) {
            $q->where('sucursal_id', session('activo_sucursal_id'));
        } elseif ($request->filled('sucursal')) {
            $q->where('sucursal_id', $request->sucursal);
        }
        if ($request->filled('desde')) $q->whereDate('created_at', '>=', $request->desde);
        if ($request->filled('hasta')) $q->whereDate('created_at', '<=', $request->hasta);
        if ($request->filled('buscar')) {
            $b = trim($request->buscar);
            $q->where(function ($w) use ($b) {
                $w->where('folio', 'like', "%{$b}%")->orWhere('cliente_nombre', 'like', "%{$b}%");
            });
        }

        $ventas = $q->latest()->get();

        $abonosPorVenta = AbonoVenta::with('usuario')
            ->whereIn('venta_id', $ventas->pluck('id'))
            ->orderBy('created_at')->orderBy('id')
            ->get()
            ->groupBy('venta_id');

        $filas = $ventas->map(fn ($v) => $this->armarCredito($v, $abonosPorVenta->get($v->id, collect()), $hoy));

        $kpi = [
            'creditos'   => $filas->count(),
            'total'      => $filas->sum('total'),
            'abonado'    => $filas->sum('abonado'),
            'por_cobrar' => $filas->sum('saldo'),
            'vencido'    => $filas->where('estado', 'vencido')->sum('saldo'),
            'n_vencidos' => $filas->where('estado', 'vencido')->count(),
        ];

        $estadoFiltro = $request->get('estado', 'con_saldo');
        if ($estadoFiltro === 'con_saldo')  $filas = $filas->where('saldo', '>', 0);
        elseif ($estadoFiltro !== 'todos')  $filas = $filas->where('estado', $estadoFiltro);
        $filas = $filas->values();

        $page    = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $creditos = new LengthAwarePaginator(
            $filas->forPage($page, $perPage)->values(),
            $filas->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $sucursales  = $esGlobal ? Sucursal::orderBy('nombre')->get() : collect();
        $cajaAbierta = $this->cajaAbierta();
        $vista       = self::VISTA;

        return view(self::VISTA . '.creditos', compact('creditos', 'kpi', 'sucursales', 'esGlobal', 'estadoFiltro', 'cajaAbierta', 'vista'));
    }

    /** Detalle de un crédito: qué se vendió, datos de la venta y todos los abonos. */
    public function show(Venta $venta)
    {
        abort_unless($venta->metodo_pago === 'credito', 404);
        abort_unless($this->puedeAccederVenta($venta), 403, 'Este crédito pertenece a otra sucursal.');

        $venta->load(['cliente', 'sucursal', 'detalles.equipo']);

        $abonos = AbonoVenta::with('usuario')
            ->where('venta_id', $venta->id)
            ->orderBy('created_at')->orderBy('id')
            ->get();

        $credito  = $this->armarCredito($venta, $abonos, now()->startOfDay());
        $vendedor = $venta->corte_caja_id
            ? optional(CorteCaja::with('user')->find($venta->corte_caja_id))->user
            : null;
        $cajaAbierta = $this->cajaAbierta();
        $vista       = self::VISTA;

        return view(self::VISTA . '.credito_detalle', compact('venta', 'credito', 'vendedor', 'cajaAbierta', 'vista'));
    }

    public function abonar(Request $request, Venta $venta)
    {
        $request->validate([
            'monto'         => 'required_unless:metodo,mixto|nullable|numeric|min:0.01',
            'metodo'        => 'required|in:efectivo,transferencia,tarjeta,mixto',
            'pagos_mixtos'  => 'required_if:metodo,mixto|nullable|array|size:2',
            'pagos_mixtos.*.metodo' => 'required_with:pagos_mixtos|in:efectivo,transferencia,tarjeta',
            'pagos_mixtos.*.monto'  => 'required_with:pagos_mixtos|numeric|min:0.01',
            'referencia'    => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Caja del usuario: SIN caja abierta no se registra ningún abono
            // (ni efectivo, ni transferencia, ni tarjeta, ni mixto). El abono queda en el corte de este turno.
            $corte = CorteCaja::where('estado', 'abierto')->where('user_id', auth()->id())->first();
            if (!$corte) {
                throw new \Exception('No hay caja abierta. Abre tu caja en el Punto de Venta para poder registrar abonos.');
            }

            $venta = Venta::whereKey($venta->id)->lockForUpdate()->firstOrFail();

            if (!$this->puedeAccederVenta($venta)) {
                throw new \Exception('Este crédito pertenece a otra sucursal.');
            }
            if ($venta->metodo_pago !== 'credito' || $venta->estado !== 'completada') {
                throw new \Exception('Esta venta no es un crédito vigente.');
            }
            if ($venta->autorizacion_solicitada) {
                throw new \Exception('La venta tiene una cancelación pendiente; no se pueden registrar abonos.');
            }

            $abonado = round((float) AbonoVenta::where('venta_id', $venta->id)->sum('monto'), 2);
            $saldo   = round((float) $venta->total - $abonado, 2);

            // Desglose del pago. Si es MIXTO el monto se recalcula en el servidor sumando los 2 pagos.
            if ($request->metodo === 'mixto') {
                $pagos = collect($request->pagos_mixtos)->map(fn ($p) => [
                    'metodo' => $p['metodo'],
                    'monto'  => round((float) $p['monto'], 2),
                ])->values()->all();
                if (count(array_unique(array_column($pagos, 'metodo'))) < 2) {
                    throw new \Exception('En pago mixto debes elegir dos métodos de pago diferentes.');
                }
                $monto = round(array_sum(array_column($pagos, 'monto')), 2);
            } else {
                $monto = round((float) $request->monto, 2);
                $pagos = [['metodo' => $request->metodo, 'monto' => $monto]];
            }

            if ($saldo <= 0.009) {
                throw new \Exception('Este crédito ya está liquidado.');
            }
            if ($monto > $saldo + 0.009) {
                throw new \Exception('El abono ($' . number_format($monto, 2) . ') excede el saldo pendiente ($' . number_format($saldo, 2) . ').');
            }

            $abono = AbonoVenta::create([
                'venta_id'      => $venta->id,
                'user_id'       => auth()->id(),
                'sucursal_id'   => $venta->sucursal_id,
                'corte_caja_id' => $corte->id,
                'monto'         => $monto,
                'metodo'        => $request->metodo,
                'pagos_mixtos'  => $request->metodo === 'mixto' ? $pagos : null,
                'referencia'    => $request->referencia,
                'observaciones' => $request->observaciones,
            ]);

            // Queda en el corte como ingreso (el efectivo suma al "Efectivo Esperado")
            // Un movimiento por cada método realmente cobrado (así el corte separa efectivo / transf. / tarjeta)
            foreach ($pagos as $p) {
                MovimientoCaja::create([
                    'corte_caja_id' => $corte->id,
                    'tipo'          => 'ingreso',
                    'concepto'      => 'Abono crédito ' . $venta->folio . ($request->metodo === 'mixto' ? ' (mixto)' : ''),
                    'monto'         => $p['monto'],
                    'metodo'        => $p['metodo'],
                ]);
            }

            DB::commit();

            $restante = max(0, round($saldo - $monto, 2));
            return back()
                ->with('success', 'Abono de $' . number_format($monto, 2) . ' registrado en ' . $venta->folio
                    . ($restante <= 0.009 ? '. ¡Crédito liquidado!' : '. Saldo restante: $' . number_format($restante, 2)))
                ->with('abono_id', $abono->id);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Abono de crédito falló: ' . $e->getMessage(), ['venta_id' => $venta->id ?? null, 'user_id' => auth()->id()]);
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function ticket(AbonoVenta $abono)
    {
        $abono->load('usuario');
        $venta = Venta::with(['cliente', 'sucursal'])->findOrFail($abono->venta_id);
        abort_unless($this->puedeAccederVenta($venta), 403);

        $abonadoHastaAqui = round((float) AbonoVenta::where('venta_id', $venta->id)->where('id', '<=', $abono->id)->sum('monto'), 2);
        $saldoRestante    = max(0, round((float) $venta->total - $abonadoHastaAqui, 2));
        $vence            = $venta->created_at->copy()->addDays((int) $venta->dias_credito);

        return view(self::VISTA . '.ticket_abono', compact('abono', 'venta', 'abonadoHastaAqui', 'saldoRestante', 'vence'));
    }

    /**
     * Resumen de la cartera para el dashboard (respeta la sucursal activa).
     */
    public static function resumenCartera(): array
    {
        $global = auth()->user()->isAdmin() && session('activo_sucursal_id') === 'global';

        $ventas = Venta::where('metodo_pago', 'credito')
            ->where('estado', 'completada')
            ->when(!$global, fn ($q) => $q->where('sucursal_id', session('activo_sucursal_id')))
            ->get(['id', 'total', 'dias_credito', 'created_at']);

        $abonos = AbonoVenta::whereIn('venta_id', $ventas->pluck('id'))
            ->selectRaw('venta_id, SUM(monto) as abonado')
            ->groupBy('venta_id')
            ->pluck('abonado', 'venta_id');

        $hoy = now()->startOfDay();
        $r = ['por_cobrar' => 0.0, 'vencido' => 0.0, 'n_abiertos' => 0, 'n_vencidos' => 0];

        foreach ($ventas as $v) {
            $saldo = round((float) $v->total - (float) ($abonos[$v->id] ?? 0), 2);
            if ($saldo <= 0.009) continue;

            $r['por_cobrar'] += $saldo;
            $r['n_abiertos']++;

            if ($v->created_at->copy()->addDays((int) $v->dias_credito)->startOfDay()->lt($hoy)) {
                $r['vencido'] += $saldo;
                $r['n_vencidos']++;
            }
        }

        return $r;
    }
}