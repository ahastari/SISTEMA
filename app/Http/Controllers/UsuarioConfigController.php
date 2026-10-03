<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsuarioConfigController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,gerente,cajero',
            'sucursales' => 'required|array|min:1',
            'sucursales.*' => 'exists:sucursales,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'sucursal_id' => $request->sucursales[0], // Asigna por defecto la primera seleccionada
            'status' => 'activo'
        ];

        if ($request->hasFile('foto')) {
            $userData['foto'] = $request->file('foto')->store('usuarios', 'public');
        }

        $user = User::create($userData);
        
        // Sincronizar múltiples sucursales
        $user->sucursales()->sync($request->sucursales);

        return redirect()->back()->with(['success' => 'Operador registrado con éxito.', 'tab' => 'usuarios']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'role' => 'required|in:admin,gerente,cajero',
            'sucursales' => 'required|array|min:1',
            'sucursales.*' => 'exists:sucursales,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $user = User::findOrFail($id);
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        // Si la sucursal actual activa ya no está entre las asignadas, cambiamos al primer ID del array
        if (!in_array($user->sucursal_id, $request->sucursales)) {
            $user->sucursal_id = $request->sucursales[0];
        }

        if ($request->hasFile('foto')) {
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            $user->foto = $request->file('foto')->store('usuarios', 'public');
        }

        $user->save();

        // Sincronizar relación de sucursales
        $user->sucursales()->sync($request->sucursales);

        return redirect()->back()->with(['success' => 'Perfil operativo actualizado correctamente.', 'tab' => 'usuarios']);
    }

    // Método para cambiar la sucursal activa durante la sesión/modal
    public function cambiarSucursalActiva(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id'
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->sucursales->contains($request->sucursal_id)) {
            
            // 1. Guardar en Base de Datos
            $user->sucursal_id = $request->sucursal_id;
            $user->save();

            // 2. LA CLAVE DEL ÉXITO: Actualizar la variable exacta que lee el Helper
            session([
                'sucursal_seleccionada' => true,
                'activo_sucursal_id' => $request->sucursal_id // <- ESTE ES EL NOMBRE CORRECTO
            ]);
            
            // 3. Limpiar variables viejas
            session()->forget('mostrar_modal_sucursal');
            $user->unsetRelation('sucursal');

            return redirect()->route('dashboard')->with('success', 'Sucursal cambiada correctamente.');
        }

        return redirect()->back()->with('error', 'No tienes permiso para acceder a esta sucursal.');
    }

    public function changePassword(Request $request, $id)
    {
        $request->validate(['password' => 'required|string|min:6']);
        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();
        return redirect()->back()->with(['success' => 'Contraseña actualizada exitosamente.', 'tab' => 'usuarios']);
    }

    public function bajaUsuario($id)
    {
        $user = User::findOrFail($id);
        if (auth()->id() === $user->id) {
            return redirect()->back()->with(['error' => 'No puedes suspender tu propio acceso.', 'tab' => 'usuarios']);
        }
        $user->status = 'baja';
        $user->save();
        return redirect()->back()->with(['success' => 'Acceso de usuario suspendido exitosamente.', 'tab' => 'usuarios']);
    }

    public function altaUsuario($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'activo';
        $user->save();
        return redirect()->back()->with(['success' => 'Acceso de usuario reactivado exitosamente.', 'tab' => 'usuarios']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if (auth()->id() === $user->id) {
            return redirect()->back()->with(['error' => 'No puedes eliminar tu propio usuario.', 'tab' => 'usuarios']);
        }
        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
        }
        $user->delete();
        return redirect()->back()->with(['success' => 'Usuario eliminado permanentemente.', 'tab' => 'usuarios']);
    }
}