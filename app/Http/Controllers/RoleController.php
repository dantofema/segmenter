<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function listarRoles(){
        $roles = Role::all();
        $permisos = Permission::all();
        return view('roles', compact('roles','permisos'));
    }

    public function renombrarRol(Request $request, Role $role){
        $rol = Role::find($role)->first();
        if($rol) {
            if($request->newName){
                $nuevo = Role::where('name', $request->newName)->first();
                if($nuevo) {
                    return redirect()->back()->with('error_rename','Ya existe el rol!')->with('id_error', $role->id);
                } else {
                    $rol->name = $request->newName;
                    $rol->save();
                }      
            }
            if($request->permisos){
                $rol->syncPermissions($request->permisos);
                return redirect()->route('admin.listarRoles')->with('info','Rol actualizado!');
            } else {
                return redirect()->back()->with('error_permissions_edit','Debe asignar al menos un permiso')->with('id_error', $role->id);
            }
            
        }
    }
    
    public function crearRol(Request $request){
        if($request->newRoleName){
            $rol = Role::where('name', $request->newRoleName)->first();
            if($rol) {
                return redirect()->back()->with('error_create','Ya existe el rol!')->with('id', $rol->id);
            } else {
                if($request->permisos){
                    $nuevoRol = Role::create(['name' => $request->newRoleName]);
                    $nuevoRol->syncPermissions($request->permisos);
                    return redirect()->route('admin.listarRoles')->with('info','Rol creado!');
                } else {
                    return redirect()->back()->with('error_permissions_new','Debe asignar al menos un permiso');
                }
            }
        } else {
            return redirect()->back()->with('error_create','El nombre del rol no puede estar vacío.');
        }
         
    }

    public function detallesRol(Request $request, $roleId) {
        $rol = Role::find($roleId);
        $permisos = $rol->permissions()->pluck('name');
        return response()->json([
            'rol' => $rol,
            'permisos' => $permisos
        ]);
    }
}