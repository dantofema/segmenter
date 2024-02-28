<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function listarRoles(){
        $roles = Role::all();
        $permisos = Permission::where('guard_name', 'web')->get();
        $filtros = Permission::where('guard_name', 'filters')->get();
        return view('roles', compact('roles','permisos','filtros'));
    }

    public function editarRol(Request $request, Role $role){
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
            if($request->role_type == "permisos"){
                if($request->permisos){
                    // actualizo el guard antes de hacer la sincronizacion
                    $rol->setAttribute('guard_name', 'web');
                    $rol->save();
                    $rol->syncPermissions($request->permisos);
                    return redirect()->route('admin.listarRoles')->with('info','Rol actualizado!');
                } else {
                    return redirect()->back()->with('error_authorization_edit','Debe asignar al menos un permiso/filtro')->with('id_error', $role->id);
                }
            } elseif($request->role_type == "filtros") {
                if($request->filtros){
                    // tomo los modelos ya que si a syncPermissions() le mando una lista de ids intentara sincronizar usando el guard default (web)
                    $filtros = Permission::find($request->filtros);
                    // actualizo el guard antes de hacer la sincronizacion
                    $rol->setAttribute('guard_name', 'filters');
                    $rol->save();
                    $rol->syncPermissions($request->filtros);
                    return redirect()->route('admin.listarRoles')->with('info','Rol actualizado!');
                } else {
                    return redirect()->back()->with('error_authorization_edit','Debe asignar al menos un permiso/filtro')->with('id_error', $role->id);
                }
            }
        }
    }
    
    public function crearRol(Request $request){
        if($request->newRoleName){
            $rol = Role::where('name', $request->newRoleName)->first();
            if($rol) {
                return redirect()->back()->with('error_create','Ya existe el rol!')->with('id', $rol->id);
            } else {
                if($request->role_type == "permisos"){
                    if($request->permisos){
                        $nuevoRol = Role::create(['guard_name' => 'web','name' => $request->newRoleName]);
                        $nuevoRol->syncPermissions($request->permisos);
                        return redirect()->route('admin.listarRoles')->with('info','Rol creado!');
                    } else {
                        return redirect()->back()->with('error_authorizations_new','Debe asignar al menos un permiso/filtro');
                    }
                } elseif($request->role_type == "filtros") {
                    $nuevoRol = Role::create(['guard_name' => 'filters','name' => $request->newRoleName]);
                    // tomo los modelos ya que si a syncPermissions() le mando una lista de ids intentara sincronizar usando el guard default (web)
                    $filtros = Permission::find($request->filtros);
                    $nuevoRol->syncPermissions($request->filtros);
                    return redirect()->route('admin.listarRoles')->with('info','Rol creado!');
                } else {
                    return redirect()->back()->with('error_authorizations_new','Debe asignar al menos un permiso/filtro');
                }       
            }
        } else {
            return redirect()->back()->with('error_create','El nombre del rol no puede estar vacío.');
        }
         
    }

    public function detallesRol(Request $request, $roleId) {
        $rol = Role::find($roleId);
        $autorizaciones = $rol->permissions()->pluck('name');
        return response()->json([
            'rol' => $rol,
            'autorizaciones' => $autorizaciones,
        ]);
    }
}