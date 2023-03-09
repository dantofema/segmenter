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
        $archivos = $AppUser->visible_files()->withCount('viewers')->get();
        $archivos = $archivos->merge($AppUser->mis_files()->withCount('viewers')->get());
        try {
            if ($AppUser->hasPermissionTo('Ver Archivos')) {
                $archivos->merge(Archivo::withCount('viewers')->get());
            }
        } catch (PermissionDoesNotExist $e) {
            Session::flash('message', 'No existe el permiso "Ver Archivos"');
        }  
        $count_archivos = $archivos->count();
        if ($request->ajax()) {
            return Datatables::of($archivos)
                ->addIndexColumn()
                ->addColumn('created_at_h', function ($row){
                     return $row->created_at->format('d-M-Y');})
                ->addColumn('usuario', function ($row){
                     return $row->user->name;})
                ->addColumn('size_h', function ($row, $precision = 1 ){
                     $size = $row->size;
                     if ( $size > 0 ) {
                        $size = (int) $size;
                        $base = log($size) / log(1024);
                        $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');
                        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
                      }
                     return $size;
                     })
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
                ->setTotalRecords($count_archivos)
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
        $vistas = DB::table('file_viewer')->where('archivo_id', $archivo->id)->count();
        if ($vistas == 0){
            $file= $archivo->nombre;
            if(Storage::delete($file)){
                Log::info('Se borró el archivo: '.$archivo->nombre_original);
            }else{
                Log::error('NO se borró el archivo: '.$file);
            }
            $archivo->delete();
            return 'ok';
        } else {
            return $vistas;
        }
        
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
                    // Para todos los archivos
                    $archivos = Archivo::all();
                    $eliminados = 0;
                    error_log("------------- ELIMINAR ARCHIVOS REPETIDOS -----------------------------");
                    foreach ($archivos as $archivo){
                        $repeticiones = Archivo::where('checksum',$archivo->checksum)->count();
                        if ( $repeticiones > 1 ){
                        // Archivo repetido
                          $original = Archivo::where('checksum',$archivo->checksum)->orderby('id','asc')->first();
                          if ($original != $archivo){
                              $mensaje = "Copia de archivo id: ".$original->id.".";
                              $archivo->limpiar_copia($original);
                              $eliminados = $eliminados + 1;
                          } else {
                              $mensaje = "Es el archivo original.";
                          }
                        } else {
                          $mensaje = "Archivo no repetido.";  
                        }
                        error_log("Archivo " . $archivo->id . ". Checksum: " . $archivo->checksum.". ".$mensaje );
                        $archivo->checkChecksum();
                    }
                    flash($eliminados . " archivos eliminados de ".$archivos->count()." encontrados.")->info();
                } else {
                    flash('message', 'No tienes permiso para hacer eso.')->error();
                }
            } catch (PermissionDoesNotExist $e) {
                flash('message', 'No existe el permiso "Administrar Archivos"')->error();
            }
            return view('archivo.list');
        } else {
            return redirect()->route('login');
        }        
    }
}
