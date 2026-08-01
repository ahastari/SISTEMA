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
            'metodo_pago' => 'required|in:efectivo,transferencia,tarjeta,mixto',
            'monto_recibido' => 'nullable|numeric|min:0',
            'pagos_mixtos' => 'nullable|array',
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
                $esEspecial = isset($item['esEspecial']) && $item['esEspecial'];

                if (!$esEspecial) {
                    $equipo = Equipo::find($item['id']);
                    if (!$equipo) throw new \Exception("Producto no encontrado.");

                    if ($sucursalId !== 'global') {
                        $stockDisponible = $equipo->getStockEnSucursal($sucursalId);
                        if ($stockDisponible < $item['cantidad']) {
                            throw new \Exception("Stock insuficiente para {$equipo->nombre}. Disponible: {$stockDisponible}");
                        }
                        $equipo->actualizarStockEnSucursal($sucursalId, $item['cantidad'], 'restar');
                    } else {
                        if ($equipo->stock < $item['cantidad']) {
                            throw new \Exception("Stock insuficiente para {$equipo->nombre}");
                        }
                        $equipo->stock -= $item['cantidad'];
                        $equipo->save();
                    }

                    $precio = $equipo->precio_venta ?? $equipo->precio_dia;
                    $costoAdquisicion = $equipo->costo ?? 0;
                    $equipoId = $equipo->id;
                } else {
                    // Ítems especiales (Flete / Mano de obra)
                    $precio = (float) $item['precio'];
                    $equipoId = null; // No apunta a equipo en BD
                }

                $subtotalItem = $precio * $item['cantidad'];
                $subtotal += $subtotalItem;

                $items[] = [
                    'equipo_id' => $equipoId,
                    'concepto_especial' => $esEspecial ? $item['nombre'] : null, // Por si manejas guardado de nombres personalizados
                    'cantidad' => $item['cantidad'],
                    'costo' => $costoAdquisicion,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotalItem
                ];
            }

            $requiereFactura = filter_var($request->requiere_factura, FILTER_VALIDATE_BOOLEAN);
            $iva = $requiereFactura ? $subtotal * 0.16 : 0;
            $total = $subtotal + $iva;

            $montoRecibido = $request->monto_recibido > 0 ? $request->monto_recibido : $total;
            $cambio = $montoRecibido - $total;

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
                'monto_recibido' => $montoRecibido,
                'cambio' => $cambio,
                'pagos_mixtos' => $request->metodo_pago === 'mixto' ? $request->pagos_mixtos : null,
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

            if ($request->metodo_pago === 'mixto' && is_array($request->pagos_mixtos)) {
                foreach ($request->pagos_mixtos as $pago) {
                    if ($pago['metodo'] === 'efectivo') $corteActivo->total_efectivo += $pago['monto'];
                    elseif ($pago['metodo'] === 'transferencia') $corteActivo->total_transferencias += $pago['monto'];
                    elseif ($pago['metodo'] === 'tarjeta') $corteActivo->total_tarjetas += $pago['monto'];
                }
            } else {
                if ($request->metodo_pago == 'efectivo') $corteActivo->total_efectivo += $total;
                elseif ($request->metodo_pago == 'transferencia') $corteActivo->total_transferencias += $total;
                elseif ($request->metodo_pago == 'tarjeta') $corteActivo->total_tarjetas += $total;
            }

            $corteActivo->save();

            // 🔥 RECALCULAR MONTOS DEL MODAL DE CIERRE EN TIEMPO REAL
            $montoFleteModal = 0;
            $montoManoObraModal = 0;

            // Recargar ventas, detalles y movimientos actualizados
            $corteActivo->load('ventas.detalles', 'movimientos');

            foreach ($corteActivo->ventas as $v) {
                foreach ($v->detalles as $d) {
                    if (str_contains(strtolower($d->concepto_especial ?? ''), 'flete')) {
                        $montoFleteModal += $d->subtotal;
                    } elseif (str_contains(strtolower($d->concepto_especial ?? ''), 'mano de obra')) {
                        $montoManoObraModal += $d->subtotal;
                    }
                }
            }

            $ingresosEfe = $corteActivo->movimientos->where('tipo', 'ingreso')->where('metodo', 'efectivo')->sum('monto');
            $egresosEfe = $corteActivo->movimientos->where('tipo', 'egreso')->where('metodo', 'efectivo')->sum('monto');
            $efeEsperado = $corteActivo->monto_inicial + $corteActivo->total_efectivo + $ingresosEfe - $egresosEfe;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta realizada exitosamente',
                'venta_id' => $venta->id,
                'total' => (float) $venta->total,
                // 🚀 ESTE OBJETO ALIMENTA AL MODAL DE CIERRE VÍA JS SIN RECARGAR
                'modal_data' => [
                    'total_ventas' => number_format($corteActivo->total_ventas, 2),
                    'total_efectivo' => number_format($corteActivo->total_efectivo, 2),
                    'total_transferencias' => number_format($corteActivo->total_transferencias, 2),
                    'total_tarjetas' => number_format($corteActivo->total_tarjetas, 2),
                    'total_flete' => number_format($montoFleteModal, 2),
                    'total_mano_obra' => number_format($montoManoObraModal, 2),
                    'efectivo_esperado' => number_format($efeEsperado, 2),
                ]
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
        // 👈 Modificar para incluir 'sucursal'
        $venta->load(['detalles.equipo', 'cliente', 'sucursal']); 
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

            $venta = Venta::with(['detalles.equipo', 'cliente', 'sucursal'])->findOrFail($id);

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
        $cortes = CorteCaja::with(['user', 'movimientos', 'ventas.detalles']) // 👈 Carga profunda de ventas y sus detalles
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

    public function reportes(Request $request)
    {
        $sucursalId = session('activo_sucursal_id');
        $user = auth()->user();
        $isGlobalAdmin = $user->isAdmin() && $sucursalId === 'global';

        // Capturar rango de fechas (Por defecto: mes actual)
        $inicio = \Carbon\Carbon::parse($request->get('fecha_inicio', date('Y-m-01')))->startOfDay();
        $fin = \Carbon\Carbon::parse($request->get('fecha_fin', date('Y-m-d')))->endOfDay();

        // 1. TOP PRODUCTOS EN RANGO
        $topQuery = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->leftJoin('equipos', 'detalle_ventas.equipo_id', '=', 'equipos.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.created_at', [$inicio, $fin]);

        // 🔒 FILTRO SEGÚN ROL Y SUCURSAL
        if (!$isGlobalAdmin) {
            // Gerente/Cajero: SOLO lo de su sucursal asignada
            $topQuery->where('ventas.sucursal_id', $sucursalId);
        }

        $topProductosData = $topQuery
            ->select(
                DB::raw('COALESCE(equipos.nombre, detalle_ventas.concepto_especial, "Servicio") as nombre_item'), 
                DB::raw('SUM(detalle_ventas.cantidad) as total_vendido')
            )
            ->groupBy('nombre_item')
            ->orderBy('total_vendido', 'desc')
            ->take(6)
            ->get();

        $topProductosNombres = $topProductosData->pluck('nombre_item')->toArray();
        $topProductosCantidades = $topProductosData->pluck('total_vendido')->toArray();

        if (empty($topProductosNombres)) {
            $topProductosNombres = ['Sin registros en rango'];
            $topProductosCantidades = [0];
        }

        // 2. VENTAS POR FECHA EN RANGO (FLUX DIARIO)
        $ventasPeriodoQuery = Venta::where('estado', 'completada')
            ->whereBetween('created_at', [$inicio, $fin]);

        if (!$isGlobalAdmin) {
            $ventasPeriodoQuery->where('sucursal_id', $sucursalId);
        }

        $ventasAgrupadas = $ventasPeriodoQuery
            ->select(DB::raw('DATE(created_at) as fecha'), DB::raw('SUM(total) as total_monto'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();

        $horasDia = [];
        $montosDia = [];

        foreach ($ventasAgrupadas as $reg) {
            $horasDia[] = \Carbon\Carbon::parse($reg->fecha)->format('d/m/Y');
            $montosDia[] = (float) $reg->total_monto;
        }

        if (empty($horasDia)) {
            $horasDia = [$inicio->format('d/m/Y')];
            $montosDia = [0];
        }

        // 3. HISTÓRICO ANUAL POR MES
        $ventasMesQuery = Venta::where('estado', 'completada')->whereYear('created_at', date('Y'));
        
        if (!$isGlobalAdmin) {
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
        $user = auth()->user();
        $isGlobalAdmin = $user->isAdmin() && $sucursalId === 'global';

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
        
        if (!$isGlobalAdmin) {
            // Gerente/Cajero: Filtro estricto por su sucursal
            $ventasQuery->where('sucursal_id', $sucursalId);
        }
        
        $ventas = $ventasQuery->get();
        $totalVentas = $ventas->sum('total');

        // Top Productos Físicos
        $topProductos = [];
        foreach ($ventas as $venta) {
            foreach ($venta->detalles as $detalle) {
                if (!$detalle->concepto_especial && $detalle->equipo) {
                    $nombre = $detalle->equipo->nombre;
                    $topProductos[$nombre] = ($topProductos[$nombre] ?? 0) + $detalle->cantidad;
                }
            }
        }
        arsort($topProductos);
        $topProductos = array_slice($topProductos, 0, 5);

        $pagosPorMetodo = [
            'efectivo'      => $ventas->where('metodo_pago', 'efectivo')->sum('total'),
            'transferencia' => $ventas->where('metodo_pago', 'transferencia')->sum('total'),
            'tarjeta'       => $ventas->where('metodo_pago', 'tarjeta')->sum('total'),
            'mixto'         => $ventas->where('metodo_pago', 'mixto')->sum('total'),
        ];

        $sucursalObj = null;
        $logoBase64 = null;
        $sucursalNombre = session('activo_sucursal_nombre', 'Consola / Matriz General');

        if ($sucursalId && $sucursalId !== 'global') {
            $sucursalObj = \App\Models\Sucursal::find($sucursalId);
            
            if ($sucursalObj) {
                $sucursalNombre = $sucursalObj->nombre;
                
                if ($sucursalObj->logo && file_exists(public_path('storage/' . $sucursalObj->logo))) {
                    $path = public_path('storage/' . $sucursalObj->logo);
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
        }

        $titulo = "Informe Financiero de Auditoría";

        $pdf = Pdf::loadView('puntoventa.reporte_pdf', compact(
            'ventas', 'titulo', 'totalVentas', 'pagosPorMetodo', 'topProductos', 
            'inicio', 'fin', 'sucursalNombre', 'sucursalObj', 'logoBase64'
        ));
        
        return $pdf->download('Balance_Financiero_' . $inicio->format('Ymd') . '_' . $fin->format('Ymd') . '.pdf');
    }
}