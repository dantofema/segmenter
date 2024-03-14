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
        $archivos = self::retrieveFiles();
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
                ->addColumn('status', function($data){
                    $info = '';
                    $unico = $checksumOk = $storageOk = true;
                    if($data->es_copia){
                        $unico = false;
                        $info .= '<span class="badge badge-pill badge-warning"><span class="bi bi-exclamation-triangle" style="font-size: 0.8rem; color: rgb(0, 0, 0);"> Copia</span></span><br>';
                    }
                    if (!$data->checkChecksum){
                        $checksumOk = false;
                        $info .= '<span class="badge badge-pill badge-danger"><span class="bi bi-exclamation-triangle" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> Checksum obsoleto</span></span><br>';
                    }
                    if (!$data->checkStorage()){
                        $storageOk = false;
                        $info .= '<span class="badge badge-pill badge-dark"><span class="bi bi-archive" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> Problema de storage</span></span><br>';
                    }
                    if ($unico and $checksumOk and $storageOk){
                        $info .= '<span class="badge badge-pill badge-success"><span class="bi bi-check" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> OK</span></span><br>';
                    }
                    return $info;
                })
                ->addColumn('action', function($data){
                    $button = '<button type="button" class="btn_descarga btn-sm btn-primary" > Descargar </button> ';
                    $button .= '<button type="button" class="btn_arch btn-sm btn-primary" > Ver </button>';
                    $button .= '<button type="button" class="btn_arch_procesar btn-sm btn-secondary" > ReProcesar </button>';

                    /*
                    Sin botón de eliminar archivo por el momento

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
                            Log::error('No existe el permiso "Administrar Archivos"');
                        }
                    }
                    */
                    return $button;
                })
                ->rawColumns(['status','action'])
                ->setTotalRecords($count_archivos)
                ->make(true);
        }
        return view('archivo.list')->with(['data'=>$archivos]);
    }

    private static function retrieveFiles(){
        $AppUser = Auth::user();
        $archivos = $AppUser->visible_files()->withCount('viewers')->with('user')->get();
        $archivos = $archivos->merge($AppUser->mis_files()->withCount('viewers')->with('user')->get());
        try {
            if ($AppUser->can('Ver Archivos')) {
                $archivos = $archivos->merge(Archivo::withCount('viewers')->with('user')->get());
            }
        } catch (PermissionDoesNotExist $e) {
            Session::flash('message', 'No existe el permiso "Ver Archivos"');
        }
        return $archivos;
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


        flash('Función no implementada x seguridad...')->warning()->important();
        return view('archivo.list');
        //Aún falta testeo

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
      //  Auth::user()->visible_files()->detach($archivo->id);
        return false;
    }

    /**
     * Descargar archivo resource.
     *
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function descargar(Archivo $archivo)
    {
	    // Descarga de archivo.
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
	    // Mensaje extraño
	    $mensaje = $archivo->procesar()?'ik0':'m4l';
      return view('archivo.list');
    }

    //no envio los repetidos directamente desde la vista para permitir acceder a la función directamente por URL sin pasar por el listado
    public function eliminar_repetidos() {


        flash('Función aún en testeo...')->warning()->important();
        return redirect('archivos');
        //Aún falta testeo

        $this->middleware('can:run-setup');

        try {
            if (Auth::user()->can(['Administrar Archivos', 'Ver Archivos'])){
                // Para todos los archivos
                $archivos = Archivo::all();
                $eliminados = 0;
                Log::error("------------- ELIMINAR ARCHIVOS REPETIDOS -----------------------------");
                foreach ($archivos as $archivo){
                    if ( $archivo->es_copia ){
                        // Archivo repetido
                        $original = Archivo::where('checksum',$archivo->checksum)->orderby('id','asc')->first();
                        // Logs repetidos pero es necesario ya que este log debe mostrarse antes que los de limpiar_copia()
                        Log::error("Archivo " . $archivo->id . ". Checksum: " . $archivo->checksum.". Copia de archivo id: ".$original->id."." );
                        $archivo->limpiar_copia($original);
                        $eliminados = $eliminados + 1;
                    } else {
                        Log::info("Archivo " . $archivo->id . ". Checksum: " . $archivo->checksum.". Es el archivo original." );
                    }
                    $archivo->checkChecksum; //para que?
                }
                flash($eliminados . " archivos eliminados de ".$archivos->count()." encontrados.")->info();
                return redirect('archivos');
            } else {
                flash('message', 'No tienes permiso para hacer eso.')->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Administrar Archivos"')->error();
        }
    }

    public function listar_repetidos(){
        try {
            if (Auth::user()->can(['Administrar Archivos', 'Ver Archivos'])){
                $archivos = Archivo::all();
                $repetidos = [];
                foreach ($archivos as $archivo){
                    if ( $archivo->es_copia() ){
                        // Archivo repetido
                        $original = Archivo::where('checksum',$archivo->checksum)->orderby('id','asc')->first();
                        $repetidos[] = [$original,$archivo];
                    } else {
                        $mensaje = "Es el archivo original.";
                    }
                }
                return view('archivo.repetidos', compact('repetidos'));
            } else {
                flash('No tienes permiso para hacer eso.')->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('No existe el permiso "Administrar Archivos"')->error();
        }
    }

    //no envio los obsoletos directamente desde la vista para permitir acceder a la función directamente por URL sin pasar por el listado
    public function reclacular_checksums_obsoletos(){

        flash('Función aún en testeo...')->warning()->important();
        return redirect('archivos');
        //Aún falta testeo

        try {
            if (Auth::user()->can(['Administrar Archivos', 'Ver Archivos'])){
                $archivos = Archivo::all();
                $recalculados = 0;
                foreach ($archivos as $archivo){
                    if (!$archivo->checkChecksum){
                        // Archivo con checksum viejo
                        $archivo->checksumRecalculate();
                        $recalculados++;
                    }
                }
                flash($recalculados . " checksums recalculados.")->info();
                return redirect('archivos');
            } else {
                flash('No tienes permiso para hacer eso.')->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('No existe el permiso "Administrar Archivos"')->error();
        }
    }

    public function listar_checksums_obsoletos(){
        try {
            if (Auth::user()->can(['Administrar Archivos', 'Ver Archivos'])){
                $archivos = Archivo::all();
                $checksums_obsoletos = [];
                foreach ($archivos as $archivo){
                    if (!$archivo->checkChecksum){
                        // Archivo con checksum viejo
                        $checksums_obsoletos[] = $archivo;
                    }
                }
                return view('archivo.checksums_obsoletos', compact('checksums_obsoletos'));
            } else {
                flash('No tienes permiso para hacer eso.')->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('No existe el permiso "Administrar Archivos"')->error();
        }
    }
}
