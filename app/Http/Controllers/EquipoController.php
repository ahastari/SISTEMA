<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Categoria;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Exports\EquiposExport;
use App\Imports\EquiposImport;
use Maatwebsite\Excel\Facades\Excel;

class EquipoController extends Controller
{
    /**
     * Generar código único para el equipo basado en la categoría
     */
    private function generarCodigo($categoriaId)
    {
        $categoria = Categoria::find($categoriaId);
        $prefijos = [
            'Andamios' => 'AND', 
            'Ruedas' => 'RUE', 
            'Flete' => 'FLE',
            'Madera' => 'MAD', 
            'Herramientas' => 'HER', 
            'Equipo de Seguridad' => 'SEG', 
            'Maquinaria' => 'MAQ'
        ];
        
        $prefijo = $prefijos[$categoria->nombre ?? ''] ?? 'GEN';
        
        $ultimoEquipo = Equipo::where('codigo', 'like', $prefijo . '-%')
            ->orderBy('codigo', 'desc')
            ->first();
        
        if ($ultimoEquipo) {
            $ultimoNumero = intval(substr($ultimoEquipo->codigo, strlen($prefijo) + 1));
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }
        
        $numeroFormateado = str_pad($nuevoNumero, 3, '0', STR_PAD_LEFT);
        
        return $prefijo . '-' . $numeroFormateado;
    }

    /**
     * LISTADO DE EQUIPOS CON FILTRO POR SUCURSAL
     */
    public function index(Request $request)
    {
        if ($request->has('view')) {
            if ($request->view == 'table') {
                session(['inventory_view' => 'table']);
            } elseif ($request->view == 'kanban') {
                session(['inventory_view' => 'kanban']);
                return redirect()->route('inventario.kanban', ['tab' => $request->get('tab', 'local')]);
            }
        }

        if (session('inventory_view') === 'kanban' && !$request->has('view')) {
            return redirect()->route('inventario.kanban', ['tab' => $request->get('tab', 'local')]);
        }

        if (!session()->has('inventory_view')) {
            session(['inventory_view' => 'table']);
        }

        $sucursalId = session('activo_sucursal_id');
        $user = auth()->user();
        $isGlobalAdmin = $user->isAdmin() && $sucursalId === 'global';
        
        // PESTAÑA ACTIVA ('local' o 'externos')
        $tabActivo = $request->get('tab', 'local');

        $query = Equipo::with(['categoria', 'unidadMedida', 'sucursales']);
        
        if (!$isGlobalAdmin) {
            if ($tabActivo === 'externos') {
                // Catálogo Externo: Productos NO asignados a mi sucursal, pero SÍ a otras
                $query->whereDoesntHave('sucursales', function($q) use ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                })->whereHas('sucursales'); // Que al menos existan en 1 sucursal
            } else {
                // Mi Inventario: Productos asignados a mi sucursal
                $query->whereHas('sucursales', function($q) use ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                });
            }
        }

        // Filtros de búsqueda y categorías...
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%')
                  ->orWhere('codigo', 'like', '%' . $search . '%')
                  ->orWhere('codigo_barras', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('categoria')) {
            $query->whereHas('categoria', function($q) use ($request) {
                $q->where('nombre', $request->categoria);
            });
        }

        $query->where('activo', true);

        // Filtros de stock unificados (solo aplican a la pestaña local)
        if ($request->filled('estado_stock')) {
            $estado = $request->estado_stock;

            if ($estado === 'agotado') {
                if (!$isGlobalAdmin && $tabActivo === 'local') {
                    $query->whereHas('sucursales', function($q) use ($sucursalId) {
                        $q->where('sucursal_id', $sucursalId)->where('stock', '<=', 0);
                    });
                } else {
                    // Aplica al Catálogo Externo o Admin Global
                    $query->where('stock', '<=', 0);
                }
            } elseif ($estado === 'bajo') {
                if (!$isGlobalAdmin && $tabActivo === 'local') {
                    $query->whereHas('sucursales', function($q) use ($sucursalId) {
                        $q->where('sucursal_id', $sucursalId)
                          ->where('stock', '<=', DB::raw('equipo_sucursal.stock_minimo'))
                          ->where('stock', '>', 0);
                    });
                } else {
                    // Compara la columna global de stock vs stock_minimo real del producto
                    $query->whereColumn('stock', '<=', 'stock_minimo')->where('stock', '>', 0);
                }
            } elseif ($estado === 'normal') {
                if (!$isGlobalAdmin && $tabActivo === 'local') {
                    $query->whereHas('sucursales', function($q) use ($sucursalId) {
                        $q->where('sucursal_id', $sucursalId)
                          ->where('stock', '>', DB::raw('equipo_sucursal.stock_minimo'));
                    });
                } else {
                    // Filtra productos cuyo stock global es mayor a su stock mínimo
                    $query->whereColumn('stock', '>', 'stock_minimo');
                }
            }
        }

        $equipos = $query->latest()->paginate($request->get('per_page', 12))->withQueryString();

        // Sobrescribir variables de stock local si estamos en "Mi Inventario"
        if (!$isGlobalAdmin && $tabActivo === 'local') {
            foreach ($equipos as $equipo) {
                $equipo->stock_global = $equipo->stock;
                $equipo->stock = $equipo->getStockEnSucursal($sucursalId);
                $equipo->stock_minimo = $this->getStockMinimoEnSucursal($equipo, $sucursalId);
            }
        }

        $categorias = Categoria::where('activa', true)->orderBy('nombre')->get();
        $vistaActual = session('inventory_view', 'table');

        return view('inventario.index', compact('equipos', 'categorias', 'vistaActual', 'isGlobalAdmin', 'tabActivo'));
    }

    /**
     * Vista Kanban del inventario
     */
    public function kanban(Request $request)
    {
        session(['inventory_view' => 'kanban']);
        
        $sucursalId = session('activo_sucursal_id');
        $user = auth()->user();
        $isGlobalAdmin = $user->isAdmin() && $sucursalId === 'global';
        
        $tabActivo = $request->get('tab', 'local');

        $query = Equipo::with(['categoria', 'unidadMedida', 'sucursales'])->where('activo', true);
        
        if (!$isGlobalAdmin) {
            if ($tabActivo === 'externos') {
                $query->whereDoesntHave('sucursales', function($q) use ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                })->whereHas('sucursales');
            } else {
                $query->whereHas('sucursales', function($q) use ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%')
                  ->orWhere('codigo', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('categoria')) {
            $query->whereHas('categoria', function($q) use ($request) {
                $q->where('nombre', $request->categoria);
            });
        }

        $equipos = $query->orderBy('nombre')->get();
        
        if (!$isGlobalAdmin && $tabActivo === 'local') {
            foreach ($equipos as $equipo) {
                $equipo->stock_global = $equipo->stock;
                $equipo->stock = $equipo->getStockEnSucursal($sucursalId);
            }
        }
        
        $categorias = Categoria::where('activa', true)->orderBy('nombre')->get();
        $vistaActual = 'kanban';

        return view('inventario.kanban', compact('equipos', 'categorias', 'vistaActual', 'isGlobalAdmin', 'tabActivo'));
    }

    /**
     * Mostrar detalle de un equipo
     */
    public function show(Equipo $equipo)
    {
        $equipo->load(['categoria', 'unidadMedida', 'sucursales']);
        
        $sucursalId = session('activo_sucursal_id');
        $user = auth()->user();
        $isGlobalAdmin = $user->isAdmin() && $sucursalId === 'global';
        
        // AHORA TODOS PUEDEN VER EL STOCK DE TODAS LAS SUCURSALES
        $sucursalesConStock = [];
        
        // Ordenamos para que la sucursal actual del cajero/gerente aparezca de primero en la lista
        $sucursales = $equipo->sucursales->sortByDesc(function($sucursal) use ($sucursalId) {
            return $sucursal->id == $sucursalId ? 1 : 0;
        });

        foreach ($sucursales as $sucursal) {
            $sucursalesConStock[] = [
                'id' => $sucursal->id,
                'nombre' => $sucursal->nombre,
                'stock' => $sucursal->pivot->stock,
                'stock_minimo' => $sucursal->pivot->stock_minimo,
                'es_mi_sucursal' => ($sucursal->id == $sucursalId) // Identificador para la vista
            ];
        }
        
        // Movimientos recientes de este equipo
        $movimientosRecientes = \App\Models\MovimientoSucursal::with(['sucursalOrigen', 'sucursalDestino', 'usuario'])
            ->where('equipo_id', $equipo->id)
            ->latest()
            ->limit(10)
            ->get();
        
        return view('inventario.show', compact('equipo', 'sucursalesConStock', 'movimientosRecientes', 'isGlobalAdmin', 'sucursalId'));
    }

    /**
     * Formulario para crear equipo
     */
    public function create()
    {
        $categorias = Categoria::where('activa', true)->orderBy('nombre')->get();
        $unidades = UnidadMedida::where('activa', true)->orderBy('nombre')->get();
        $sucursales = \App\Models\Sucursal::where('activa', true)->get();
        $sucursalActual = session('activo_sucursal_id', 'global');
        
        return view('inventario.create', compact('categorias', 'unidades', 'sucursales', 'sucursalActual'));
    }

    /**
     * Guardar nuevo equipo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_barras' => 'nullable|string|max:255|unique:equipos,codigo_barras',
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
            'operaciones' => 'required|array|min:1',
            'operaciones.*' => 'in:renta,venta',
            'costo' => 'nullable|numeric|min:0',
            'precio_dia' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable|boolean',
            'sucursal_id' => 'nullable',
        ]);

        // 🔥 1. INTERCEPTOR DE DUPLICADOS: Buscar si ya existe el producto a nivel global
        $equipoExistente = Equipo::where('nombre', trim($request->nombre))
                                 ->where('categoria_id', $request->categoria_id)
                                 ->first();

        $sucursalId = $request->sucursal_id ?? session('activo_sucursal_id');
        if (!$sucursalId || $sucursalId === 'global') {
            $primeraSucursal = \App\Models\Sucursal::where('activa', true)->first();
            $sucursalId = $primeraSucursal ? $primeraSucursal->id : null;
        }

        // Si el producto ya existe, solo lo vinculamos a la sucursal actual
        if ($equipoExistente) {
            if ($sucursalId) {
                // Usamos tu método existente para sumar el nuevo stock a la tabla pivote
                $equipoExistente->actualizarStockEnSucursal($sucursalId, $request->stock, 'sumar');
                
                // Actualizamos el stock mínimo para esta sucursal específica
                DB::table('equipo_sucursal')
                    ->where('equipo_id', $equipoExistente->id)
                    ->where('sucursal_id', $sucursalId)
                    ->update(['stock_minimo' => $request->stock_minimo]);
            }

            $ruta = session('inventory_view') == 'kanban' ? 'inventario.kanban' : 'inventario.index';
            return redirect()->route($ruta)->with('success', 'El producto ya existía en el catálogo de otra sucursal. Se ha vinculado exitosamente a tu almacén bajo el código: ' . $equipoExistente->codigo);
        }

        // 2. FLUJO NORMAL: Si no existe, se crea un producto totalmente nuevo
        $ops = $request->operaciones;
        $tipo_operacion = (count($ops) == 2) ? 'ambas' : $ops[0];

        $codigo = $this->generarCodigo($request->categoria_id);
        
        $equipo = new Equipo();
        $equipo->codigo = $codigo;
        $equipo->codigo_barras = $request->codigo_barras;
        $equipo->nombre = $request->nombre;
        $equipo->categoria_id = $request->categoria_id;
        $equipo->unidad_medida_id = $request->unidad_medida_id;
        $equipo->tipo_operacion = $tipo_operacion;
        $equipo->costo = $request->costo ?? 0;
        $equipo->precio_dia = $request->precio_dia ?? 0;
        $equipo->tipo_tarifa = Equipo::tarifaParaUnidad($request->unidad_medida_id);
        $equipo->precio_venta = $request->precio_venta ?? 0;
        $equipo->stock = $request->stock;
        $equipo->stock_minimo = $request->stock_minimo;
        $equipo->descripcion = $request->descripcion;
        $equipo->activo = $request->has('activo');

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('equipos', 'public');
            $equipo->imagen = $path;
        }

        $equipo->save();

        if ($sucursalId) {
            $equipo->sucursales()->syncWithoutDetaching([
                $sucursalId => [
                    'stock' => $request->stock,
                    'stock_minimo' => $request->stock_minimo,
                ]
            ]);
            
            $equipo->sincronizarStockTotal();
        }

        $ruta = session('inventory_view') == 'kanban' ? 'inventario.kanban' : 'inventario.index';
        return redirect()->route($ruta)->with('success', 'Equipo creado exitosamente. Código: ' . $codigo);
    }

    /**
     * Formulario para editar equipo
     */
    public function edit(Equipo $equipo)
    {
        $equipo->load('sucursales');
        $categorias = Categoria::where('activa', true)->orderBy('nombre')->get();
        $unidades = UnidadMedida::where('activa', true)->orderBy('nombre')->get();
        $sucursales = \App\Models\Sucursal::where('activa', true)->get();
        $sucursalActual = session('activo_sucursal_id', 'global');
        
        $esMultiSucursal = ($sucursalActual === 'global' && $equipo->sucursales->count() > 1);

        // Si es edición normal (1 sola sucursal o estar dentro de una sucursal específica)
        if ($sucursalActual !== 'global') {
            $equipo->stock = $equipo->getStockEnSucursal($sucursalActual);
            $equipo->stock_minimo = $this->getStockMinimoEnSucursal($equipo, $sucursalActual);
        } elseif (!$esMultiSucursal && $equipo->sucursales->count() === 1) {
            // Precargar el stock exacto de esa única sucursal
            $unicaSucursal = $equipo->sucursales->first();
            $equipo->stock = $unicaSucursal->pivot->stock;
            $equipo->stock_minimo = $unicaSucursal->pivot->stock_minimo;
        }
        
        return view('inventario.edit', compact('equipo', 'categorias', 'unidades', 'sucursales', 'sucursalActual', 'esMultiSucursal'));
    }

    /**
     * Actualizar equipo
     */
    public function update(Request $request, Equipo $equipo)
    {
        $sucursalId = session('activo_sucursal_id', 'global');
        $equipo->load('sucursales');

        // Determinar si la petición procesa desglose multisucursal
        $esMultiSucursal = ($sucursalId === 'global' && $request->has('stocks_sucursales') && is_array($request->stocks_sucursales));

        $rules = [
            'codigo_barras' => 'nullable|string|max:255|unique:equipos,codigo_barras,' . $equipo->id,
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
            'operaciones' => 'required|array|min:1',
            'operaciones.*' => 'in:renta,venta',
            'costo' => 'nullable|numeric|min:0',
            'precio_dia' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'quitar_imagen' => 'nullable|in:0,1',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ];

        if ($esMultiSucursal) {
            $rules['stocks_sucursales'] = 'required|array';
            $rules['stocks_sucursales.*'] = 'required|integer|min:0';
            $rules['stocks_minimos_sucursales'] = 'required|array';
            $rules['stocks_minimos_sucursales.*'] = 'required|integer|min:0';
        } else {
            $rules['stock'] = 'required|integer|min:0';
            $rules['stock_minimo'] = 'required|integer|min:0';
        }

        $request->validate($rules);

        $ops = $request->operaciones;
        $tipo_operacion = (count($ops) == 2) ? 'ambas' : $ops[0];

        $equipo->codigo_barras = $request->codigo_barras;
        $equipo->nombre = $request->nombre;
        $equipo->categoria_id = $request->categoria_id;
        $equipo->unidad_medida_id = $request->unidad_medida_id;
        $equipo->tipo_operacion = $tipo_operacion;
        $equipo->costo = $request->costo ?? 0;
        $equipo->precio_dia = $request->precio_dia ?? 0;
        $equipo->tipo_tarifa = Equipo::tarifaParaUnidad($request->unidad_medida_id);
        $equipo->precio_venta = $request->precio_venta ?? 0;
        $equipo->descripcion = $request->descripcion;
        $equipo->activo = $request->has('activo');

        // Manejo de imagen
        if ($request->has('quitar_imagen') && $request->quitar_imagen == '1') {
            if ($equipo->imagen && Storage::disk('public')->exists($equipo->imagen)) {
                Storage::disk('public')->delete($equipo->imagen);
            }
            $equipo->imagen = null;
        }

        if ($request->hasFile('imagen')) {
            if ($equipo->imagen && Storage::disk('public')->exists($equipo->imagen)) {
                Storage::disk('public')->delete($equipo->imagen);
            }
            $path = $request->file('imagen')->store('equipos', 'public');
            $equipo->imagen = $path;
        }

        $equipo->save();

        if ($esMultiSucursal) {
            foreach ($request->stocks_sucursales as $sId => $cantStock) {
                $minStock = $request->stocks_minimos_sucursales[$sId] ?? 5;
                $equipo->sucursales()->syncWithoutDetaching([
                    $sId => [
                        'stock' => $cantStock,
                        'stock_minimo' => $minStock,
                    ]
                ]);
            }
        } else {
            // Edición normal: determinar la sucursal objetivo
            if ($sucursalId !== 'global') {
                $targetSucursalId = $sucursalId;
            } else {
                $unicaSucursal = $equipo->sucursales->first();
                if ($unicaSucursal) {
                    $targetSucursalId = $unicaSucursal->id;
                } else {
                    $primera = \App\Models\Sucursal::where('activa', true)->first();
                    $targetSucursalId = $primera ? $primera->id : null;
                }
            }

            if ($targetSucursalId) {
                $equipo->actualizarStockEnSucursal($targetSucursalId, $request->stock, 'establecer');
                DB::table('equipo_sucursal')
                    ->updateOrInsert(
                        ['equipo_id' => $equipo->id, 'sucursal_id' => $targetSucursalId],
                        ['stock_minimo' => $request->stock_minimo]
                    );
            } else {
                $equipo->stock = $request->stock;
                $equipo->save();
            }
        }

        // Sincronizar el acumulado total
        $equipo->sincronizarStockTotal();

        $ruta = session('inventory_view') == 'kanban' ? 'inventario.kanban' : 'inventario.index';
        return redirect()->route($ruta)->with('success', 'Equipo actualizado exitosamente');
    }

    /**
     * Eliminar equipo
     */
    public function destroy(Equipo $equipo)
    {
        try {
            // Guardamos la ruta de la imagen temporalmente
            $rutaImagen = $equipo->imagen;

            // Intentamos desvincularlo de las sucursales y luego eliminar el registro
            $equipo->sucursales()->detach();
            $equipo->delete();

            // Si se logró eliminar de la BD sin errores, entonces borramos la foto del servidor
            if ($rutaImagen && Storage::disk('public')->exists($rutaImagen)) {
                Storage::disk('public')->delete($rutaImagen);
            }

            $ruta = session('inventory_view') == 'kanban' ? 'inventario.kanban' : 'inventario.index';
            return redirect()->route($ruta)->with('success', 'Equipo eliminado exitosamente');

        } catch (\Illuminate\Database\QueryException $e) {
            
            if ($e->getCode() == "23000") {
                
                // En lugar de eliminar, lo marcamos como INACTIVO
                $equipo->activo = false;
                $equipo->save();

                $ruta = session('inventory_view') == 'kanban' ? 'inventario.kanban' : 'inventario.index';
                return redirect()->route($ruta)->with('info', 'Este equipo ya tiene historial de rentas, ventas o movimientos y no puede borrarse para no afectar la contabilidad. En su lugar, ha sido ocultado y marcado como "Inactivo".');
            }

            // Si es cualquier otro error de base de datos
            return back()->with('error', 'Error al intentar procesar el equipo: ' . $e->getMessage());
        }
    }

    /**
     * Exportar inventario a Excel
     */
    public function exportExcel()
    {
        return Excel::download(new EquiposExport, 'inventario_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Importar inventario desde Excel
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'documento_excel' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            // Pasamos el ID de la sucursal activa al Importador
            $sucursalId = session('activo_sucursal_id');
            Excel::import(new EquiposImport($sucursalId), $request->file('documento_excel'));
            
            $ruta = session('inventory_view') == 'kanban' ? 'inventario.kanban' : 'inventario.index';
            return redirect()->route($ruta)->with('success', 'Inventario importado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }

    /**
     * Obtener stock mínimo en una sucursal específica
     */
    private function getStockMinimoEnSucursal($equipo, $sucursalId): int
    {
        $pivot = DB::table('equipo_sucursal')
            ->where('equipo_id', $equipo->id)
            ->where('sucursal_id', $sucursalId)
            ->first();
        
        return $pivot ? (int) $pivot->stock_minimo : ($equipo->stock_minimo ?? 5);
    }
}