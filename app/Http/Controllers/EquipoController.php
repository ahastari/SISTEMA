<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Categoria;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Exports\EquiposExport;
use App\Imports\EquiposImport;
use Maatwebsite\Excel\Facades\Excel;

class EquipoController extends Controller
{
    private function generarCodigo($categoriaId)
    {
        $categoria = Categoria::find($categoriaId);
        $prefijos = [
            'Andamios' => 'AND', 'Ruedas' => 'RUE', 'Flete' => 'FLE',
            'Madera' => 'MAD', 'Herramientas' => 'HER', 
            'Equipo de Seguridad' => 'SEG', 'Maquinaria' => 'MAQ'
        ];
        
        $prefijo = $prefijos[$categoria->nombre] ?? 'GEN';
        
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

    // En EquipoController - index
    public function index(Request $request)
    {
        // Control de vista
        if ($request->has('view') && $request->view == 'table') {
            session(['inventory_view' => 'table']);
        } elseif (session('inventory_view') == 'kanban') {
            return redirect()->route('inventario.kanban');
        } else {
            session(['inventory_view' => 'table']);
        }

        $sucursalId = session('activo_sucursal_id');
        
        if ($sucursalId === 'global') {
            // Admin ve todo el inventario consolidado
            $query = Equipo::with(['categoria', 'unidadMedida']);
        } else {
            // Usuario ve solo su sucursal
            $query = Equipo::with(['categoria', 'unidadMedida'])
                        ->whereHas('sucursales', function($q) use ($sucursalId) {
                            $q->where('sucursal_id', $sucursalId);
                        });
        }

        // Filtros...
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%')
                ->orWhere('codigo', 'like', '%' . $request->search . '%')
                ->orWhere('codigo_barras', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('categoria') && $request->categoria) {
            $query->whereHas('categoria', function($q) use ($request) {
                $q->where('nombre', $request->categoria);
            });
        }

        $equipos = $query->latest()->paginate(10);
        $categorias = Categoria::where('activa', true)->get();

        return view('inventario.index', compact('equipos', 'categorias'));
    }

    public function show(Equipo $equipo)
    {
        $equipo->load(['categoria', 'unidadMedida']);
        
        // Obtener stock por sucursal
        $sucursalesConStock = [];
        foreach ($equipo->sucursales as $sucursal) {
            $sucursalesConStock[] = [
                'nombre' => $sucursal->nombre,
                'stock' => $sucursal->pivot->stock,
                'stock_minimo' => $sucursal->pivot->stock_minimo,
            ];
        }
        
        return view('inventario.show', compact('equipo', 'sucursalesConStock'));
    }

    public function kanban()
    {
        session(['inventory_view' => 'kanban']);
        $equipos = Equipo::with(['categoria', 'unidadMedida'])->where('activo', true)->get();
        return view('inventario.kanban', compact('equipos'));
    }

    public function create()
    {
        $categorias = Categoria::where('activa', true)->get();
        $unidades = UnidadMedida::where('activa', true)->get();
        return view('inventario.create', compact('categorias', 'unidades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_barras' => 'nullable|string|max:255|unique:equipos,codigo_barras',
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
            'operaciones' => 'required|array|min:1',
            'operaciones.*' => 'in:renta,venta',
            'precio_dia' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable'
        ]);

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
        $equipo->precio_dia = $request->precio_dia;
        $equipo->precio_venta = $request->precio_venta;
        $equipo->stock = $request->stock;
        $equipo->stock_minimo = $request->stock_minimo;
        $equipo->descripcion = $request->descripcion;
        $equipo->activo = $request->has('activo');

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('equipos', 'public');
            $equipo->imagen = $path;
        }

        $equipo->save();

        $ruta = session('inventory_view') == 'kanban' ? 'inventario.kanban' : 'inventario.index';
        return redirect()->route($ruta)->with('success', 'Equipo creado exitosamente. Código: ' . $codigo);
    }

    public function edit(Equipo $equipo)
    {
        $categorias = Categoria::where('activa', true)->get();
        $unidades = UnidadMedida::where('activa', true)->get();
        return view('inventario.edit', compact('equipo', 'categorias', 'unidades'));
    }

    public function update(Request $request, Equipo $equipo)
    {
        $validated = $request->validate([
            'codigo_barras' => 'nullable|string|max:255|unique:equipos,codigo_barras,' . $equipo->id,
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
            'operaciones' => 'required|array|min:1',
            'operaciones.*' => 'in:renta,venta',
            'precio_dia' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'quitar_imagen' => 'nullable|in:0,1',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable'
        ]);

        $ops = $request->operaciones;
        $tipo_operacion = (count($ops) == 2) ? 'ambas' : $ops[0];

        $equipo->codigo_barras = $request->codigo_barras;
        $equipo->nombre = $request->nombre;
        $equipo->categoria_id = $request->categoria_id;
        $equipo->unidad_medida_id = $request->unidad_medida_id;
        $equipo->tipo_operacion = $tipo_operacion;
        $equipo->precio_dia = $request->precio_dia;
        $equipo->precio_venta = $request->precio_venta;
        $equipo->stock = $request->stock;
        $equipo->stock_minimo = $request->stock_minimo;
        $equipo->descripcion = $request->descripcion;
        $equipo->activo = $request->has('activo');

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

        $ruta = session('inventory_view') == 'kanban' ? 'inventario.kanban' : 'inventario.index';
        return redirect()->route($ruta)->with('success', 'Equipo actualizado exitosamente');
    }

    public function destroy(Equipo $equipo)
    {
        if ($equipo->imagen && Storage::disk('public')->exists($equipo->imagen)) {
            Storage::disk('public')->delete($equipo->imagen);
        }

        $equipo->delete();

        $ruta = session('inventory_view') == 'kanban' ? 'inventario.kanban' : 'inventario.index';
        return redirect()->route($ruta)->with('success', 'Equipo eliminado exitosamente');
    }

    public function exportExcel()
    {
        return Excel::download(new EquiposExport, 'inventario.xlsx');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'documento_excel' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new EquiposImport, $request->file('documento_excel'));
            
            $ruta = session('inventory_view') == 'kanban' ? 'inventario.kanban' : 'inventario.index';
            return redirect()->route($ruta)->with('success', 'Inventario importado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al importar: Verifica que el formato sea correcto.');
        }
    }
}