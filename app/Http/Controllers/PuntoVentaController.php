<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PuntoVentaController extends Controller
{
    public function index()
    {
        $sucursalId = session('activo_sucursal_id');
        
        $corteActivo = CorteCaja::where('estado', 'abierto')
            ->where('user_id', auth()->id())
            ->with('movimientos')
            ->first();

        // 🔥 FILTRO APLICADO: Solo productos para VENTA o AMBAS
        $productosQuery = Equipo::where('activo', true)
            ->whereIn('tipo_operacion', ['venta', 'ambas'])
            ->with(['categoria', 'unidadMedida']);
        
        // 🔒 Filtrar que existan físicamente en la sucursal y con stock
        if ($sucursalId !== 'global') {
            $productosQuery->whereHas('sucursales', function($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)
                  ->where('stock', '>', 0);
            });
        } else {
            $productosQuery->where('stock', '>', 0);
        }
        
        $productos = $productosQuery->get();
        
        // 🔒 Sobrescribir el atributo 'stock' para que JavaScript no se confunda
        if ($sucursalId !== 'global') {
            foreach ($productos as $producto) {
                $producto->stock_global = $producto->stock; // Guardamos el global por precaución
                $producto->stock = $producto->getStockEnSucursal($sucursalId); // El local será el principal
            }
        }

        $clientes = Cliente::orderBy('nombre_completo')->get();

        return view('puntoventa.index', compact('productos', 'clientes', 'corteActivo'));
    }

    public function buscarProductos(Request $request)
    {
        $sucursalId = session('activo_sucursal_id');
        
        // 🔥 FILTRO APLICADO: Solo productos para VENTA o AMBAS
        $query = Equipo::where('activo', true)
            ->whereIn('tipo_operacion', ['venta', 'ambas']);

        // 🔒 Filtrar por sucursal
        if ($sucursalId !== 'global') {
            $query->whereHas('sucursales', function($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)
                  ->where('stock', '>', 0);
            });
        } else {
            $query->where('stock', '>', 0);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        if ($request->has('categoria') && $request->categoria) {
            $query->whereHas('categoria', function($q) use ($request) {
                $q->where('nombre', $request->categoria);
            });
        }

        $productos = $query->with(['categoria', 'unidadMedida'])->get();
        
        // 🔒 Sobrescribir stock para JSON
        if ($sucursalId !== 'global') {
            foreach ($productos as $producto) {
                $producto->stock_global = $producto->stock;
                $producto->stock = $producto->getStockEnSucursal($sucursalId);
            }
        }

        return response()->json($productos);
    }

    public function store(Request $request)
    {
        $sucursalId = session('activo_sucursal_id');
        
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:equipos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'metodo_pago' => 'required|in:efectivo,transferencia,tarjeta,mixto',
            'cliente_id' => 'nullable|exists:clientes,id',
            'cliente_nombre' => 'nullable|string|max:255',
            'requiere_factura' => 'nullable', 
            'rfc_cliente' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $corteActivo = CorteCaja::where('estado', 'abierto')
                ->where('user_id', auth()->id())
                ->first();

            if (!$corteActivo) {
                throw new \Exception('No hay caja abierta. Debes abrir caja antes de realizar ventas.');
            }

            $subtotal = 0;
            $items = [];

            foreach ($request->items as $item) {
                $equipo = Equipo::find($item['id']);

                // 🔒 Verificar stock en la sucursal correcta
                if ($sucursalId !== 'global') {
                    $stockDisponible = $equipo->getStockEnSucursal($sucursalId);
                    if ($stockDisponible < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente en esta sucursal para {$equipo->nombre}. Disponible: {$stockDisponible}");
                    }
                    // Descontar del stock de la sucursal
                    $equipo->actualizarStockEnSucursal($sucursalId, $item['cantidad'], 'restar');
                } else {
                    if ($equipo->stock < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente para {$equipo->nombre}");
                    }
                    $equipo->stock -= $item['cantidad'];
                    $equipo->save();
                }

                $precio = $equipo->precio_venta ?? $equipo->precio_dia;
                $subtotalItem = $precio * $item['cantidad'];
                $subtotal += $subtotalItem;

                $items[] = [
                    'equipo_id' => $equipo->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotalItem
                ];
            }

            $requiereFactura = filter_var($request->requiere_factura, FILTER_VALIDATE_BOOLEAN);
            $iva = $requiereFactura ? $subtotal * 0.16 : 0;
            $total = $subtotal + $iva;

            $venta = Venta::create([
                'folio' => Venta::generarFolio(),
                'corte_caja_id' => $corteActivo->id,
                'sucursal_id' => $sucursalId !== 'global' ? $sucursalId : null,
                'cliente_id' => $request->cliente_id,
                'cliente_nombre' => $request->cliente_nombre ?? 'Cliente general',
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
                'metodo_pago' => $request->metodo_pago,
                'observaciones' => $request->observaciones ?? null,
                'estado' => 'completada',
                'requiere_factura' => $requiereFactura,
                'rfc_cliente' => $request->rfc_cliente ?? null,
            ]);

            foreach ($items as $item) {
                $item['venta_id'] = $venta->id;
                DetalleVenta::create($item);
            }

            $corteActivo->total_ventas += $total;
            
            if ($request->metodo_pago == 'efectivo') {
                $corteActivo->total_efectivo += $total;
            } elseif ($request->metodo_pago == 'transferencia') {
                $corteActivo->total_transferencias += $total;
            } elseif ($request->metodo_pago == 'tarjeta') {
                $corteActivo->total_tarjetas += $total;
            }
            $corteActivo->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta realizada exitosamente',
                'venta_id' => $venta->id,
                'total' => (float) $venta->total
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function ticket(Venta $venta)
    {
        $venta->load('detalles.equipo', 'cliente');
        return view('puntoventa.ticket', compact('venta'));
    }

    public function historial(Request $request)
    {
        $sucursalId = session('activo_sucursal_id');
        
        $fechaFiltro = $request->get('fecha', date('Y-m-d'));

        $ventasQuery = Venta::with(['cliente', 'detalles.equipo'])
            ->whereDate('created_at', $fechaFiltro);
        
        // 🔒 Filtrar por sucursal si no es admin global
        if ($sucursalId !== 'global') {
            $ventasQuery->where('sucursal_id', $sucursalId);
        }
        
        $ventas = $ventasQuery->latest()->get();

        return view('puntoventa.historial', compact('ventas', 'fechaFiltro'));
    }

    public function cancelar($id)
    {
        $sucursalId = session('activo_sucursal_id');
        
        try {
            DB::beginTransaction();

            $venta = Venta::with('detalles')->findOrFail($id);

            if ($venta->estado === 'cancelada') {
                return back()->with('error', 'Esta transacción comercial ya fue cancelada con anterioridad.');
            }

            foreach ($venta->detalles as $detalle) {
                $producto = Equipo::find($detalle->equipo_id);
                if ($producto) {
                    // 🔒 Restaurar stock en la sucursal correspondiente
                    if ($sucursalId !== 'global' && $venta->sucursal_id) {
                        $producto->actualizarStockEnSucursal($venta->sucursal_id, $detalle->cantidad, 'sumar');
                    } else {
                        $producto->increment('stock', $detalle->cantidad);
                    }
                }
            }

            $corteActivo = CorteCaja::where('estado', 'abierto')
                ->where('user_id', auth()->id())
                ->first();

            if ($corteActivo && $venta->corte_caja_id === $corteActivo->id) {
                $corteActivo->decrement('total_ventas', $venta->total);

                if ($venta->metodo_pago === 'efectivo') {
                    $corteActivo->decrement('total_efectivo', $venta->total);
                } elseif ($venta->metodo_pago === 'transferencia') {
                    $corteActivo->decrement('total_transferencias', $venta->total);
                } elseif ($venta->metodo_pago === 'tarjeta') {
                    $corteActivo->decrement('total_tarjetas', $venta->total);
                }
                $corteActivo->save();
            }

            $venta->update(['estado' => 'cancelada']);

            DB::commit();
            return back()->with('success', "Venta con Folio {$venta->folio} revocada con éxito. El inventario fue restaurado.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error técnico al procesar cancelación: ' . $e->getMessage());
        }
    }

    public function cortes()
    {
        $cortes = CorteCaja::with('user')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('puntoventa.cortes', compact('cortes'));
    }

    public function abrirCaja(Request $request)
    {
        $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
            'turno' => 'required|in:mañana,tarde,noche'
        ]);

        $corteActivo = CorteCaja::where('estado', 'abierto')
            ->where('user_id', auth()->id())
            ->first();

        if ($corteActivo) {
            return back()->with('error', 'Ya tienes una caja abierta');
        }

        CorteCaja::create([
            'user_id' => auth()->id(),
            'sucursal_id' => session('activo_sucursal_id') !== 'global' ? session('activo_sucursal_id') : null, // 🔒 Asignar sucursal
            'turno' => $request->turno,
            'fecha_apertura' => now(),
            'monto_inicial' => $request->monto_inicial,
            'estado' => 'abierto'
        ]);

        return back()->with('success', 'Caja abierta exitosamente en el turno de la ' . $request->turno);
    }

    public function cerrarCaja(Request $request)
    {
        $corte = CorteCaja::where('estado', 'abierto')
            ->where('user_id', auth()->id())
            ->with('movimientos')
            ->first();

        if (!$corte) {
            return back()->with('error', 'No hay caja abierta');
        }

        $request->validate([
            'monto_final' => 'required|numeric|min:0'
        ]);

        $ingresosEfectivo = $corte->movimientos->where('tipo', 'ingreso')->where('metodo', 'efectivo')->sum('monto');
        $egresosEfectivo = $corte->movimientos->where('tipo', 'egreso')->where('metodo', 'efectivo')->sum('monto');
        
        $efectivoEsperado = $corte->monto_inicial + $corte->total_efectivo + $ingresosEfectivo - $egresosEfectivo;
        $diferencia = $request->monto_final - $efectivoEsperado;

        $corte->update([
            'fecha_cierre' => now(),
            'monto_final' => $request->monto_final,
            'diferencia' => $diferencia,
            'estado' => 'cerrado'
        ]);

        return back()->with('success', 'Caja cerrada exitosamente. Diferencia: $' . number_format($diferencia, 2));
    }

    public function getEstadoCaja()
    {
        $corteActivo = CorteCaja::where('estado', 'abierto')
            ->where('user_id', auth()->id())
            ->first();

        if (!$corteActivo) {
            return response()->json([
                'abierta' => false,
                'message' => 'No hay caja abierta'
            ]);
        }

        return response()->json([
            'abierta' => true,
            'corte' => $corteActivo,
            'total_ventas' => $corteActivo->total_ventas,
            'total_efectivo' => $corteActivo->total_efectivo,
            'total_transferencias' => $corteActivo->total_transferencias,
            'total_tarjetas' => $corteActivo->total_tarjetas,
        ]);
    }

    public function movimiento(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:ingreso,egreso',
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'metodo' => 'required|in:efectivo,transferencia,tarjeta',
        ]);

        $corteActivo = CorteCaja::where('estado', 'abierto')
            ->where('user_id', auth()->id())
            ->first();

        if (!$corteActivo) {
            return back()->with('error', 'No hay caja abierta');
        }

        MovimientoCaja::create([
            'corte_caja_id' => $corteActivo->id,
            'tipo' => $request->tipo,
            'concepto' => $request->concepto,
            'monto' => $request->monto,
            'metodo' => $request->metodo,
        ]);

        return back()->with('success', 'Movimiento registrado exitosamente');
    }

    public function reportes()
    {
        $sucursalId = session('activo_sucursal_id');
        
        $topQuery = DB::table('detalle_ventas')
            ->join('equipos', 'detalle_ventas.equipo_id', '=', 'equipos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id');
        
        if ($sucursalId !== 'global') {
            $topQuery->where('ventas.sucursal_id', $sucursalId);
        }
        
        $topProductosData = $topQuery
            ->select('equipos.nombre', DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'))
            ->groupBy('equipos.id', 'equipos.nombre')
            ->orderBy('total_vendido', 'desc')
            ->take(6)
            ->get();

        $topProductosNombres = $topProductosData->pluck('nombre')->toArray();
        $topProductosCantidades = $topProductosData->pluck('total_vendido')->toArray();

        // Ventas del día por hora
        $ventasHoyQuery = Venta::where('estado', 'completada')->whereDate('created_at', now());
        if ($sucursalId !== 'global') {
            $ventasHoyQuery->where('sucursal_id', $sucursalId);
        }
        
        $ventasHoyData = $ventasHoyQuery
            ->select(DB::raw('HOUR(created_at) as hora'), DB::raw('SUM(total) as total_monto'))
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hora')
            ->get();

        $horasDia = [];
        $montosDia = [];
        for ($i = 7; $i <= 20; $i++) {
            $horasDia[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $registro = $ventasHoyData->firstWhere('hora', $i);
            $montosDia[] = $registro ? (float)$registro->total_monto : 0;
        }

        // Ventas del año por mes
        $ventasMesQuery = Venta::where('estado', 'completada')->whereYear('created_at', date('Y'));
        if ($sucursalId !== 'global') {
            $ventasMesQuery->where('sucursal_id', $sucursalId);
        }
        
        $ventasMesData = $ventasMesQuery
            ->select(DB::raw('MONTH(created_at) as mes'), DB::raw('SUM(total) as total_monto'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('mes')
            ->get();

        $mesesNombres = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $montosMes = [];
        for ($m = 1; $m <= 12; $m++) {
            $registro = $ventasMesData->firstWhere('mes', $m);
            $montosMes[] = $registro ? (float)$registro->total_monto : 0;
        }

        return view('puntoventa.reportes', compact(
            'topProductosNombres', 
            'topProductosCantidades', 
            'horasDia', 
            'montosDia', 
            'mesesNombres', 
            'montosMes'
        ));
    }

    public function generarReporte(Request $request)
    {
        $sucursalId = session('activo_sucursal_id');
        
        $request->validate([
            'tipo' => 'required|in:personalizado,diario,semanal,mes,anual',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $inicio = \Carbon\Carbon::parse($request->fecha_inicio)->startOfDay();
        $fin = \Carbon\Carbon::parse($request->fecha_fin)->endOfDay();

        $ventasQuery = Venta::with(['detalles.equipo', 'cliente'])
            ->where('estado', 'completada')
            ->whereBetween('created_at', [$inicio, $fin]);
        
        if ($sucursalId !== 'global') {
            $ventasQuery->where('sucursal_id', $sucursalId);
        }
        
        $ventas = $ventasQuery->get();

        $totalVentas = $ventas->sum('total');
        $totalItems = $ventas->sum(function($v) { return $v->detalles->sum('cantidad'); });
        $promedioVenta = $ventas->count() > 0 ? $totalVentas / $ventas->count() : 0;

        $totalCostos = 0;
        foreach ($ventas as $venta) {
            foreach ($venta->detalles as $detalle) {
                $precioCompra = $detalle->equipo->precio_compra ?? ($detalle->precio_unitario * 0.60);
                $totalCostos += $precioCompra * $detalle->cantidad;
            }
        }
        $gananciaNeta = $totalVentas - $totalCostos;

        $pagosPorMetodo = [
            'efectivo' => $ventas->where('metodo_pago', 'efectivo')->sum('total'),
            'transferencia' => $ventas->where('metodo_pago', 'transferencia')->sum('total'),
            'tarjeta' => $ventas->where('metodo_pago', 'tarjeta')->sum('total'),
            'mixto' => $ventas->where('metodo_pago', 'mixto')->sum('total'),
        ];

        $topProductos = [];
        foreach ($ventas as $venta) {
            foreach ($venta->detalles as $detalle) {
                $nombre = $detalle->equipo->nombre;
                if (!isset($topProductos[$nombre])) {
                    $topProductos[$nombre] = 0;
                }
                $topProductos[$nombre] += $detalle->cantidad;
            }
        }
        arsort($topProductos);
        $topProductos = array_slice($topProductos, 0, 7);

        $sucursalNombre = session('activo_sucursal_nombre', 'Todas las sucursales');
        $titulo = "Informe Ejecutivo - {$sucursalNombre} (" . $inicio->format('d/m/Y') . " - " . $fin->format('d/m/Y') . ")";

        $pdf = Pdf::loadView('puntoventa.reporte_pdf', compact(
            'ventas', 'titulo', 'totalVentas', 'totalItems', 'totalCostos', 'gananciaNeta',
            'promedioVenta', 'pagosPorMetodo', 'topProductos', 'inicio', 'fin'
        ));
        
        return $pdf->download('Balance_Financiero_' . $inicio->format('Ymd') . '_' . $fin->format('Ymd') . '.pdf');
    }
}