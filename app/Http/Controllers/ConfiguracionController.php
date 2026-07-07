<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\PlantillaDocumento;
use App\Models\Configuracion; // <-- IMPORTANTE: Agrega esta línea
use Illuminate\Support\Facades\Cache; // <-- IMPORTANTE: Agrega esta línea

class ConfiguracionController extends Controller
{

    public function index()
    {
        $sucursales = Sucursal::all();
        $usuarios = User::with('sucursal')->get();
        $plantillas = PlantillaDocumento::all(); 
        return view('configuracion.configuracion', compact('sucursales', 'usuarios', 'plantillas'));
    }

    public function updatePlantilla(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string'
        ]);

        $plantilla = PlantillaDocumento::findOrFail($id);
        $plantilla->update($request->only(['titulo', 'contenido']));

        return redirect()->back()->with(['success' => 'Estructura del documento actualizada con éxito.', 'tab' => 'plantillas']);
    }

    // ALTA DE OPERADOR CON FOTO Y NUEVOS ROLES
    public function storeUsuario(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,gerente,cajero',
            'sucursal_id' => 'required|exists:sucursales,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'sucursal_id' => $request->sucursal_id,
            'status' => 'activo'
        ];

        if ($request->hasFile('foto')) {
            $userData['foto'] = $request->file('foto')->store('usuarios', 'public');
        }

        User::create($userData);

        return redirect()->back()->with(['success' => 'Operador registrado con éxito.', 'tab' => 'usuarios']);
    }

    // ACTUALIZACIÓN COMPLETA DE OPERADOR (REPARADO AL 100%)
    public function updateUsuario(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'role' => 'required|in:admin,gerente,cajero',
            'sucursal_id' => 'required|exists:sucursales,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $user = User::findOrFail($id);
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->sucursal_id = $request->sucursal_id;

        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existía para optimizar espacio
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            $user->foto = $request->file('foto')->store('usuarios', 'public');
        }

        $user->save();

        return redirect()->back()->with(['success' => 'Perfil operativo actualizado correctamente.', 'tab' => 'usuarios']);
    }

    public function changePassword(Request $request, $id)
    {
        $request->validate(['password' => 'required|string|min:6']);
        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with(['success' => 'Contraseña actualizada.', 'tab' => 'usuarios']);
    }

    public function bajaUsuario($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'baja';
        $user->save();
        return redirect()->back()->with(['success' => 'Acceso de usuario suspendido.', 'tab' => 'usuarios']);
    }

    public function altaUsuario($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'activo';
        $user->save();
        return redirect()->back()->with(['success' => 'Acceso de usuario reactivado.', 'tab' => 'usuarios']);
    }
    
    public function updateEmpresa(Request $request)
    {
        $request->validate([
            'empresa_nombre' => 'required|string|max:255',
            'empresa_direccion' => 'nullable|string|max:500',
            'empresa_rfc' => 'nullable|string|max:20',
            'empresa_telefono' => 'nullable|string|max:20',
            'empresa_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        // Campos de texto plano
        $campos = $request->only(['empresa_nombre', 'empresa_direccion', 'empresa_rfc', 'empresa_telefono']);
        
        foreach ($campos as $key => $value) {
            Configuracion::set($key, $value);
            Cache::forget("config_{$key}"); // Limpiamos caché para actualización en tiempo real
        }

        // Tratamiento profesional del Logo corporativo
        if ($request->hasFile('empresa_logo')) {
            // Buscamos si ya existía un logo previo para borrarlo del disco y no acumular basura
            $logoActual = Configuracion::where('key', 'empresa_logo')->first();
            if ($logoActual && $logoActual->value && Storage::disk('public')->exists($logoActual->value)) {
                Storage::disk('public')->delete($logoActual->value);
            }

            $path = $request->file('empresa_logo')->store('empresa', 'public');
            Configuracion::set('empresa_logo', $path);
            Cache::forget('config_empresa_logo');
        }

        return redirect()->back()->with(['success' => 'Información corporativa actualizada correctamente.', 'tab' => 'empresa']);
    }

    // SUCURSALES
    public function storeSucursal(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:500',
            'rfc' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        // Usamos forceFill o instanciamos directamente asegurando los datos
        $sucursal = new Sucursal($request->except('logo'));

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('sucursales', 'public');
            $sucursal->logo = $path;
        }
        $sucursal->save();

        // UX: Redireccionamos explicitamente forzando la pestaña de sucursales activa
        return redirect()->back()->with(['success' => 'Nueva sucursal dada de alta correctamente.', 'tab' => 'sucursales']);
    }

    public function updateSucursal(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:500',
            'rfc' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'activa' => 'required|in:0,1', // <-- Validamos el estado binario de la tienda
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $sucursal = Sucursal::findOrFail($id);
        
        // Asignamos datos del formulario de manera limpia
        $sucursal->nombre = $request->nombre;
        $sucursal->direccion = $request->direccion;
        $sucursal->rfc = $request->rfc;
        $sucursal->telefono = $request->telefono;
        $sucursal->activa = $request->activa;

        if ($request->hasFile('logo')) {
            // Borramos el logo anterior para no dejar basura en el almacenamiento
            if ($sucursal->logo && Storage::disk('public')->exists($sucursal->logo)) {
                Storage::disk('public')->delete($sucursal->logo);
            }
            $path = $request->file('logo')->store('sucursales', 'public');
            $sucursal->logo = $path;
        }
        $sucursal->save();

        // UX: Redireccionamos manteniendo el foco
        return redirect()->back()->with(['success' => 'Datos de la sucursal actualizados con éxito.', 'tab' => 'sucursales']);
    }
}