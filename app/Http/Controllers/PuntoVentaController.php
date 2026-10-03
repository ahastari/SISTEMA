<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\CorteCaja;
use App\Models\MovimientoCaja;
use App\Models\Cliente;
use App\Models\SolicitudDescuento;
use App\Models\Configuracion;
use App\Models\Sucursal;
use App\Models\AbonoVenta;
use App\Models\Renta;
use App\Models\Pago;
use App\Models\User;
use App\Support\XlsxSimple;
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

        $productosQuery = Equipo::where('activo', true)
            ->whereIn('tipo_operacion', ['venta', 'ambas'])
            ->with(['categoria', 'unidadMedida']);
        
        if ($sucursalId !== 'global') {
            $productosQuery->whereHas('sucursales', function($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)
                  ->where('stock', '>', 0);
            });
        } else {
            $productosQuery->where('stock', '>', 0);
        }
        
        $productos = $productosQuery->get();
        
        if ($sucursalId !== 'global') {
            foreach ($productos as $producto) {
                $producto->stock_global = $producto->stock; // Guardamos el global por precaución
                $producto->stock = $producto->getStockEnSucursal($sucursalId); // El local será el principal
            }
        }

        $isGlobalAdmin = auth()->user()->isAdmin() && $sucursalId === 'global';
        
        $clientesQuery = Cliente::where('bloqueado', false)->where('activo', true);
        
        if (!$isGlobalAdmin) {
            $clientesQuery->where('sucursal_id', $sucursalId);
        }
        
        $clientes = $clientesQuery->orderBy('nombre_completo')->get();

        // Admin/Gerente pueden dar descuentos sin pedir autorización a nadie más
        $puedeAutorizarDescuento = auth()->user()->isAdmin() || auth()->user()->isGerente();

        // Límite de crédito sin autorización (null = sin límite)
        $limiteCredito = $this->limiteCredito($sucursalId);

        return view('puntoventa.index', compact('productos', 'clientes', 'corteActivo', 'puedeAutorizarDescuento', 'limiteCredito'));
    }

    // ==================== SOLICITUDES DE AUTORIZACIÓN DE DESCUENTO ====================

    /**
     * El cajero envía la solicitud de descuento al gerente (mismo esquema que la cancelación de venta).
     */
    public function solicitarDescuento(Request $request)
    {
        $request->validate([
            'monto'    => 'required|numeric|min:0.01',
            'subtotal' => 'required|numeric|min:0.01',
            'motivo'   => 'required|string|max:255',
            'cliente_id'  => 'nullable|integer',
            'reemplaza_id' => 'nullable|integer',
            'tipo'        => 'nullable|in:descuento,credito',
            'origen'      => 'nullable|in:renta',
            'dias_credito' => 'required_if:tipo,credito|nullable|integer|min:1|max:365',
            'abono_metodo'                  => 'nullable|in:efectivo,transferencia,tarjeta,mixto',
            'abono_pagos_mixtos'            => 'nullable|array|size:2',
            'abono_pagos_mixtos.*.metodo'   => 'required_with:abono_pagos_mixtos|in:efectivo,transferencia,tarjeta',
            'abono_pagos_mixtos.*.monto'    => 'required_with:abono_pagos_mixtos|numeric|min:0.01',
            'abono_referencia'              => 'nullable|string|max:100',
        ]);

        $monto = round((float) $request->monto, 2);

        if ($monto > round((float) $request->subtotal, 2)) {
            return response()->json(['success' => false, 'message' => 'El descuento no puede ser mayor al subtotal.'], 422);
        }

        $sucursalId = session('activo_sucursal_id');
        // Tipo de solicitud: 'descuento' (default) o 'credito' (total a crédito que excede el límite)
        $tipo = $request->input('tipo', 'descuento');
        $limiteCredito = null;
        if ($tipo === 'credito') {
            $limiteCredito = $this->limiteCredito($sucursalId);
            if ($limiteCredito === null || $monto <= $limiteCredito) {
                return response()->json(['success' => false, 'message' => 'Este crédito no requiere autorización (está dentro del límite).'], 422);
            }
        }

        $corteActivo = CorteCaja::where('estado', 'abierto')->where('user_id', auth()->id())->first();

        // Ya NO se cancelan todas las solicitudes del cajero: puede tener varias en paralelo
        // (una por carrito en espera). Solo se anula la que este carrito reemplaza (cambió el monto).
        if ($request->filled('reemplaza_id')) {
            SolicitudDescuento::where('id', $request->reemplaza_id)
                ->where('user_id', auth()->id())
                ->whereIn('estado', ['pendiente', 'aprobada'])
                ->update(['estado' => 'cancelada']);
        }

        // Cliente al que se le hace el descuento (se resuelve en servidor)
        $cliente = $request->filled('cliente_id') ? Cliente::find($request->cliente_id) : null;

        // forceFill: guarda cliente_id/cliente_nombre aunque no estén en $fillable del modelo
        $solicitud = (new SolicitudDescuento)->forceFill([
            'user_id'        => auth()->id(),
            'cliente_id'     => $cliente?->id,
            'cliente_nombre' => $cliente?->nombre_completo ?? 'Público General',
            'tipo'           => $tipo,
            'origen'         => $tipo === 'descuento' ? $request->input('origen') : null,
            'limite_credito' => $limiteCredito,
            'dias_credito'   => $tipo === 'credito' ? (int) $request->input('dias_credito') : null,
            'sucursal_id'   => $sucursalId !== 'global' ? $sucursalId : null,
            'corte_caja_id' => $corteActivo?->id,
            'monto'         => $monto,
            'subtotal'      => round((float) $request->subtotal, 2),
            'motivo'        => $request->motivo,
            'estado'        => 'pendiente',
        ]);
        $solicitud->save();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud enviada al gerente.',
            'id'      => $solicitud->id,
            'estado'  => $solicitud->estado,
        ]);
    }

    /**
     * El POS del cajero consulta si el gerente ya resolvió su solicitud.
     */
    public function estadoDescuento(SolicitudDescuento $solicitud)
    {
        abort_if($solicitud->user_id !== auth()->id(), 403);

        return response()->json([
            'estado'      => $solicitud->estado,
            'monto'       => (float) $solicitud->monto,
            'autorizador' => $solicitud->autorizador?->name,
            'dias'        => $solicitud->dias_credito,
            'sucursal'    => Sucursal::where('id', $solicitud->sucursal_id)->value('nombre'),
        ]);
    }

    /**
     * Límite de crédito sin autorización de la SUCURSAL indicada. null = sin límite.
     */
    private function limiteCredito($sucursalId = null): ?float
    {
        if (!$sucursalId || $sucursalId === 'global') {
            return null;
        }
        $valor = Sucursal::where('id', $sucursalId)->value('credito_limite_autorizacion');
        return ($valor === null || $valor === '') ? null : (float) $valor;
    }

    /**
     * Avisos al cajero sobre sus solicitudes de CANCELACIÓN de venta.
     * GET /puntoventa/cancelaciones/estado?ids=1,2  -> todas sus pendientes + las ids consultadas
     */
    public function estadoCancelaciones(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($i) => (int) $i)->filter()->unique()->take(50)->values();

        $ventas = Venta::with('autorizadoPor')
            ->where('solicitado_por_id', auth()->id())
            ->where(function ($q) use ($ids) {
                $q->where('autorizacion_solicitada', true);
                if ($ids->isNotEmpty()) {
                    $q->orWhereIn('id', $ids);
                }
            })
            ->get()
            ->map(fn ($v) => [
                'id'          => $v->id,
                'folio'       => $v->folio,
                'cliente'     => $v->cliente_nombre ?: 'Público General',
                'total'       => (float) $v->total,
                'estado'      => $v->autorizacion_solicitada
                                    ? 'pendiente'
                                    : ($v->estado === 'cancelada' ? 'aprobada' : 'rechazada'),
                'autorizador' => $v->autorizadoPor?->name,
            ])->values();

        return response()->json(['ventas' => $ventas]);
    }

    /**
     * Estado de varias solicitudes a la vez (polling único para el carrito actual y los carritos en espera).
     * GET /puntoventa/descuento/estados?ids=1,2,3
     */
    public function estadosDescuentos(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($i) => (int) $i)->filter()->unique()->take(50)->values();

        $solicitudes = SolicitudDescuento::with('autorizador')
            ->where('user_id', auth()->id())
            ->whereIn('id', $ids)
            ->get();

        $nombresSucursal = Sucursal::whereIn('id', $solicitudes->pluck('sucursal_id')->filter()->unique())->pluck('nombre', 'id');

        return response()->json((object) $solicitudes->mapWithKeys(fn ($s) => [
            $s->id => [
                'estado'      => $s->estado,
                'monto'       => (float) $s->monto,
                'autorizador' => $s->autorizador?->name,
                'dias'        => $s->dias_credito,
                'sucursal'    => $nombresSucursal[$s->sucursal_id] ?? null,
            ],
        ])->all());
    }

    /**
     * Bandeja del gerente/admin con las solicitudes de descuento pendientes.
     */
    public function descuentosPendientes()
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isGerente(), 403);

        $sucursalId = session('activo_sucursal_id');

        $query = SolicitudDescuento::with('user')->where('estado', 'pendiente');
        if ($sucursalId !== 'global') {
            $query->where('sucursal_id', $sucursalId);
        }

        $solicitudes = $query->latest()->get();

        return view('puntoventa.descuentos', compact('solicitudes'));
    }

    /**
     * El gerente/admin aprueba o rechaza la solicitud.
     */
    public function resolverDescuento(Request $request, SolicitudDescuento $solicitud)
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isGerente(), 403);

        $request->validate(['accion' => 'required|in:aprobar,rechazar']);

        if ($solicitud->estado !== 'pendiente') {
            return back()->with('error', 'Esta solicitud ya fue resuelta o cancelada.');
        }

        $aprobar = $request->accion === 'aprobar';

        $solicitud->update([
            'estado'            => $aprobar ? 'aprobada' : 'rechazada',
            'autorizado_por_id' => auth()->id(),
            'resuelta_at'       => now(),
        ]);

        return back()->with('success', $aprobar
            ? 'Descuento de $' . number_format($solicitud->monto, 2) . ' aprobado.'
            : 'Solicitud de descuento rechazada.');
    }

    public function buscarProductos(Request $request)
    {
        $sucursalId = session('activo_sucursal_id');
        
        $query = Equipo::where('activo', true)
            ->whereIn('tipo_operacion', ['venta', 'ambas']);

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
                ->orWhere('codigo', 'like', "%{$search}%")
                ->orWhere('codigo_barras', 'like', "%{$search}%"); // 🔥 Línea agregada
            });
        }

        if ($request->has('categoria') && $request->categoria) {
            $query->whereHas('categoria', function($q) use ($request) {
                $q->where('nombre', $request->categoria);
            });
        }

        $productos = $query->with(['categoria', 'unidadMedida'])->get();
        
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
            'metodo_pago' => 'required|in:efectivo,transferencia,tarjeta,credito,mixto',
            'descuento' => 'nullable|numeric|min:0',
            'solicitud_descuento_id' => 'nullable|integer',
            'solicitud_credito_id' => 'nullable|integer',
            'dias_credito' => 'required_if:metodo_pago,credito|nullable|integer|min:1',
            'abono_inicial' => 'nullable|numeric|min:0',
            'monto_recibido' => 'nullable|numeric|min:0',
            'pagos_mixtos' => 'nullable|array',
            'cliente_id' => 'nullable',
            'cliente_nombre' => 'nullable|string|max:255',
            'requiere_factura' => 'nullable', 
            'rfc_cliente' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // =============== VALIDACIÓN DE CLIENTE BLOQUEADO (LISTA NEGRA) ===============

            $clienteId = $request->cliente_id ?? null;

            if ($clienteId) {

                $cliente = Cliente::find($clienteId);

                if ($cliente) {

                    $clienteBloqueado = Cliente::with('sucursal')
                        ->where('bloqueado', true)
                        ->where(function ($query) use ($cliente) {
                            $query->where('rfc', $cliente->rfc)
                                ->orWhere('curp', $cliente->curp)
                                ->orWhere('nombre_completo', $cliente->nombre_completo);
                        })
                        ->first();

                    if ($clienteBloqueado) {

                        $sucursalOrigen = $clienteBloqueado->sucursal
                            ? $clienteBloqueado->sucursal->nombre
                            : 'Otra sucursal';

                        $motivo = $clienteBloqueado->motivo_bloqueo
                            ?? 'Sin motivo especificado';

                        // Sincronizar el cliente local
                        if (!$cliente->bloqueado) {
                            $cliente->update([
                                'bloqueado' => true,
                                'motivo_bloqueo' => $motivo
                            ]);
                        }

                        $mensajeError = "OPERACIÓN RECHAZADA: Cliente en LISTA NEGRA (Bloqueado en: {$sucursalOrigen}). Motivo: {$motivo}";

                        throw new \Exception($mensajeError);
                    }
                }
            }

            // =============================================================================

            $corteActivo = CorteCaja::where('estado', 'abierto')
                ->where('user_id', auth()->id())
                ->first();

            if (!$corteActivo) {
                throw new \Exception('No hay caja abierta. Debes abrir caja antes de realizar ventas.');
            }

            $itemsPayload = $request->input('items');
            $subtotalAcumulado = 0;
            $detallesProcesados = [];

            foreach ($itemsPayload as $item) {
                $esEspecial = isset($item['esEspecial']) && $item['esEspecial'];
                $costoAdquisicion = 0;

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
                    $precio = (float) $item['precio'];
                    $equipoId = null;
                }

                $subtotalItem = $precio * $item['cantidad'];
                $subtotalAcumulado += $subtotalItem;

                $detallesProcesados[] = [
                    'equipo_id' => $equipoId,
                    'concepto_especial' => $esEspecial ? $item['nombre'] : null, 
                    'cantidad' => $item['cantidad'],
                    'costo' => $costoAdquisicion,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotalItem
                ];
            }

            // --- RESOLVER CLIENTE DE FORMA SEGURA ---
            $clienteNombre = 'Cliente general';
            $clienteIdValido = null;

            if ($request->filled('cliente_id') && is_numeric($request->cliente_id)) {
                $clienteObj = Cliente::find($request->cliente_id);
                if ($clienteObj) {
                    $clienteIdValido = $clienteObj->id;
                    $clienteNombre = $clienteObj->nombre_completo ?? $clienteObj->nombre ?? $clienteObj->razon_social;
                }
            } elseif ($request->filled('cliente_nombre') && $request->cliente_nombre !== 'Cliente general') {
                $clienteNombre = $request->cliente_nombre;
            }

            // Cálculos de Totales y Descuentos
            $subtotalBruto = $subtotalAcumulado;
            $descuento = round(floatval($request->input('descuento', 0)), 2);

            if ($descuento > $subtotalBruto) {
                throw new \Exception('El descuento no puede ser mayor al subtotal de la venta.');
            }

            // --- AUTORIZACIÓN DE DESCUENTO ---
            $descuentoAutorizadoPorId = null;
            $solicitudDescuento = null;

            if ($descuento > 0) {
                if (auth()->user()->isAdmin() || auth()->user()->isGerente()) {
                    $descuentoAutorizadoPorId = auth()->id();
                } else {
                    // El cajero necesita una solicitud APROBADA por el gerente, con el mismo monto y sin usar
                    $solicitudDescuento = SolicitudDescuento::where('id', $request->input('solicitud_descuento_id'))
                        ->where('user_id', auth()->id())
                        ->where('tipo', 'descuento')
                        ->whereNull('origen') // los descuentos de renta no valen en el POS
                        ->where('estado', 'aprobada')
                        ->lockForUpdate()
                        ->first();

                    if (!$solicitudDescuento || abs($solicitudDescuento->monto - $descuento) >= 0.01) {
                        throw new \Exception('El descuento requiere autorización de un gerente o administrador.');
                    }

                    // El descuento solo vale para el cliente para el que se autorizó
                    $clienteVenta = $request->filled('cliente_id') ? (int) $request->input('cliente_id') : 0;
                    if ((int) ($solicitudDescuento->cliente_id ?? 0) !== $clienteVenta) {
                        throw new \Exception('Este descuento fue autorizado para otro cliente (' . ($solicitudDescuento->cliente_nombre ?: 'Público General') . '). Solicita una nueva autorización.');
                    }

                    $descuentoAutorizadoPorId = $solicitudDescuento->autorizado_por_id;
                }
            }

            $subtotalConDescuento = max(0, $subtotalBruto - $descuento);

            // IVA: SOLO si el check "¿Requiere Factura?" viene activo
            $requiereFactura = $request->boolean('requiere_factura');
            $iva = $requiereFactura ? round($subtotalConDescuento * 0.16, 2) : 0;
            $totalFinal = $subtotalConDescuento + $iva;

            $metodoPago = $request->input('metodo_pago');

            // --- AUTORIZACIÓN DE CRÉDITO (límite de monto configurable) ---
            $solicitudCredito = null;
            if ($metodoPago === 'credito' && !(auth()->user()->isAdmin() || auth()->user()->isGerente())) {
                $limiteCredito = $this->limiteCredito($sucursalId);
                if ($limiteCredito !== null && $totalFinal > $limiteCredito + 0.0001) {
                    
                    $solicitudCredito = SolicitudDescuento::where('user_id', auth()->id())
                        ->where('tipo', 'credito')
                        ->where('estado', 'aprobada')
                        ->when($sucursalId !== 'global', fn ($q) => $q->where('sucursal_id', $sucursalId))
                        ->where('cliente_id', $clienteIdValido)
                        ->whereRaw('ABS(monto - ?) < 0.01', [$totalFinal])
                        ->lockForUpdate()
                        ->first();

                    if (!$solicitudCredito || (int) ($solicitudCredito->dias_credito ?? 0) !== (int) $request->input('dias_credito', 15)) {
                        
                        throw new \Exception("ERROR_CREDITO_LIMITE|" . json_encode([
                            'monto' => $totalFinal,
                            'subtotal' => $subtotalBruto,
                            'cliente_id' => $clienteIdValido,
                            'cliente_nombre' => $clienteNombre,
                            'limite' => $limiteCredito,
                            'dias' => (int) $request->input('dias_credito', 15)
                        ]));
                    }
                }
            }

            $montoRecibido = $request->monto_recibido > 0 ? floatval($request->monto_recibido) : $totalFinal;
            $cambio = max(0, $montoRecibido - $totalFinal);

            // Crear la Venta (Guarda 'completada' para no marcarse como Cancelada)
            $venta = Venta::create([
                'folio' => Venta::generarFolio(),
                'corte_caja_id' => $corteActivo->id,
                'sucursal_id' => $sucursalId !== 'global' ? $sucursalId : null,
                'cliente_id' => $clienteIdValido,
                'cliente_nombre' => $clienteNombre,
                'subtotal' => $subtotalBruto,
                'descuento' => $descuento,
                'descuento_autorizado_por_id' => $descuentoAutorizadoPorId,
                'iva' => $iva,
                'total' => $totalFinal,
                'dias_credito' => $metodoPago === 'credito' ? intval($request->input('dias_credito', 15)) : null,
                'metodo_pago' => $metodoPago,
                'monto_recibido' => $montoRecibido,
                'cambio' => $cambio,
                'pagos_mixtos' => $metodoPago === 'mixto' ? $request->pagos_mixtos : null,
                'observaciones' => $request->observaciones ?? null,
                'estado' => 'completada', 
                'requiere_factura' => $requiereFactura,
                'rfc_cliente' => $requiereFactura ? ($request->rfc_cliente ?? null) : null,
            ]);

            // La autorización de descuento es de un solo uso
            if ($solicitudDescuento) {
                $solicitudDescuento->update(['estado' => 'usada', 'venta_id' => $venta->id]);
            }
            if ($solicitudCredito) {
                $solicitudCredito->update(['estado' => 'usada', 'venta_id' => $venta->id]);
            }

            // Guardar detalles de venta
            foreach ($detallesProcesados as $detalle) {
                $detalle['venta_id'] = $venta->id;
                DetalleVenta::create($detalle);
            }

            $abonoInicial = round(floatval($request->input('abono_inicial', 0)), 2);

            if ($metodoPago === 'credito' && $abonoInicial > 0) {

                $metodoAbono = $request->input('abono_metodo', 'efectivo');

                if ($metodoAbono === 'mixto') {
                    $pagosAbono = collect($request->input('abono_pagos_mixtos', []))->map(fn ($p) => [
                        'metodo' => $p['metodo'],
                        'monto'  => round((float) $p['monto'], 2),
                    ])->values()->all();

                    if (count($pagosAbono) !== 2 || count(array_unique(array_column($pagosAbono, 'metodo'))) < 2) {
                        throw new \Exception('En el abono inicial mixto debes elegir dos métodos de pago diferentes.');
                    }
                    $abonoInicial = round(array_sum(array_column($pagosAbono, 'monto')), 2);
                } else {
                    $pagosAbono = [['metodo' => $metodoAbono, 'monto' => $abonoInicial]];
                }

                if ($abonoInicial > $totalFinal + 0.009) {
                    throw new \Exception('El abono inicial no puede ser mayor al total de la venta.');
                }

                AbonoVenta::create([
                    'venta_id'      => $venta->id,
                    'user_id'       => auth()->id(),
                    'sucursal_id'   => $sucursalId !== 'global' ? $sucursalId : null,
                    'corte_caja_id' => $corteActivo->id,
                    'monto'         => $abonoInicial,
                    'metodo'        => $metodoAbono,
                    'pagos_mixtos'  => $metodoAbono === 'mixto' ? $pagosAbono : null,
                    'referencia'    => $request->input('abono_referencia') ?: 'Abono inicial / Enganche',
                    'observaciones' => 'Abono inicial / Enganche',
                ]);

                foreach ($pagosAbono as $p) {
                    MovimientoCaja::create([
                        'corte_caja_id' => $corteActivo->id,
                        'tipo'          => 'ingreso',
                        'concepto'      => 'Abono crédito ' . $venta->folio . ' (enganche)' . ($metodoAbono === 'mixto' ? ' (mixto)' : ''),
                        'monto'         => $p['monto'],
                        'metodo'        => $p['metodo'],
                    ]);
                }
            }

            // Actualizar Arqueo / Corte de Caja
            $corteActivo->total_ventas += $totalFinal;

            if ($metodoPago === 'mixto' && is_array($request->pagos_mixtos)) {
                foreach ($request->pagos_mixtos as $pago) {
                    if ($pago['metodo'] === 'efectivo') $corteActivo->total_efectivo += $pago['monto'];
                    elseif ($pago['metodo'] === 'transferencia') $corteActivo->total_transferencias += $pago['monto'];
                    elseif ($pago['metodo'] === 'tarjeta') $corteActivo->total_tarjetas += $pago['monto'];
                }
            } else {
                if ($metodoPago === 'efectivo') $corteActivo->total_efectivo += $totalFinal;
                elseif ($metodoPago === 'transferencia') $corteActivo->total_transferencias += $totalFinal;
                elseif ($metodoPago === 'tarjeta') $corteActivo->total_tarjetas += $totalFinal;
            }

            $corteActivo->save();

            // Totales adicionales para la respuesta JSON
            $montoFleteModal = 0;
            $montoManoObraModal = 0;

            $corteActivo->load('ventas.detalles', 'movimientos');

            foreach ($corteActivo->ventas as $v) {
                
                if ($v->estado !== 'completada') continue;

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

            $abonosMov = $corteActivo->movimientos
                ->where('tipo', 'ingreso')
                ->filter(fn ($m) => \Illuminate\Support\Str::startsWith($m->concepto ?? '', 'Abono crédito'));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta realizada exitosamente',
                'venta_id' => $venta->id,
                'total' => (float) $venta->total,
                'modal_data' => [
                    'total_ventas' => number_format($corteActivo->total_ventas, 2),
                    'total_efectivo' => number_format($corteActivo->total_efectivo, 2),
                    'total_transferencias' => number_format($corteActivo->total_transferencias, 2),
                    'total_tarjetas' => number_format($corteActivo->total_tarjetas, 2),
                    'total_flete' => number_format($montoFleteModal, 2),
                    'total_mano_obra' => number_format($montoManoObraModal, 2),
                    'efectivo_esperado' => number_format($efeEsperado, 2),
                    'abonos_efectivo'      => number_format($abonosMov->where('metodo', 'efectivo')->sum('monto'), 2),
                    'abonos_transferencia' => number_format($abonosMov->where('metodo', 'transferencia')->sum('monto'), 2),
                    'abonos_tarjeta'       => number_format($abonosMov->where('metodo', 'tarjeta')->sum('monto'), 2),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            if (str_starts_with($e->getMessage(), 'ERROR_CREDITO_LIMITE|')) {
                $payload = json_decode(explode('|', $e->getMessage(), 2)[1], true);
                
                $montoCredito = (float) $payload['monto'];
                $subtBruto    = (float) $payload['subtotal'];
                $idCl         = $payload['cliente_id'];
                $nomCl        = $payload['cliente_nombre'];
                $limCred      = (float) $payload['limite'];
                $diasCredito  = (int) $payload['dias'];

                $pendiente = SolicitudDescuento::where('user_id', auth()->id())
                    ->where('tipo', 'credito')
                    ->where('estado', 'pendiente')
                    ->where('cliente_id', $idCl)
                    ->whereRaw('ABS(monto - ?) < 0.01', [$montoCredito])
                    ->first();

                if (!$pendiente) {
                    $corteActivo = CorteCaja::where('estado', 'abierto')->where('user_id', auth()->id())->first();
                    
                    $nuevaSolicitud = (new SolicitudDescuento)->forceFill([
                        'user_id'        => auth()->id(),
                        'cliente_id'     => $idCl,
                        'cliente_nombre' => $nomCl,
                        'tipo'           => 'credito',
                        'limite_credito' => $limCred,
                        'dias_credito'   => $diasCredito,
                        'sucursal_id'    => session('activo_sucursal_id') !== 'global' ? session('activo_sucursal_id') : null,
                        'corte_caja_id'  => $corteActivo ? $corteActivo->id : null,
                        'monto'          => $montoCredito,
                        'subtotal'       => $subtBruto,
                        'motivo'         => 'Venta a crédito (' . $diasCredito . ' días). El monto excede el límite de sucursal configurado ($' . number_format($limCred, 2) . ').',
                        'estado'         => 'pendiente',
                    ]);
                    $nuevaSolicitud->save();
                }

                return response()->json([
                    'success' => false,
                    'message' => 'El crédito de $' . number_format($montoCredito, 2) . ' excede el límite sin autorización. Se ha enviado la solicitud automáticamente al gerente. Espera la confirmación en sistema y vuelve a Registrar.'
                ], 422); 
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function ticket(Venta $venta)
    {
        $venta->load(['detalles.equipo', 'cliente', 'sucursal', 'abonos']); 
        return view('puntoventa.ticket', compact('venta'));
    }

    public function historial(Request $request)
    {
        $sucursalId = session('activo_sucursal_id');
        
        $fechaFiltro = $request->get('fecha', date('Y-m-d'));

        $ventasQuery = Venta::with(['cliente', 'detalles.equipo'])
            ->whereDate('created_at', $fechaFiltro);
        
        if ($sucursalId !== 'global') {
            $ventasQuery->where('sucursal_id', $sucursalId);
        }
        
        $ventas = $ventasQuery->latest()->get();

        // Ventas a crédito que ya tienen abonos (no se pueden cancelar): la vista avisa de inmediato
        $ventasConAbonos = AbonoVenta::whereIn('venta_id', $ventas->pluck('id'))
            ->pluck('venta_id')->unique()->values()->all();

        return view('puntoventa.historial', compact('ventas', 'fechaFiltro', 'ventasConAbonos'));
    }

    public function cancelar(Request $request, $id)
    {
        $sucursalId = session('activo_sucursal_id');
        
        try {
            $venta = Venta::with(['detalles.equipo', 'cliente', 'sucursal'])->findOrFail($id);

            if ($venta->estado === 'cancelada') {
                return back()->with('error', 'Esta transacción comercial ya fue cancelada con anterioridad.');
            }

            $abonosRegistrados = AbonoVenta::where('venta_id', $venta->id)->get();
            $esGerente = auth()->user()->isAdmin() || auth()->user()->isGerente();

            if (!$esGerente && $venta->metodo_pago === 'credito' && $abonosRegistrados->count() > 1) {
                return back()->with('error', 'Esta venta a crédito tiene abonos adicionales registrados; no se puede cancelar sin antes resolver las devoluciones con el gerente.');
            }

            if ($venta->autorizacion_solicitada) {
                return back()->with('error', 'Esta venta ya tiene una solicitud de cancelación pendiente de revisión.');
            }

            DB::beginTransaction();

            if (!$esGerente) {
                $venta->update([
                    'autorizacion_solicitada' => true,
                    'solicitado_por_id' => auth()->id(),
                    'motivo_cancelacion' => $request->input('motivo_cancelacion', 'Cancelación solicitada por error de cobro.')
                ]);
                DB::commit();
                return back()->with('success', "Se envió la solicitud de cancelación al gerente para la venta {$venta->folio}.");
            }

            foreach ($venta->detalles as $detalle) {
                $producto = Equipo::find($detalle->equipo_id);
                if ($producto) {
                    if ($sucursalId !== 'global' && $venta->sucursal_id) {
                        $producto->actualizarStockEnSucursal($venta->sucursal_id, $detalle->cantidad, 'sumar');
                    } else {
                        $producto->increment('stock', $detalle->cantidad);
                    }
                }
            }

            $corteActivo = CorteCaja::where('estado', 'abierto')->where('id', $venta->corte_caja_id)->first();
            
            if ($corteActivo) {
                $corteActivo->decrement('total_ventas', $venta->total);

                if ($venta->metodo_pago === 'efectivo') {
                    $corteActivo->decrement('total_efectivo', $venta->total);
                } elseif ($venta->metodo_pago === 'transferencia') {
                    $corteActivo->decrement('total_transferencias', $venta->total);
                } elseif ($venta->metodo_pago === 'tarjeta') {
                    $corteActivo->decrement('total_tarjetas', $venta->total);
                } elseif ($venta->metodo_pago === 'mixto' && is_array($venta->pagos_mixtos)) {
                    foreach ($venta->pagos_mixtos as $pago) {
                        if ($pago['metodo'] === 'efectivo') $corteActivo->decrement('total_efectivo', $pago['monto']);
                        elseif ($pago['metodo'] === 'transferencia') $corteActivo->decrement('total_transferencias', $pago['monto']);
                        elseif ($pago['metodo'] === 'tarjeta') $corteActivo->decrement('total_tarjetas', $pago['monto']);
                    }
                } 
                elseif ($venta->metodo_pago === 'credito' && $abonosRegistrados->count() > 0) {
                    foreach ($abonosRegistrados as $ab) {
                        foreach (CreditoController::desglosePago($ab) as $p) {
                            MovimientoCaja::create([
                                'corte_caja_id' => $corteActivo->id,
                                'tipo'          => 'egreso',
                                'concepto'      => 'Devolución de abono de crédito por cancelación ' . $venta->folio,
                                'monto'         => $p['monto'],
                                'metodo'        => $p['metodo'],
                            ]);
                        }
                    }
                }
                $corteActivo->save();
            }

            $venta->update(['estado' => 'cancelada', 'autorizado_por_id' => auth()->id()]);
            DB::commit();
            
            return back()->with('success', "Venta con Folio {$venta->folio} cancelada con éxito. El inventario fue restaurado.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error técnico al procesar: ' . $e->getMessage());
        }
    }

    public function cortes()
    {
        $cortes = CorteCaja::with(['user', 'movimientos', 'ventas.detalles'])
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
            'sucursal_id' => session('activo_sucursal_id') !== 'global' ? session('activo_sucursal_id') : null,
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

        $inicio = \Carbon\Carbon::parse($request->get('fecha_inicio', date('Y-m-01')))->startOfDay();
        $fin = \Carbon\Carbon::parse($request->get('fecha_fin', date('Y-m-d')))->endOfDay();

        $topQuery = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->leftJoin('equipos', 'detalle_ventas.equipo_id', '=', 'equipos.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.created_at', [$inicio, $fin]);

        if (!$isGlobalAdmin) {
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

        $rep = $this->datosReporte($inicio, $fin);
        $repRentas = $this->datosReporteRentas($inicio, $fin);

        return view('puntoventa.reportes', compact(
            'topProductosNombres', 
            'topProductosCantidades', 
            'horasDia', 
            'montosDia', 
            'mesesNombres', 
            'montosMes',
            'rep',
            'repRentas'
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
            $ventasQuery->where('sucursal_id', $sucursalId);
        }
        
        $ventas = $ventasQuery->get();
        $totalVentas = $ventas->sum('total');

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
            'credito'       => $ventas->where('metodo_pago', 'credito')->sum('total'),
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

        $rep = $this->datosReporte($inicio, $fin);

        $titulo = "Informe Financiero de Auditoría";

        $pdf = Pdf::loadView('puntoventa.reporte_pdf', compact(
            'ventas', 'titulo', 'totalVentas', 'pagosPorMetodo', 'topProductos', 
            'inicio', 'fin', 'sucursalNombre', 'sucursalObj', 'logoBase64', 'rep'
        ));
        
        return $pdf->download('Balance_Financiero_' . $inicio->format('Ymd') . '_' . $fin->format('Ymd') . '.pdf');
    }

    /**
     * Avisos al cajero sobre sus solicitudes de CRÉDITO (autorizado / rechazado).
     * GET /puntoventa/creditos/estado?ids=1,2 -> sus pendientes + las ids consultadas
     */
    public function estadoCreditos(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($i) => (int) $i)->filter()->unique()->take(50)->values();

        $solicitudes = SolicitudDescuento::with('autorizador')
            ->where('user_id', auth()->id())
            ->where('tipo', 'credito')
            ->where(function ($q) use ($ids) {
                $q->where('estado', 'pendiente');
                if ($ids->isNotEmpty()) {
                    $q->orWhereIn('id', $ids);
                }
            })
            ->get();

        $sucursales = Sucursal::whereIn('id', $solicitudes->pluck('sucursal_id')->filter()->unique())
            ->pluck('nombre', 'id');

        $creditos = $solicitudes->map(fn ($s) => [
            'id'          => $s->id,
            'cliente'     => $s->cliente_nombre ?: 'Público General',
            'monto'       => (float) $s->monto,
            'limite'      => $s->limite_credito !== null ? (float) $s->limite_credito : null,
            'dias'        => $s->dias_credito !== null ? (int) $s->dias_credito : null,
            'sucursal'    => $sucursales[$s->sucursal_id] ?? 'Sin sucursal',
            'autorizador' => $s->autorizador?->name,
            // 'usada' = aprobada y ya aplicada a una venta
            'estado'      => match ($s->estado) {
                'pendiente' => 'pendiente',
                'rechazada' => 'rechazada',
                'cancelada' => 'cancelada',
                default     => 'aprobada',
            },
        ])->values();

        return response()->json(['creditos' => $creditos]);
    }


    /**
     * Datos del reporte financiero (los usan la vista, el PDF y el Excel):
     * ventas, descuentos (ventas + rentas), créditos otorgados, abonos recibidos y cartera.
     */
    private function datosReporte(\Carbon\Carbon $inicio, \Carbon\Carbon $fin): array
    {
        $sucursalId = session('activo_sucursal_id');
        $global = auth()->user()->isAdmin() && $sucursalId === 'global';
        $hoy = now()->startOfDay();
        $filtraSuc = fn ($q) => $global ? $q : $q->where('sucursal_id', $sucursalId);

        $ventas = $filtraSuc(
            Venta::with(['detalles.equipo', 'cliente', 'sucursal'])
                ->where('estado', 'completada')
                ->whereBetween('created_at', [$inicio, $fin])
        )->orderBy('created_at')->get();

        // ---- KPIs y productos
        $fletes = 0; $manoObra = 0; $costo = 0; $productos = [];
        foreach ($ventas as $v) {
            foreach ($v->detalles as $d) {
                $concepto = strtolower($d->concepto_especial ?? '');
                if (str_contains($concepto, 'flete')) {
                    $fletes += $d->subtotal;
                } elseif (str_contains($concepto, 'mano de obra')) {
                    $manoObra += $d->subtotal;
                } else {
                    $costo += ($d->costo ?? ($d->equipo->costo ?? 0)) * $d->cantidad;
                    $nombre = $d->equipo->nombre ?? ($d->concepto_especial ?: 'Servicio');
                    $productos[$nombre]['cantidad'] = ($productos[$nombre]['cantidad'] ?? 0) + $d->cantidad;
                    $productos[$nombre]['importe']  = ($productos[$nombre]['importe'] ?? 0) + $d->subtotal;
                }
            }
        }
        $totalVendido = (float) $ventas->sum('total');
        $kpi = [
            'total'     => $totalVendido,
            'costo'     => (float) $costo,
            'utilidad'  => $totalVendido - $costo,
            'fletes'    => (float) $fletes,
            'mano_obra' => (float) $manoObra,
        ];

        // ---- Canales de pago (incluye crédito)
        $canales = [];
        foreach (['efectivo', 'tarjeta', 'transferencia', 'mixto', 'credito'] as $m) {
            $sub = $ventas->where('metodo_pago', $m);
            $canales[$m] = ['n' => $sub->count(), 'total' => (float) $sub->sum('total')];
        }

        // ---- DESCUENTOS (ventas del POS + rentas)
        $ventasDesc = $ventas->filter(fn ($v) => (float) $v->descuento > 0)->values();

        $rentasDesc = collect();
        if (\Illuminate\Support\Facades\Schema::hasColumn('rentas', 'descuento')) {
            $rentasDesc = $filtraSuc(
                Renta::with(['cliente', 'sucursal'])
                    ->where('estado', '!=', 'cancelada')
                    ->where('descuento', '>', 0)
                    ->whereBetween('created_at', [$inicio, $fin])
            )->orderBy('created_at')->get();
        }

        $idsAut = $ventasDesc->pluck('descuento_autorizado_por_id')
            ->merge($rentasDesc->pluck('descuento_autorizado_por_id'))
            ->filter()->unique()->values();
        $usuarios = User::whereIn('id', $idsAut)->pluck('name', 'id');
        $motivosVenta = SolicitudDescuento::whereIn('venta_id', $ventasDesc->pluck('id'))
            ->where('tipo', 'descuento')->pluck('motivo', 'venta_id');

        $descRows = [];
        foreach ($ventasDesc as $v) {
            $sub = (float) $v->subtotal;
            $descRows[] = [
                'tipo'      => 'Venta',
                'folio'     => $v->folio,
                'fecha'     => $v->created_at,
                'cliente'   => $v->cliente_nombre ?: ($v->cliente->nombre_completo ?? 'Público General'),
                'sucursal'  => $v->sucursal->nombre ?? '—',
                'subtotal'  => $sub,
                'descuento' => (float) $v->descuento,
                'pct'       => $sub > 0 ? ((float) $v->descuento / $sub) * 100 : 0,
                'total'     => (float) $v->total,
                'autorizo'  => $usuarios->get($v->descuento_autorizado_por_id, 'Sin solicitud (directo)'),
                'motivo'    => $motivosVenta->get($v->id, ''),
            ];
        }
        foreach ($rentasDesc as $r) {
            $sub = (float) $r->subtotal;
            $descRows[] = [
                'tipo'      => 'Renta',
                'folio'     => $r->folio,
                'fecha'     => $r->created_at,
                'cliente'   => $r->cliente->nombre_completo ?? 'N/A',
                'sucursal'  => $r->sucursal->nombre ?? '—',
                'subtotal'  => $sub,
                'descuento' => (float) $r->descuento,
                'pct'       => $sub > 0 ? ((float) $r->descuento / $sub) * 100 : 0,
                'total'     => (float) $r->total,
                'autorizo'  => $usuarios->get($r->descuento_autorizado_por_id, 'Sin solicitud (directo)'),
                'motivo'    => $r->motivo_descuento ?? '',
            ];
        }
        usort($descRows, fn ($a, $b) => $a['fecha']->timestamp <=> $b['fecha']->timestamp);

        $descVentasMonto = (float) $ventasDesc->sum('descuento');
        $descRentasMonto = (float) $rentasDesc->sum('descuento');
        $brutoVentas = (float) $ventas->sum('subtotal');
        $desc = [
            'ventas_n'     => $ventasDesc->count(),
            'ventas_monto' => $descVentasMonto,
            'rentas_n'     => $rentasDesc->count(),
            'rentas_monto' => $descRentasMonto,
            'total'        => $descVentasMonto + $descRentasMonto,
            'pct_ventas'   => $brutoVentas > 0 ? ($descVentasMonto / $brutoVentas) * 100 : 0,
            'rows'         => $descRows,
        ];

        // ---- CRÉDITOS otorgados en el periodo
        $creditosVentas = $ventas->where('metodo_pago', 'credito')->values();
        $abonadoPorVenta = AbonoVenta::whereIn('venta_id', $creditosVentas->pluck('id'))
            ->selectRaw('venta_id, SUM(monto) as abonado')->groupBy('venta_id')->pluck('abonado', 'venta_id');

        $creditoRows = [];
        foreach ($creditosVentas as $v) {
            $abonado = round((float) $abonadoPorVenta->get($v->id, 0), 2);
            $total   = round((float) $v->total, 2);
            $saldo   = max(0, round($total - $abonado, 2));
            $vence   = $v->created_at->copy()->addDays((int) $v->dias_credito)->startOfDay();

            if ($saldo <= 0.009)        $estado = 'Liquidado';
            elseif ($vence->lt($hoy))   $estado = 'Vencido';
            elseif ($abonado > 0)       $estado = 'Con abonos';
            else                        $estado = 'Pendiente';

            $creditoRows[] = [
                'folio'    => $v->folio,
                'fecha'    => $v->created_at,
                'cliente'  => $v->cliente_nombre ?: ($v->cliente->nombre_completo ?? 'Público General'),
                'sucursal' => $v->sucursal->nombre ?? '—',
                'dias'     => (int) $v->dias_credito,
                'vence'    => $vence,
                'total'    => $total,
                'abonado'  => $abonado,
                'saldo'    => $saldo,
                'estado'   => $estado,
            ];
        }

        // ---- ABONOS recibidos en el periodo (de cualquier crédito)
        $abonosPeriodo = $filtraSuc(
            AbonoVenta::with('usuario')->whereBetween('created_at', [$inicio, $fin])
        )->orderBy('created_at')->get();
        $ventasDeAbonos = Venta::whereIn('id', $abonosPeriodo->pluck('venta_id')->unique())
            ->get(['id', 'folio', 'cliente_nombre'])->keyBy('id');

        // Los abonos MIXTOS se reparten por su método real (efectivo / transferencia / tarjeta)
        $abonosPorMetodo = ['efectivo' => 0.0, 'transferencia' => 0.0, 'tarjeta' => 0.0];
        foreach ($abonosPeriodo as $a) {
            foreach (CreditoController::desglosePago($a) as $p) {
                $abonosPorMetodo[$p['metodo']] = ($abonosPorMetodo[$p['metodo']] ?? 0) + $p['monto'];
            }
        }
        $abonosMixtos = $abonosPeriodo->where('metodo', 'mixto');

        $abonoRows = $abonosPeriodo->map(fn ($a) => [
            'fecha'      => $a->created_at,
            'folio'      => $ventasDeAbonos->get($a->venta_id)->folio ?? '—',
            'cliente'    => ($ventasDeAbonos->get($a->venta_id)->cliente_nombre ?? null) ?: 'Público General',
            'metodo'     => $a->metodo,
            'detalle'    => CreditoController::textoDesglose($a),
            'referencia' => $a->referencia ?? '',
            'recibio'    => $a->usuario->name ?? '—',
            'monto'      => (float) $a->monto,
        ])->all();

        $abonosMonto = (float) $abonosPeriodo->sum('monto');
        $cred = [
            'otorgados_n'    => $creditosVentas->count(),
            'otorgado_monto' => (float) $creditosVentas->sum('total'),
            'abonos_n'       => $abonosPeriodo->count(),
            'abonos_monto'   => $abonosMonto,
            // Ya incluyen la parte de cada abono mixto
            'abonos_efectivo'      => (float) $abonosPorMetodo['efectivo'],
            'abonos_transferencia' => (float) $abonosPorMetodo['transferencia'],
            'abonos_tarjeta'       => (float) $abonosPorMetodo['tarjeta'],
            // Informativo: cuánto de lo anterior llegó en abonos pagados con 2 métodos
            'abonos_mixto'         => (float) $abonosMixtos->sum('monto'),
            'abonos_mixto_n'       => $abonosMixtos->count(),
            // dinero realmente cobrado: ventas que no son a crédito + abonos recibidos
            'cobranza_real'  => $totalVendido - (float) $creditosVentas->sum('total') + $abonosMonto,
            'cartera'        => CreditoController::resumenCartera(),
            'rows'           => $creditoRows,
            'abonos'         => $abonoRows,
        ];

        uasort($productos, fn ($a, $b) => $b['cantidad'] <=> $a['cantidad']);

        // =================== DATOS PARA GRÁFICAS DEL POS ===================
        $lim = fn ($t, $n = 26) => \Illuminate\Support\Str::limit((string) $t, $n);

        // -- Vendido vs cobranza real vs crédito (por día; por mes si el rango es largo)
        $porMes = $inicio->diffInDays($fin) > 62;
        $clave  = fn ($dt) => $dt->format($porMes ? 'Y-m' : 'Y-m-d');
        $gFlujo = [];
        $cur = $porMes ? $inicio->copy()->startOfMonth() : $inicio->copy()->startOfDay();
        while ($cur->lte($fin)) {
            $gFlujo[$clave($cur)] = ['label' => $cur->format($porMes ? 'm/Y' : 'd/m'), 'v' => 0.0, 'c' => 0.0, 'cr' => 0.0];
            $porMes ? $cur->addMonthNoOverflow() : $cur->addDay();
        }
        foreach ($ventas as $v) {
            $k = $clave($v->created_at);
            if (!isset($gFlujo[$k])) continue;
            $gFlujo[$k]['v'] += (float) $v->total;
            if ($v->metodo_pago === 'credito') $gFlujo[$k]['cr'] += (float) $v->total;
            else                               $gFlujo[$k]['c']  += (float) $v->total;
        }
        foreach ($abonosPeriodo as $a) {
            $k = $clave($a->created_at);
            if (isset($gFlujo[$k])) $gFlujo[$k]['c'] += (float) $a->monto;
        }

        // -- Artículos por ingreso
        $porIngreso = [];
        foreach ($productos as $nombre => $pr) $porIngreso[] = ['nombre' => $nombre, 'cantidad' => $pr['cantidad'], 'importe' => (float) $pr['importe']];
        usort($porIngreso, fn ($a, $b) => $b['importe'] <=> $a['importe']);

        // -- Clientes frecuentes (se omite "Público General")
        $cl = [];
        foreach ($ventas as $v) {
            $nombre = trim((string) ($v->cliente_nombre ?: ($v->cliente->nombre_completo ?? '')));
            if ($nombre === '' || stripos($nombre, 'público general') !== false || stripos($nombre, 'publico general') !== false) continue;
            $key = $v->cliente_id ?? mb_strtoupper($nombre);
            $cl[$key] ??= ['cliente' => $nombre, 'compras' => 0, 'total' => 0.0, 'ultima' => null];
            $cl[$key]['compras']++;
            $cl[$key]['total'] += (float) $v->total;
            if (!$cl[$key]['ultima'] || $v->created_at->gt($cl[$key]['ultima'])) $cl[$key]['ultima'] = $v->created_at;
        }
        $clientes = array_values($cl);
        usort($clientes, fn ($a, $b) => [$b['compras'], $b['total']] <=> [$a['compras'], $a['total']]);

        // -- Ventas por hora del día (se recorta a las horas con actividad)
        $horas = array_fill(0, 24, ['n' => 0, 't' => 0.0]);
        foreach ($ventas as $v) {
            $h = (int) $v->created_at->format('G');
            $horas[$h]['n']++;
            $horas[$h]['t'] += (float) $v->total;
        }
        $activas = array_keys(array_filter($horas, fn ($x) => $x['n'] > 0));
        $h1 = $activas ? min($activas) : 8;
        $h2 = $activas ? max($activas) : 18;
        $horasLabels = []; $horasTotales = []; $horasN = [];
        for ($h = $h1; $h <= $h2; $h++) {
            $horasLabels[] = sprintf('%02d:00', $h);
            $horasTotales[] = round($horas[$h]['t'], 2);
            $horasN[] = $horas[$h]['n'];
        }

        // -- Ventas por día de la semana (Lun..Dom)
        $dias = array_fill(0, 7, ['n' => 0, 't' => 0.0]);
        foreach ($ventas as $v) {
            $d = (int) $v->created_at->dayOfWeek;   // 0 = domingo
            $dias[$d]['n']++;
            $dias[$d]['t'] += (float) $v->total;
        }
        $ordenDias = [1, 2, 3, 4, 5, 6, 0];
        $nombresDias = [0 => 'Dom', 1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb'];

        // -- Antigüedad de los créditos otorgados en el período (saldo > 0)
        $aging = ['Vigentes' => 0.0, '1-7 días' => 0.0, '8-15 días' => 0.0, '16-30 días' => 0.0, 'Más de 30 días' => 0.0];
        foreach ($creditoRows as $r) {
            if ($r['saldo'] <= 0.009) continue;
            if ($r['estado'] === 'Vencido') {
                $dv = (int) $r['vence']->diffInDays($hoy);
                $b = $dv <= 7 ? '1-7 días' : ($dv <= 15 ? '8-15 días' : ($dv <= 30 ? '16-30 días' : 'Más de 30 días'));
            } else {
                $b = 'Vigentes';
            }
            $aging[$b] += $r['saldo'];
        }

        $top8 = fn (array $a) => array_slice($a, 0, 8);
        $productosTop = $productos; // ya ordenado por cantidad
        $graf = [
            'flujo' => [
                'labels'   => array_column($gFlujo, 'label'),
                'vendido'  => array_map(fn ($x) => round($x['v'], 2), array_values($gFlujo)),
                'cobrado'  => array_map(fn ($x) => round($x['c'], 2), array_values($gFlujo)),
                'credito'  => array_map(fn ($x) => round($x['cr'], 2), array_values($gFlujo)),
                'por_mes'  => $porMes,
            ],
            'canales' => [
                'labels' => ['Efectivo', 'Terminal', 'Transferencia', 'Mixto', 'A crédito'],
                'values' => array_map(fn ($m) => round($canales[$m]['total'], 2), ['efectivo', 'tarjeta', 'transferencia', 'mixto', 'credito']),
            ],
            'top_ingreso' => [
                'labels' => array_map(fn ($e) => $lim($e['nombre']), $top8($porIngreso)),
                'values' => array_map(fn ($e) => round($e['importe'], 2), $top8($porIngreso)),
            ],
            'clientes' => [
                'labels'  => array_map(fn ($c) => $lim($c['cliente']), $top8($clientes)),
                'values'  => array_column($top8($clientes), 'compras'),
                'totales' => array_map(fn ($c) => round($c['total'], 2), $top8($clientes)),
            ],
            'horas' => ['labels' => $horasLabels, 'totales' => $horasTotales, 'n' => $horasN],
            'dias'  => [
                'labels'  => array_map(fn ($d) => $nombresDias[$d], $ordenDias),
                'totales' => array_map(fn ($d) => round($dias[$d]['t'], 2), $ordenDias),
                'n'       => array_map(fn ($d) => $dias[$d]['n'], $ordenDias),
            ],
            'composicion' => [
                'labels' => ['Productos / equipo', 'Fletes', 'Mano de obra'],
                'values' => [round((float) array_sum(array_column($productos, 'importe')), 2), round($kpi['fletes'], 2), round($kpi['mano_obra'], 2)],
            ],
            'aging' => ['labels' => array_keys($aging), 'values' => array_map(fn ($v) => round($v, 2), array_values($aging))],
            // tablas completas (Excel / PDF)
            'por_ingreso'  => $porIngreso,
            'clientes_all' => $clientes,
            'aging_all'    => $aging,
        ];

        return [
            'ventas'    => $ventas,
            'kpi'       => $kpi,
            'canales'   => $canales,
            'desc'      => $desc,
            'cred'      => $cred,
            'productos' => $productos,
            'graf'      => $graf,
        ];
    }

    /** Exporta el balance financiero a Excel (.xlsx) con varias hojas. */
    public function exportarExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $inicio = \Carbon\Carbon::parse($request->fecha_inicio)->startOfDay();
        $fin    = \Carbon\Carbon::parse($request->fecha_fin)->endOfDay();
        $rep    = $this->datosReporte($inicio, $fin);

        $sid = session('activo_sucursal_id');
        $sucursalNombre = session('activo_sucursal_nombre', 'Consola / Matriz General');
        if ($sid && $sid !== 'global') {
            $sucursalNombre = Sucursal::where('id', $sid)->value('nombre') ?? $sucursalNombre;
        }

        $k = $rep['kpi']; $d = $rep['desc']; $c = $rep['cred']; $cart = $c['cartera'];
        $H = fn ($t) => XlsxSimple::head($t);
        $M = fn ($n) => XlsxSimple::money($n);
        $MB = fn ($n) => XlsxSimple::moneyB($n);
        $f = fn ($dt) => $dt ? $dt->format('d/m/Y H:i') : '';
        $fd = fn ($dt) => $dt ? $dt->format('d/m/Y') : '';
        $metodoTxt = ['efectivo' => 'Efectivo', 'tarjeta' => 'Terminal', 'transferencia' => 'Transferencia', 'mixto' => 'Pago mixto', 'credito' => 'A crédito'];

        $x = new XlsxSimple();

        // ---------- Resumen
        $res = [
            [XlsxSimple::title('Balance Financiero')],
            [XlsxSimple::note('Sucursal: ' . $sucursalNombre . '  ·  Período: ' . $inicio->format('d/m/Y') . ' al ' . $fin->format('d/m/Y') . '  ·  Generado: ' . now()->format('d/m/Y H:i'))],
            [''],
            [$H('Ventas'), $H('Monto'), $H('Detalle')],
            ['Total vendido', $MB($k['total']), $rep['ventas']->count() . ' ventas'],
            ['Costo de producción', $M($k['costo']), 'Costo base de mercancía'],
            ['Utilidad bruta estimada', $MB($k['utilidad']), ''],
            ['Cobro de fletes', $M($k['fletes']), ''],
            ['Cobro de mano de obra', $M($k['mano_obra']), ''],
            [''],
            [$H('Descuentos otorgados'), $H('Monto'), $H('Detalle')],
            ['En ventas del POS', $M($d['ventas_monto']), $d['ventas_n'] . ' ventas con descuento (' . number_format($d['pct_ventas'], 1) . '% del subtotal bruto vendido)'],
            ['En rentas', $M($d['rentas_monto']), $d['rentas_n'] . ' rentas con descuento'],
            [XlsxSimple::bold('Total descuentos'), $MB($d['total']), ''],
            [''],
            [$H('Créditos y cobranza'), $H('Monto'), $H('Detalle')],
            ['Créditos otorgados en el período', $M($c['otorgado_monto']), $c['otorgados_n'] . ' ventas a crédito'],
            ['Abonos recibidos en el período', $MB($c['abonos_monto']), $c['abonos_n'] . ' abonos'],
            ['   · en efectivo', $M($c['abonos_efectivo']), ''],
            ['   · por transferencia', $M($c['abonos_transferencia']), ''],
            ['   · con tarjeta', $M($c['abonos_tarjeta']), ''],
            ['   · (incluido arriba) pagados en mixto', $M($c['abonos_mixto']), $c['abonos_mixto_n'] . ' abonos con 2 métodos'],
            ['Cobranza real del período', $MB($c['cobranza_real']), 'Ventas que no son a crédito + abonos recibidos'],
            ['Cartera por cobrar (a la fecha)', $MB($cart['por_cobrar']), $cart['n_abiertos'] . ' créditos abiertos'],
            ['   · de la cual vencida', $M($cart['vencido']), $cart['n_vencidos'] . ' créditos vencidos'],
            [''],
            [$H('Recaudación por canal de pago'), $H('Transacciones'), $H('Monto')],
        ];
        foreach ($rep['canales'] as $m => $cn) {
            $res[] = [$metodoTxt[$m] ?? ucfirst($m), XlsxSimple::int($cn['n']), $M($cn['total'])];
        }
        // Dinero realmente cobrado por método: ventas (las mixtas se reparten) + abonos (los mixtos también)
        $vpm = ['efectivo' => 0.0, 'transferencia' => 0.0, 'tarjeta' => 0.0];
        foreach ($rep['ventas'] as $v) {
            if ($v->metodo_pago === 'mixto' && is_array($v->pagos_mixtos)) {
                foreach ($v->pagos_mixtos as $p) {
                    if (isset($vpm[$p['metodo']])) $vpm[$p['metodo']] += (float) $p['monto'];
                }
            } elseif (isset($vpm[$v->metodo_pago])) {
                $vpm[$v->metodo_pago] += (float) $v->total;
            }
        }
        $apm = ['efectivo' => $c['abonos_efectivo'], 'transferencia' => $c['abonos_transferencia'], 'tarjeta' => $c['abonos_tarjeta']];
        $res[] = [''];
        $res[] = [$H('Dinero cobrado por método real'), $H('Ventas'), $H('Abonos de crédito'), $H('Total')];
        foreach (['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'tarjeta' => 'Tarjeta / Terminal'] as $m => $txt) {
            $res[] = [$txt, $M($vpm[$m]), $M($apm[$m]), $MB($vpm[$m] + $apm[$m])];
        }
        $res[] = [XlsxSimple::note('Los pagos mixtos (ventas y abonos) ya están repartidos en su método real. Las ventas a crédito no entran hasta que se abonan.')];
        $x->sheet('Resumen', $res, [38, 18, 60, 16]);

        // ---------- Ventas
        $filas = [[$H('Folio'), $H('Fecha'), $H('Cliente'), $H('Sucursal'), $H('Método'), $H('Subtotal'), $H('Descuento'), $H('IVA'), $H('Total'), $H('Factura'), $H('Días crédito'), $H('Desglose pago mixto')]];
        foreach ($rep['ventas'] as $v) {
            $desgloseMixto = ($v->metodo_pago === 'mixto' && is_array($v->pagos_mixtos))
                ? collect($v->pagos_mixtos)->map(fn ($p) => ucfirst($p['metodo']) . ' $' . number_format((float) $p['monto'], 2))->implode(' + ')
                : '';
            $filas[] = [
                $v->folio, $f($v->created_at),
                $v->cliente_nombre ?: ($v->cliente->nombre_completo ?? 'Público General'),
                $v->sucursal->nombre ?? '—',
                $metodoTxt[$v->metodo_pago] ?? ucfirst((string) $v->metodo_pago),
                $M($v->subtotal), $M($v->descuento ?? 0), $M($v->iva ?? 0), $M($v->total),
                $v->requiere_factura ? 'Sí' : 'No',
                $v->metodo_pago === 'credito' ? XlsxSimple::int($v->dias_credito) : '',
                $desgloseMixto,
            ];
        }
        $filas[] = ['', '', '', '', XlsxSimple::bold('TOTAL'), '', '', '', $MB($k['total'])];
        $x->sheet('Ventas', $filas, [12, 17, 30, 20, 16, 14, 14, 12, 14, 9, 13, 42], true);

        // ---------- Descuentos
        $filas = [[$H('Tipo'), $H('Folio'), $H('Fecha'), $H('Cliente'), $H('Sucursal'), $H('Subtotal'), $H('Descuento'), $H('% Desc.'), $H('Total'), $H('Autorizó'), $H('Motivo')]];
        foreach ($d['rows'] as $r) {
            $filas[] = [$r['tipo'], $r['folio'], $f($r['fecha']), $r['cliente'], $r['sucursal'], $M($r['subtotal']), $M($r['descuento']), XlsxSimple::pct(round($r['pct'], 1)), $M($r['total']), $r['autorizo'], $r['motivo']];
        }
        $filas[] = ['', '', '', '', XlsxSimple::bold('TOTAL'), '', $MB($d['total'])];
        $x->sheet('Descuentos', $filas, [9, 12, 17, 30, 20, 14, 14, 9, 14, 24, 40], true);

        // ---------- Créditos
        $filas = [[$H('Folio'), $H('Fecha'), $H('Cliente'), $H('Sucursal'), $H('Plazo (días)'), $H('Vence'), $H('Monto'), $H('Abonado'), $H('Saldo'), $H('Estado')]];
        foreach ($c['rows'] as $r) {
            $filas[] = [$r['folio'], $f($r['fecha']), $r['cliente'], $r['sucursal'], XlsxSimple::int($r['dias']), $fd($r['vence']), $M($r['total']), $M($r['abonado']), $M($r['saldo']), $r['estado']];
        }
        $filas[] = ['', '', '', '', '', XlsxSimple::bold('TOTAL'),
            $MB(array_sum(array_column($c['rows'], 'total'))),
            $MB(array_sum(array_column($c['rows'], 'abonado'))),
            $MB(array_sum(array_column($c['rows'], 'saldo')))];
        $x->sheet('Créditos', $filas, [12, 17, 30, 20, 13, 12, 14, 14, 14, 13], true);

        // ---------- Abonos
        $filas = [[$H('Fecha'), $H('Crédito (folio)'), $H('Cliente'), $H('Método'), $H('Referencia'), $H('Recibió'), $H('Monto')]];
        foreach ($c['abonos'] as $r) {
            $filas[] = [$f($r['fecha']), $r['folio'], $r['cliente'], ucfirst($r['metodo']) . ($r['detalle'] ? ' (' . $r['detalle'] . ')' : ''), $r['referencia'], $r['recibio'], $M($r['monto'])];
        }
        $filas[] = ['', '', '', '', '', XlsxSimple::bold('TOTAL'), $MB($c['abonos_monto'])];
        $x->sheet('Abonos', $filas, [17, 15, 30, 34, 24, 22, 14], true);

        // ---------- Productos
        $filas = [[$H('Producto / Concepto'), $H('Cantidad'), $H('Importe')]];
        foreach ($rep['productos'] as $nombre => $p) {
            $filas[] = [$nombre, XlsxSimple::int($p['cantidad']), $M($p['importe'])];
        }
        $x->sheet('Productos', $filas, [45, 12, 16], true);

        // ---------- Clientes frecuentes
        $filas = [[$H('Cliente'), $H('Compras'), $H('Total comprado'), $H('Última compra')]];
        foreach ($rep['graf']['clientes_all'] as $cl) {
            $filas[] = [$cl['cliente'], XlsxSimple::int($cl['compras']), $M($cl['total']), $fd($cl['ultima'])];
        }
        $x->sheet('Clientes', $filas, [38, 10, 18, 14], true);

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $x->save($tmp);

        $nombreArchivo = 'Reporte_Financiero_' . $inicio->format('Ymd') . '_' . $fin->format('Ymd') . '.xlsx';

        return response()->download($tmp, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // =====================================================================
    //  REPORTE FINANCIERO DE RENTAS
    // =====================================================================

    /**
     * Datos del estado financiero de las rentas (los usan la pestaña, el PDF y el Excel).
     *  - Rentas CONTRATADAS en el período (por fecha de creación; las canceladas se excluyen).
     *  - Pagos RECIBIDOS en el período (abonos, liquidaciones y ampliaciones) + depósitos de esas rentas.
     *  - Cartera por cobrar A LA FECHA (todas las rentas con saldo, sin importar el período).
     */
    private function datosReporteRentas(\Carbon\Carbon $inicio, \Carbon\Carbon $fin): array
    {
        $sucursalId = session('activo_sucursal_id');
        $global = auth()->user()->isAdmin() && $sucursalId === 'global';
        $hoy = now()->startOfDay();
        $filtraSuc = fn ($q) => $global ? $q : $q->where('sucursal_id', $sucursalId);

        // Convierte una renta en una fila con todos sus números financieros
        $fila = function (Renta $r) use ($hoy) {
            $total    = round((float) $r->total, 2);
            $deposito = round((float) ($r->deposito ?? 0), 2);
            $pagado   = round((float) $r->pagos->sum('monto'), 2);
            $abonado  = round($deposito + $pagado, 2);
            $saldo    = $r->estado === 'cancelada' ? 0.0 : max(0.0, round($total - $abonado, 2));

            $fechaFin = $r->fecha_fin ? $r->fecha_fin->copy()->startOfDay() : null;
            $vencida  = $r->estado === 'activa' && $fechaFin && $fechaFin->lt($hoy);
            $diasRetraso = $vencida ? (int) $fechaFin->diffInDays($hoy) : 0;

            // Retraso estimado: solo equipo pendiente de devolver (igual que en el detalle de la renta)
            $costoDia = 0;
            if ($vencida) {
                foreach ($r->detalles as $d) {
                    $pend = $d->cantidad - $d->cantidad_devuelta;
                    if ($pend > 0) $costoDia += $d->precio_dia * $pend;
                }
            }
            $multa = round($diasRetraso * $costoDia, 2);

            if ($saldo <= 0.009)    $estadoPago = 'Liquidado';
            elseif ($abonado > 0)   $estadoPago = 'Con abonos';
            else                    $estadoPago = 'Pendiente';

            return [
                'folio'        => $r->folio,
                'fecha'        => $r->created_at,
                'cliente_id'   => $r->cliente_id,
                'cliente'      => $r->cliente->nombre_completo ?? 'N/A',
                'sucursal'     => $r->sucursal->nombre ?? '—',
                'estado'       => $vencida ? 'Vencida' : ucfirst((string) $r->estado),
                'estado_pago'  => $estadoPago,
                'inicio'       => $r->fecha_inicio,
                'fin'          => $r->fecha_fin,
                'dias'         => (int) ($r->dias_totales ?? 0),
                'subtotal'     => round((float) $r->subtotal, 2),
                'flete'        => round((float) ($r->flete ?? 0), 2),
                'mano_obra'    => round((float) ($r->mano_obra ?? 0), 2),
                'descuento'    => round((float) ($r->descuento ?? 0), 2),
                'iva'          => round((float) ($r->iva ?? 0), 2),
                'cargos_extra' => round((float) ($r->cargos_extra ?? 0), 2),
                'total'        => $total,
                'deposito'     => $deposito,
                'pagado'       => $pagado,
                'saldo'        => $saldo,
                'vencida'      => $vencida,
                'dias_retraso' => $diasRetraso,
                'multa'        => $multa,
                'facturar'     => (bool) $r->facturar,
            ];
        };

        // ---- Rentas contratadas en el período
        $todas = $filtraSuc(
            Renta::with(['cliente', 'sucursal', 'pagos', 'detalles.equipo'])
                ->whereBetween('created_at', [$inicio, $fin])
        )->orderBy('created_at')->get();

        $canceladasN = $todas->where('estado', 'cancelada')->count();
        $rows = $todas->where('estado', '!=', 'cancelada')->map($fila)->values()->all();
        $col = collect($rows);

        $kpi = [
            'n'            => count($rows),
            'canceladas_n' => $canceladasN,
            'subtotal'     => (float) $col->sum('subtotal'),
            'descuentos'   => (float) $col->sum('descuento'),
            'descuentos_n' => $col->where('descuento', '>', 0)->count(),
            'iva'          => (float) $col->sum('iva'),
            'fletes'       => (float) $col->sum('flete'),
            'mano_obra'    => (float) $col->sum('mano_obra'),
            'cargos_extra' => (float) $col->sum('cargos_extra'),
            'total'        => (float) $col->sum('total'),
            'depositos'    => (float) $col->sum('deposito'),
            'pagado'       => (float) $col->sum('pagado'),
            'saldo'        => (float) $col->sum('saldo'),
        ];

        // ---- Pagos recibidos en el período (de cualquier renta no cancelada)
        $pagosPeriodo = Pago::with(['renta.cliente', 'renta.sucursal'])
            ->whereBetween('fecha_pago', [$inicio, $fin])
            ->whereHas('renta', function ($q) use ($filtraSuc) {
                $filtraSuc($q)->where('estado', '!=', 'cancelada');
            })
            ->orderBy('fecha_pago')->get();

        $porMetodo = [];
        foreach (['efectivo', 'transferencia', 'tarjeta'] as $m) {
            $sub = $pagosPeriodo->where('metodo_pago', $m);
            $porMetodo[$m] = ['n' => $sub->count(), 'total' => (float) $sub->sum('monto')];
        }
        $porTipo = [];
        foreach (['abono' => 'Abonos', 'liquidacion' => 'Liquidaciones', 'ampliacion' => 'Ampliaciones'] as $t => $txt) {
            $sub = $pagosPeriodo->where('tipo', $t);
            $porTipo[$t] = ['texto' => $txt, 'n' => $sub->count(), 'total' => (float) $sub->sum('monto')];
        }

        $pagosRows = $pagosPeriodo->map(fn ($p) => [
            'fecha'      => $p->fecha_pago,
            'folio'      => $p->renta->folio ?? '—',
            'cliente'    => $p->renta->cliente->nombre_completo ?? 'N/A',
            'sucursal'   => $p->renta->sucursal->nombre ?? '—',
            'tipo'       => ucfirst((string) $p->tipo),
            'metodo'     => (string) $p->metodo_pago,
            'referencia' => $p->referencia ?? '',
            'monto'      => (float) $p->monto,
        ])->all();

        $pagosMonto = (float) $pagosPeriodo->sum('monto');
        $cobro = [
            'n'            => $pagosPeriodo->count(),
            'pagos_monto'  => $pagosMonto,
            'depositos'    => $kpi['depositos'],
            // dinero que entró: pagos del período + depósitos de las rentas contratadas en el período
            'total'        => $pagosMonto + $kpi['depositos'],
            'por_metodo'   => $porMetodo,
            'por_tipo'     => $porTipo,
            'rows'         => $pagosRows,
        ];

        // ---- Cartera por cobrar A LA FECHA (saldo > 0, sin importar el período)
        $tPagos = (new Pago)->getTable();
        $abiertas = $filtraSuc(
            Renta::with(['cliente', 'sucursal', 'pagos', 'detalles'])
                ->where('estado', '!=', 'cancelada')
                ->whereRaw("rentas.total - COALESCE(rentas.deposito, 0) - COALESCE((select sum(monto) from {$tPagos} where {$tPagos}.renta_id = rentas.id), 0) > 0.009")
        )->orderBy('fecha_fin')->get()->map($fila)->values()->all();

        $abCol = collect($abiertas);
        $venc  = $abCol->where('vencida', true);
        $cartera = [
            'por_cobrar' => (float) $abCol->sum('saldo'),
            'n_abiertas' => $abCol->count(),
            'vencido'    => (float) $venc->sum('saldo'),
            'n_vencidas' => $venc->count(),
            'multa'      => (float) $abCol->sum('multa'),
            'rows'       => $abiertas,
        ];

        // =================== DATOS PARA GRÁFICAS ===================

        // -- Equipos más rentados (rentas del período, sin canceladas)
        $eq = [];
        foreach ($todas->where('estado', '!=', 'cancelada') as $r) {
            foreach ($r->detalles as $d) {
                $nombre = $d->equipo->nombre ?? ($d->concepto_especial ?: 'Equipo');
                $eq[$nombre] ??= ['nombre' => $nombre, 'unidades' => 0, 'ingreso' => 0.0, 'rentas' => 0];
                $eq[$nombre]['unidades'] += (int) $d->cantidad;
                $eq[$nombre]['ingreso']  += (float) $d->subtotal;
                $eq[$nombre]['rentas']++;
            }
        }
        $equipos = array_values($eq);
        usort($equipos, fn ($a, $b) => [$b['unidades'], $b['ingreso']] <=> [$a['unidades'], $a['ingreso']]);
        $porIngreso = $equipos;
        usort($porIngreso, fn ($a, $b) => $b['ingreso'] <=> $a['ingreso']);

        // -- Clientes frecuentes (por número de rentas en el período)
        $cl = [];
        foreach ($rows as $r) {
            $key = $r['cliente_id'] ?? $r['cliente'];
            $cl[$key] ??= ['cliente' => $r['cliente'], 'rentas' => 0, 'total' => 0.0, 'saldo' => 0.0, 'ultima' => null];
            $cl[$key]['rentas']++;
            $cl[$key]['total'] += $r['total'];
            $cl[$key]['saldo'] += $r['saldo'];
            if (!$cl[$key]['ultima'] || $r['fecha']->gt($cl[$key]['ultima'])) $cl[$key]['ultima'] = $r['fecha'];
        }
        $clientes = array_values($cl);
        usort($clientes, fn ($a, $b) => [$b['rentas'], $b['total']] <=> [$a['rentas'], $a['total']]);

        // -- Flujo: contratado vs cobrado (por día; por mes si el rango es largo)
        $porMes = $inicio->diffInDays($fin) > 62;
        $clave  = fn ($dt) => $dt->format($porMes ? 'Y-m' : 'Y-m-d');
        $flujo  = [];
        $cur = $porMes ? $inicio->copy()->startOfMonth() : $inicio->copy()->startOfDay();
        while ($cur->lte($fin)) {
            $flujo[$clave($cur)] = ['label' => $cur->format($porMes ? 'm/Y' : 'd/m'), 'c' => 0.0, 'p' => 0.0];
            $porMes ? $cur->addMonthNoOverflow() : $cur->addDay();
        }
        foreach ($rows as $r) {
            $k = $clave($r['fecha']);
            if (isset($flujo[$k])) {
                $flujo[$k]['c'] += $r['total'];
                $flujo[$k]['p'] += $r['deposito'];   // el depósito se cobra al contratar
            }
        }
        foreach ($pagosRows as $p) {
            $k = $clave($p['fecha']);
            if (isset($flujo[$k])) $flujo[$k]['p'] += $p['monto'];
        }

        // -- Histórico del año (contratado vs cobrado por mes)
        $anio = $fin->year;
        $rentasMes = $filtraSuc(Renta::where('estado', '!=', 'cancelada')->whereYear('created_at', $anio))
            ->selectRaw('MONTH(created_at) as mes, SUM(total) as t')
            ->groupBy(DB::raw('MONTH(created_at)'))->pluck('t', 'mes');
        $pagosMes = Pago::whereYear('fecha_pago', $anio)
            ->whereHas('renta', function ($q) use ($filtraSuc) {
                $filtraSuc($q)->where('estado', '!=', 'cancelada');
            })
            ->selectRaw('MONTH(fecha_pago) as mes, SUM(monto) as t')
            ->groupBy(DB::raw('MONTH(fecha_pago)'))->pluck('t', 'mes');
        $mensualC = []; $mensualP = [];
        for ($m = 1; $m <= 12; $m++) {
            $mensualC[] = round((float) ($rentasMes[$m] ?? 0), 2);
            $mensualP[] = round((float) ($pagosMes[$m] ?? 0), 2);
        }

        // -- Antigüedad de la cartera
        $aging = ['Vigentes' => 0.0, 'Finalizadas con adeudo' => 0.0, '1-7 días' => 0.0, '8-15 días' => 0.0, '16-30 días' => 0.0, 'Más de 30 días' => 0.0];
        foreach ($abiertas as $r) {
            if ($r['vencida']) {
                $d = $r['dias_retraso'];
                $b = $d <= 7 ? '1-7 días' : ($d <= 15 ? '8-15 días' : ($d <= 30 ? '16-30 días' : 'Más de 30 días'));
            } else {
                $b = $r['estado'] === 'Activa' ? 'Vigentes' : 'Finalizadas con adeudo';
            }
            $aging[$b] += $r['saldo'];
        }

        // -- Rentas por estado
        $estados = [];
        foreach ($rows as $r) $estados[$r['estado']] = ($estados[$r['estado']] ?? 0) + 1;

        $lim = fn ($t, $n = 26) => \Illuminate\Support\Str::limit($t, $n);
        $graf = [
            'flujo' => [
                'labels'     => array_column($flujo, 'label'),
                'contratado' => array_map(fn ($x) => round($x['c'], 2), array_values($flujo)),
                'cobrado'    => array_map(fn ($x) => round($x['p'], 2), array_values($flujo)),
                'por_mes'    => $porMes,
            ],
            'metodos' => [
                'labels' => ['Efectivo', 'Transferencia', 'Tarjeta', 'Depósitos'],
                'values' => [
                    round($porMetodo['efectivo']['total'], 2), round($porMetodo['transferencia']['total'], 2),
                    round($porMetodo['tarjeta']['total'], 2), round($kpi['depositos'], 2),
                ],
            ],
            'top_unidades' => [
                'labels' => array_map(fn ($e) => $lim($e['nombre']), array_slice($equipos, 0, 8)),
                'values' => array_column(array_slice($equipos, 0, 8), 'unidades'),
            ],
            'top_ingreso' => [
                'labels' => array_map(fn ($e) => $lim($e['nombre']), array_slice($porIngreso, 0, 8)),
                'values' => array_map(fn ($e) => round($e['ingreso'], 2), array_slice($porIngreso, 0, 8)),
            ],
            'clientes' => [
                'labels'  => array_map(fn ($c) => $lim($c['cliente']), array_slice($clientes, 0, 8)),
                'values'  => array_column(array_slice($clientes, 0, 8), 'rentas'),
                'totales' => array_map(fn ($c) => round($c['total'], 2), array_slice($clientes, 0, 8)),
            ],
            'aging' => ['labels' => array_keys($aging), 'values' => array_map(fn ($v) => round($v, 2), array_values($aging))],
            'mensual' => ['anio' => $anio, 'contratado' => $mensualC, 'cobrado' => $mensualP],
            'estados' => ['labels' => array_keys($estados), 'values' => array_values($estados)],
            // tablas completas (Excel / PDF)
            'equipos'  => $equipos,
            'clientes_all' => $clientes,
            'aging_all' => $aging,
        ];

        return [
            'kpi'     => $kpi,
            'rows'    => $rows,
            'cobro'   => $cobro,
            'cartera' => $cartera,
            'graf'    => $graf,
        ];
    }

    /** PDF del estado financiero de rentas. */
    public function generarReporteRentas(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $inicio = \Carbon\Carbon::parse($request->fecha_inicio)->startOfDay();
        $fin    = \Carbon\Carbon::parse($request->fecha_fin)->endOfDay();

        $sucursalId = session('activo_sucursal_id');
        $sucursalObj = null;
        $logoBase64 = null;
        $sucursalNombre = session('activo_sucursal_nombre', 'Consola / Matriz General');

        if ($sucursalId && $sucursalId !== 'global') {
            $sucursalObj = Sucursal::find($sucursalId);
            if ($sucursalObj) {
                $sucursalNombre = $sucursalObj->nombre;
                if ($sucursalObj->logo && file_exists(public_path('storage/' . $sucursalObj->logo))) {
                    $path = public_path('storage/' . $sucursalObj->logo);
                    $logoBase64 = 'data:image/' . pathinfo($path, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($path));
                }
            }
        }

        $repRentas = $this->datosReporteRentas($inicio, $fin);

        $pdf = Pdf::loadView('puntoventa.reporte_rentas_pdf', compact(
            'repRentas', 'inicio', 'fin', 'sucursalNombre', 'sucursalObj', 'logoBase64'
        ))->setPaper('letter', 'landscape');

        return $pdf->download('Finanzas_Rentas_' . $inicio->format('Ymd') . '_' . $fin->format('Ymd') . '.pdf');
    }

    /** Excel (.xlsx) del estado financiero de rentas, con varias hojas. */
    public function exportarExcelRentas(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $inicio = \Carbon\Carbon::parse($request->fecha_inicio)->startOfDay();
        $fin    = \Carbon\Carbon::parse($request->fecha_fin)->endOfDay();
        $rep    = $this->datosReporteRentas($inicio, $fin);

        $sid = session('activo_sucursal_id');
        $sucursalNombre = session('activo_sucursal_nombre', 'Consola / Matriz General');
        if ($sid && $sid !== 'global') {
            $sucursalNombre = Sucursal::where('id', $sid)->value('nombre') ?? $sucursalNombre;
        }

        $k = $rep['kpi']; $c = $rep['cobro']; $cart = $rep['cartera'];
        $H  = fn ($t) => XlsxSimple::head($t);
        $M  = fn ($n) => XlsxSimple::money($n);
        $MB = fn ($n) => XlsxSimple::moneyB($n);
        $f  = fn ($dt) => $dt ? $dt->format('d/m/Y H:i') : '';
        $fd = fn ($dt) => $dt ? $dt->format('d/m/Y') : '';
        $metodoTxt = ['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'tarjeta' => 'Tarjeta / Terminal'];

        $x = new XlsxSimple();

        // ---------- Resumen
        $res = [
            [XlsxSimple::title('Estado Financiero de Rentas')],
            [XlsxSimple::note('Sucursal: ' . $sucursalNombre . '  ·  Período: ' . $inicio->format('d/m/Y') . ' al ' . $fin->format('d/m/Y') . '  ·  Generado: ' . now()->format('d/m/Y H:i'))],
            [''],
            [$H('Rentas contratadas en el período'), $H('Monto'), $H('Detalle')],
            ['Total contratado', $MB($k['total']), $k['n'] . ' rentas (' . $k['canceladas_n'] . ' canceladas excluidas)'],
            ['Subtotal (antes de descuento)', $M($k['subtotal']), ''],
            ['Descuentos otorgados', $M($k['descuentos']), $k['descuentos_n'] . ' rentas con descuento'],
            ['IVA', $M($k['iva']), ''],
            ['Cobro de fletes', $M($k['fletes']), ''],
            ['Cobro de mano de obra', $M($k['mano_obra']), ''],
            ['Cargos extra (multas, daños, faltantes)', $M($k['cargos_extra']), ''],
            ['Depósitos en garantía', $M($k['depositos']), ''],
            ['Pagos recibidos a la fecha', $M($k['pagado']), 'Abonos, liquidaciones y ampliaciones de estas rentas'],
            ['Saldo pendiente de estas rentas', $MB($k['saldo']), ''],
            [''],
            [$H('Cobranza del período'), $H('Transacciones'), $H('Monto')],
        ];
        foreach ($c['por_metodo'] as $m => $cn) {
            $res[] = [$metodoTxt[$m] ?? ucfirst($m), XlsxSimple::int($cn['n']), $M($cn['total'])];
        }
        $res[] = [XlsxSimple::bold('Pagos recibidos'), XlsxSimple::int($c['n']), $MB($c['pagos_monto'])];
        $res[] = ['Depósitos de rentas contratadas en el período', '', $M($c['depositos'])];
        $res[] = [XlsxSimple::bold('Cobranza total del período'), '', $MB($c['total'])];
        $res[] = [''];
        $res[] = [$H('Pagos por tipo'), $H('Transacciones'), $H('Monto')];
        foreach ($c['por_tipo'] as $t) {
            $res[] = [$t['texto'], XlsxSimple::int($t['n']), $M($t['total'])];
        }
        $res[] = [''];
        $res[] = [$H('Cartera por cobrar (a la fecha)'), $H('Monto'), $H('Detalle')];
        $res[] = ['Saldo por cobrar', $MB($cart['por_cobrar']), $cart['n_abiertas'] . ' rentas con saldo'];
        $res[] = ['   · de la cual en rentas vencidas', $M($cart['vencido']), $cart['n_vencidas'] . ' rentas vencidas'];
        $res[] = ['Retraso estimado aún no cargado', $M($cart['multa']), 'Días extra de rentas activas vencidas (equipo pendiente)'];
        $res[] = [XlsxSimple::note('El depósito en garantía se cuenta como abonado. Las rentas canceladas no entran en ningún total. El saldo no incluye el retraso estimado.')];
        $x->sheet('Resumen', $res, [46, 18, 58]);

        // ---------- Rentas
        $filas = [[$H('Folio'), $H('Fecha'), $H('Cliente'), $H('Sucursal'), $H('Estado'), $H('Pago'), $H('Inicio'), $H('Fin'), $H('Días'), $H('Subtotal'), $H('Descuento'), $H('IVA'), $H('Cargos extra'), $H('Total'), $H('Depósito'), $H('Pagado'), $H('Saldo')]];
        foreach ($rep['rows'] as $r) {
            $filas[] = [$r['folio'], $f($r['fecha']), $r['cliente'], $r['sucursal'], $r['estado'], $r['estado_pago'], $fd($r['inicio']), $fd($r['fin']), XlsxSimple::int($r['dias']),
                $M($r['subtotal']), $M($r['descuento']), $M($r['iva']), $M($r['cargos_extra']), $M($r['total']), $M($r['deposito']), $M($r['pagado']), $M($r['saldo'])];
        }
        $filas[] = ['', '', '', '', '', '', '', '', XlsxSimple::bold('TOTAL'), $MB($k['subtotal']), $MB($k['descuentos']), $MB($k['iva']), $MB($k['cargos_extra']), $MB($k['total']), $MB($k['depositos']), $MB($k['pagado']), $MB($k['saldo'])];
        $x->sheet('Rentas', $filas, [11, 17, 30, 20, 11, 12, 11, 11, 7, 14, 14, 12, 14, 14, 14, 14, 14], true);

        // ---------- Pagos
        $filas = [[$H('Fecha'), $H('Folio renta'), $H('Cliente'), $H('Sucursal'), $H('Tipo'), $H('Método'), $H('Referencia'), $H('Monto')]];
        foreach ($c['rows'] as $r) {
            $filas[] = [$f($r['fecha']), $r['folio'], $r['cliente'], $r['sucursal'], $r['tipo'], ucfirst($r['metodo']), $r['referencia'], $M($r['monto'])];
        }
        $filas[] = ['', '', '', '', '', '', XlsxSimple::bold('TOTAL'), $MB($c['pagos_monto'])];
        $x->sheet('Pagos', $filas, [17, 12, 30, 20, 13, 15, 24, 14], true);

        // ---------- Cartera
        $filas = [[$H('Folio'), $H('Cliente'), $H('Sucursal'), $H('Estado'), $H('Fin de renta'), $H('Días de retraso'), $H('Total'), $H('Abonado'), $H('Saldo'), $H('Retraso estimado')]];
        foreach ($cart['rows'] as $r) {
            $filas[] = [$r['folio'], $r['cliente'], $r['sucursal'], $r['estado'], $fd($r['fin']), XlsxSimple::int($r['dias_retraso']),
                $M($r['total']), $M($r['deposito'] + $r['pagado']), $M($r['saldo']), $M($r['multa'])];
        }
        $filas[] = ['', '', '', '', '', XlsxSimple::bold('TOTAL'),
            $MB(array_sum(array_column($cart['rows'], 'total'))),
            $MB(array_sum(array_map(fn ($r) => $r['deposito'] + $r['pagado'], $cart['rows']))),
            $MB($cart['por_cobrar']), $MB($cart['multa'])];
        $x->sheet('Cartera', $filas, [11, 30, 20, 12, 13, 15, 14, 14, 14, 16], true);

        // ---------- Equipos más rentados
        $filas = [[$H('Equipo'), $H('Unidades rentadas'), $H('Rentas'), $H('Ingreso')]];
        foreach ($rep['graf']['equipos'] as $e) {
            $filas[] = [$e['nombre'], XlsxSimple::int($e['unidades']), XlsxSimple::int($e['rentas']), $M($e['ingreso'])];
        }
        $filas[] = [XlsxSimple::bold('TOTAL'), XlsxSimple::int(array_sum(array_column($rep['graf']['equipos'], 'unidades'))), '', $MB(array_sum(array_column($rep['graf']['equipos'], 'ingreso')))];
        $x->sheet('Equipos', $filas, [42, 18, 10, 16], true);

        // ---------- Clientes frecuentes
        $filas = [[$H('Cliente'), $H('Rentas'), $H('Total contratado'), $H('Saldo pendiente'), $H('Última renta')]];
        foreach ($rep['graf']['clientes_all'] as $cl) {
            $filas[] = [$cl['cliente'], XlsxSimple::int($cl['rentas']), $M($cl['total']), $M($cl['saldo']), $fd($cl['ultima'])];
        }
        $x->sheet('Clientes', $filas, [38, 10, 18, 18, 14], true);

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $x->save($tmp);

        return response()->download($tmp, 'Finanzas_Rentas_' . $inicio->format('Ymd') . '_' . $fin->format('Ymd') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}