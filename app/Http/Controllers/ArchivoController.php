<?php

namespace App\Http\Controllers;

use App\Model\Archivo;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Session;

class ArchivoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
	    //
      if (Auth::check()) {
        $AppUser = Auth::user();
        $archivos = $AppUser->visible_files()->get();
        $archivos = $archivos->merge($AppUser->mis_files()->get());
        try {
            if ($AppUser->hasPermissionTo('Ver Archivos')) {
                $archivos = Archivo::all();
            }
        } catch (PermissionDoesNotExist $e) {
            Session::flash('message', 'No existe el permiso "Ver Archivos"');
        }  
        if ($request->ajax()) {
            return Datatables::of($archivos)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    $button = '<button type="button" class="btn_descarga btn-sm btn-primary" > Descargar </button> ';
                    $button .= '<button type="button" class="btn_arch btn-sm btn-primary" > Ver </button>';
                    $button .= '<button type="button" class="btn_arch_procesar btn-sm btn-secondary" > Procesar </button>';
                    if ($data->user_id == Auth::user()->id) {
                        $button .= '<button type="button" class="btn_arch_delete btn-sm btn-danger " > Borrar </button>';
                    } else {
                        try {
                            if (Auth::user()->hasPermissionTo('Administrar Archivos')){
                                $button .= '<button type="button" class="btn_arch_delete btn-sm btn-danger " > Borrar </button>';
                            } else if (Auth::user()->visible_files()->get()->contains($data)){
                                $button .= '<button type="button" class="btn_arch_detach btn-sm btn-danger " > Dejar de ver </button>';
                            }
                        } catch (PermissionDoesNotExist $e) {
                            Session::flash('message', 'No existe el permiso "Administrar Archivos"');
                            $button .= '<button type="button" class="btn_arch_detach btn-sm btn-danger " > Dejar de ver </button>';
                        }
                    }    
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
      } else {
          $archivos= null;
          return redirect()->route('login');
      }
          return view('archivo.list')->with(['data'=>$archivos]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Archivo $archivo)
    {
    	//
///      return response($request->format);
      $result = $archivo->load('user');
      if ($request->format == 'html') {
        $result = $result->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
      } 
     	return $result;
          
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function edit(Archivo $archivo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Archivo $archivo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Archivo $archivo)
    {
        $this->middleware('auth');
        $this->middleware('can:run-setup');      
	    // Borro el archivo del storage
	    //
        DB::table('file_viewer')->where('archivo_id', $archivo->id)->delete();
        $file= $archivo->nombre;
        if(Storage::delete($file)){
            Log::info('Se borró el archivo: '.$archivo->nombre_original);
        }else{
            Log::error('NO se borró el archivo: '.$file);
        }
        $archivo->delete();
        return 'ok';
    }

    public function detach(Archivo $archivo)
    {
	    // Borro el archivo del storage
	    //
        Auth::user()->visible_files()->detach($archivo->id);
        return 'ok';
    }

    /**
     * Descargar archivo resource.
     *
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function descargar(Archivo $archivo)
    {
	    //
	    return $archivo->descargar();
    }

    /**
     * Procesar archivo resource.
     *
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function procesar(Archivo $archivo)
    {
	    //
	    $mensaje = $archivo->procesar()?'ik0':'m4l';
      return view('archivo.list');
    }

    public function eliminar_repetidos() {
        $this->middleware('auth');
        $this->middleware('can:run-setup');
        if (Auth::check()){
            try {
                if (Auth::user()->hasPermissionTo('Administrar Archivos')){
                    $archivos = Archivo::all();
                    $eliminados = 0;
                    foreach ($archivos as $archivo){
                        error_log("-----------------------------------------------------------------------");
                        error_log("Archivo " . $archivo->id . ". Checksum: " . $archivo->checksum);
                        $min_id = Archivo::where('checksum',$archivo->checksum)->min('id');
                        if ($min_id != $archivo->id){
                            error_log("Es copia");
                            $archivo->limpiar_copia($min_id);
                            $eliminados = $eliminados + 1;
                        } else {
                            error_log("Es el archivo original");
                        }
                    }
                    flash($eliminados . " archivos eliminados.")->info();
                } else {
                    Session::flash('message', 'No tienes permiso para hacer eso.');
                }
            } catch (PermissionDoesNotExist $e) {
                Session::flash('message', 'No existe el permiso "Administrar Archivos"');
            }
            return view('archivo.list');
        } else {
            return redirect()->route('login');
        }        
    }
}
