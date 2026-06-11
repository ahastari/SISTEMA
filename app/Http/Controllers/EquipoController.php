<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EquipoController extends Controller
{
    private function generarCodigo($categoria)
    {
        $prefijos = [
            'Andamios' => 'AND',
            'Ruedas' => 'RUE',
            'Flete' => 'FLE',
            'Madera' => 'MAD',
            'Herramientas' => 'HER'
        ];
        
        $prefijo = $prefijos[$categoria] ?? 'GEN';
        
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
        $query = Equipo::query();
        
        if ($request->has('search') && $request->search) {
            $query->where('nombre', 'like', '%' . $request->search . '%')
                ->orWhere('codigo', 'like', '%' . $request->search . '%');
        }
        
        if ($request->has('categoria') && $request->categoria) {
            $query->where('categoria', $request->categoria);
        }
        
        $equipos = $query->latest()->paginate(10);
        
        return view('inventario.index', compact('equipos'));
    }

    public function create()
    {
        return view('inventario.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:100',
            'precio_dia' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable'
        ]);

        $codigo = $this->generarCodigo($request->categoria);
        
        $equipo = new Equipo();
        $equipo->codigo = $codigo;
        $equipo->nombre = $request->nombre;
        $equipo->categoria = $request->categoria;
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

    // CAMBIADO: Equipo $inventario en lugar de Equipo $equipo
    public function show(Equipo $inventario)
    {
        return view('inventario.show', compact('inventario'));
    }

    // CAMBIADO: Equipo $inventario en lugar de Equipo $equipo
    public function edit(Equipo $inventario)
    {
        return view('inventario.edit', compact('inventario'));
    }

    // CAMBIADO: Equipo $inventario en lugar de Equipo $equipo
    public function update(Request $request, Equipo $inventario)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:100',
            'precio_dia' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable'
        ]);

        $inventario->nombre = $request->nombre;
        $inventario->categoria = $request->categoria;
        $inventario->precio_dia = $request->precio_dia;
        $inventario->stock = $request->stock;
        $inventario->descripcion = $request->descripcion;
        $inventario->activo = $request->has('activo');

        if ($request->hasFile('imagen')) {
            if ($inventario->imagen && Storage::disk('public')->exists($inventario->imagen)) {
                Storage::disk('public')->delete($inventario->imagen);
            }
            $path = $request->file('imagen')->store('equipos', 'public');
            $inventario->imagen = $path;
        }

        $inventario->save();

        return redirect()->route('inventario.show', $inventario)
            ->with('success', 'Equipo actualizado exitosamente');
    }

    // CAMBIADO: Equipo $inventario en lugar de Equipo $equipo
    public function destroy(Equipo $inventario)
    {
        if ($inventario->imagen && Storage::disk('public')->exists($inventario->imagen)) {
            Storage::disk('public')->delete($inventario->imagen);
        }

        $inventario->delete();

        return redirect()->route('inventario.index')
            ->with('success', 'Equipo eliminado exitosamente');
    }

    public function kanban()
    {
        $equipos = Equipo::all();
        
        return view('inventario.kanban', compact('equipos'));
    }
}