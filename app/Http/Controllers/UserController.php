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
    $usuarios = User::paginate(15);
    $roles = Role::all();
    $permisos = Permission::all();
    $superadmins = User::role('Super Admin')->count();
    return view('users', compact('usuarios', 'roles', 'permisos', 'superadmins'));
  }

  public function editarRolUsuario(Request $request, User $user){
    $user->roles()->sync($request->roles);
    return redirect()->route('admin.listarUsuarios')->with('info','Roles actualizados!');
  }

  public function editarPermisoUsuario(Request $request, User $user){
    $user->syncPermissions($request->permisos);
    return redirect()->route('admin.listarUsuarios')->with('info','Permisos actualizados!');
  }
 
}