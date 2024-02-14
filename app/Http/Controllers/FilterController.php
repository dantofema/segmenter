<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class FilterController extends Controller
{
    public function listarFiltros(){
        $filtros = Permission::where('is_filter', true)->get();
        if ($filtros->isEmpty()) {
            return view('filters')->with('filtros', null);;
        }
        return view('filters', compact('filtros'));
      }

    public function renombrarFiltro(Request $request, Permission $filter){
    $filtro = Permission::where('id', $filter->id)->where('is_filter', true)->first();
    if($filtro) {
        if ($request->newName) {
            $nuevo = Permission::where('name', $request->newName)->where('is_filter', true)->first();
            if($nuevo) {
                return redirect()->back()->with('error_rename','Ya existe el filtro!')->with('id_error', $filter->id);
            } else {
                $filtro->name = $request->newName;
                $filtro->save();
                return redirect()->route('admin.listarFiltros')->with('info','Filtro actualizado!');
            }
        } else {
            return redirect()->back()->with('error_rename','El nombre del filtro no puede estar vacío.')->with('id_error', $filter->id);
        }
    }
    }

    public function crearFiltro(Request $request){
        if($request->newFilterName){
            $filtro = Permission::where('name', $request->newFilterName)->where('is_filter', true)->first();
            if($filtro) {
                return redirect()->back()->with('error_create','Ya existe el filtro!')->with('id', $filtro->id);
            } else {
                Permission::create(['name' => $request->newFilterName, 'is_filter' => true]);
                return redirect()->route('admin.listarFiltros')->with('info','Filtro creado!');
            }
        } else {
            return redirect()->back()->with('error_create','El nombre del filtro no puede estar vacío.');
        }   
    }
}
