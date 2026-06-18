<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Categoria;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EquipoController extends Controller
{
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

    public function index(Request $request)
    {
        $query = Equipo::with(['categoria', 'unidadMedida']);
        
        if ($request->has('search') && $request->search) {
            $query->where('nombre', 'like', '%' . $request->search . '%')
                ->orWhere('codigo', 'like', '%' . $request->search . '%');
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

    public function create()
    {
        $categorias = Categoria::where('activa', true)->get();
        $unidades = UnidadMedida::where('activa', true)->get();
        return view('inventario.create', compact('categorias', 'unidades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
            'precio_dia' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable'
        ]);

        $codigo = $this->generarCodigo($request->categoria_id);
        
        $equipo = new Equipo();
        $equipo->codigo = $codigo;
        $equipo->nombre = $request->nombre;
        $equipo->categoria_id = $request->categoria_id;
        $equipo->unidad_medida_id = $request->unidad_medida_id;
        $equipo->precio_dia = $request->precio_dia;
        $equipo->stock = $request->stock;
        $equipo->descripcion = $request->descripcion;
        $equipo->activo = $request->has('activo');

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('equipos', 'public');
            $equipo->imagen = $path;
        }

        $equipo->save();

        return redirect()->route('inventario.index')
            ->with('success', 'Equipo creado exitosamente. Código: ' . $codigo);
    }

    // CORREGIDO - Usa Equipo $equipo en lugar de $inventario
    public function show(Equipo $equipo)
    {
        $equipo->load(['categoria', 'unidadMedida']);
        return view('inventario.show', compact('equipo'));
    }

    // CORREGIDO
    public function edit(Equipo $equipo)
    {
        $categorias = Categoria::where('activa', true)->get();
        $unidades = UnidadMedida::where('activa', true)->get();
        return view('inventario.edit', compact('equipo', 'categorias', 'unidades'));
    }

    // CORREGIDO
    public function update(Request $request, Equipo $equipo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
            'precio_dia' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable'
        ]);

        $equipo->nombre = $request->nombre;
        $equipo->categoria_id = $request->categoria_id;
        $equipo->unidad_medida_id = $request->unidad_medida_id;
        $equipo->precio_dia = $request->precio_dia;
        $equipo->stock = $request->stock;
        $equipo->descripcion = $request->descripcion;
        $equipo->activo = $request->has('activo');

        if ($request->hasFile('imagen')) {
            if ($equipo->imagen && Storage::disk('public')->exists($equipo->imagen)) {
                Storage::disk('public')->delete($equipo->imagen);
            }
            $path = $request->file('imagen')->store('equipos', 'public');
            $equipo->imagen = $path;
        }

        $equipo->save();

        return redirect()->route('inventario.show', $equipo)
            ->with('success', 'Equipo actualizado exitosamente');
    }

    // CORREGIDO
    public function destroy(Equipo $equipo)
    {
        if ($equipo->imagen && Storage::disk('public')->exists($equipo->imagen)) {
            Storage::disk('public')->delete($equipo->imagen);
        }

        $equipo->delete();

        return redirect()->route('inventario.index')
            ->with('success', 'Equipo eliminado exitosamente');
    }

    public function kanban()
    {
        $equipos = Equipo::with(['categoria', 'unidadMedida'])->where('activo', true)->get();
        return view('inventario.kanban', compact('equipos'));
    }
}