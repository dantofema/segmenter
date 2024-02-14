<?php

namespace App\Http\Controllers;

use App\User;
use Session;
use Auth;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class UserController extends Controller
{

  public function listarUsuarios(){
    $usuarios = User::all();
    $roles = Role::all();
    $permisos = Permission::where('is_filter', false)->get();
    $filtros = Permission::where('is_filter', true)->get();
    try{
      $superadmins = User::role('Super Admin')->count();
    } catch (Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
      Session::flash('message', 'No existe el rol "Super Admin"');
    } 
    return view('users', compact('usuarios', 'roles', 'permisos', 'filtros', 'superadmins'));
  }

  public function editarRolUsuario(Request $request, User $user){
    $user->roles()->sync($request->roles);
    return redirect()->route('admin.listarUsuarios')->with('info','Roles actualizados!');
  }

  public function editarPermisoUsuario(Request $request, User $user){
    $filtros = $user->getAllPermissions()->where('is_filter', true);
    $user->syncPermissions([$request->permisos, $filtros]);
    return redirect()->route('admin.listarUsuarios')->with('info','Permisos actualizados!');
  }

  public function editarFiltroUsuario(Request $request, User $user){
    $permisos = $user->getAllPermissions()->where('is_filter', false);
    $user->syncPermissions([$request->filtros, $permisos]);
    return redirect()->route('admin.listarUsuarios')->with('info','Filtros actualizados!');
  }
 
}