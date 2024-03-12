<?php

namespace App\Http\Controllers;

use App\User;
use Session;
use Auth;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

  public function listarUsuarios(){
    $usuarios = User::all();
    $roles = Role::all();
    $permisos = Permission::where('guard_name', 'web')->get();
    $filtros = Permission::where('guard_name', 'filters')->get();
    try{
      $superadmins = User::role('Super Admin')->count();
    } catch (Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
      Session::flash('message', 'No existe el rol "Super Admin"');
    } 
    return view('users', compact('usuarios', 'roles', 'permisos', 'filtros', 'superadmins'));
  }

  public function mostrarPerfil(){
    $usuario = User::find(Auth::user()->id);
    $permisos = $usuario->getAllPermissions()->where('guard_name', 'web');
    $permisos_roles = $usuario->getPermissionsViaRoles()->pluck('name');
    $filtros = $usuario->getAllPermissions()->where('guard_name', 'filters');
    $filtros_roles = $usuario->getPermissionsViaRoles()->where('guard_name', 'filters')->pluck('name');
    $roles = $usuario->roles;
    return view('perfil', compact('usuario', 'permisos', 'permisos_roles', 'filtros', 'filtros_roles', 'roles'));
  }

  public function editarUsername(Request $request) {
    $user = Auth::user();
    $user->name = $request->input('newUsername');
    $user->save();
    return response()->json(['statusCode'=> 200,'message' => 'Nombre de usuario actualizado correctamente']);
  }

  public function editarEmail(Request $request) {
    $email = $request->input('newEmail');
    $emailOwner = User::where('email', $email)->first();
    if ($emailOwner) {
      return response()->json(['statusCode'=> 304, 'message' => 'El email ya está en uso.']);
    }
    $user = Auth::user();
    $user->email = $request->input('newEmail');
    $user->save();
    return response()->json(['statusCode'=> 200, 'message' => 'Email actualizado correctamente']);
  }

  public function editarRolUsuario(Request $request, User $user){
    $user->roles()->sync($request->roles);
    return redirect()->route('admin.listarUsuarios')->with('info','Roles actualizados!');
  }

  public function editarPermisoUsuario(Request $request, User $user){
    // le sincronizo tambien los filtros que ya tenia para que no se pierdan
    $filtros = $user->getAllPermissions()->where('guard_name', 'filters');
    $user->syncPermissions([$request->permisos, $filtros]);
    return redirect()->route('admin.listarUsuarios')->with('info','Permisos actualizados!');
  }

  public function editarFiltroUsuario(Request $request, User $user){
     // le sincronizo tambien los permisos que ya tenia para que no se pierdan
    $permisos = $user->getAllPermissions()->where('guard_name', 'web');
    // tomo los modelos de los filtros, 
    // ya que si a syncPermissions() le mando una lista de ids intentara sincronizar usando el guard default (web)
    $filtros = Permission::where('guard_name', 'filters')->get()->whereIn('id', $request->filtros);
    $user->syncPermissions([$filtros, $permisos]);
    return redirect()->route('admin.listarUsuarios')->with('info','Filtros actualizados!');
  }
 
}