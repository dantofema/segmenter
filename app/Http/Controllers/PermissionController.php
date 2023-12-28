<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function listarPermisos(){
        $permisos = Permission::all();
        return view('permissions', compact('permisos'));
      }

    public function renombrarPermiso(Request $request, Permission $permission){
    $permiso = Permission::find($permission)->first();
    if($permiso) {
        $nuevo = Permission::where('name', $request->newName)->first();
        if($nuevo) {
            return redirect()->back()->with('error_rename','Ya existe el permiso!')->with('id_error', $permission->id);
        } else {
            $permiso->name = $request->newName;
            $permiso->save();
            return redirect()->route('admin.listarPermisos')->with('info','Permiso actualizado!');
        }
    }
    }

    public function crearPermiso(Request $request){
        $permiso = Permission::where('name', $request->newPermissionName)->first();
        if($permiso) {
            return redirect()->back()->with('error_create','Ya existe el permiso!')->with('id', $permiso->id);
        } else {
            Permission::create(['name' => $request->newPermissionName]);
            return redirect()->route('admin.listarPermisos')->with('info','Permiso creado!');
        } 
    }
}
