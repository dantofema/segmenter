<?php

namespace App\Http\Controllers;

use App\User;
use Session;
use Auth;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UserController extends Controller
{

  public function listarUsuarios(){
    $usuarios = User::paginate();
    $roles = Role::all();
    return view('users', compact('usuarios', 'roles'));
  }

  public function editarRolUsuario(Request $request, User $user){
    $user->roles()->sync($request->roles);
    return redirect()->route('admin.listarUsuarios')->with('info','Roles actualizados!');
  }
 
}