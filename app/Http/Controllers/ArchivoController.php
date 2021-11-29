<?php

namespace App\Http\Controllers;

use App\Model\Archivo;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Auth;

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
            $AppUser=Auth::user();
	    $archivos=Archivo::where('user_id',$AppUser->id);
	    if ($request->ajax()) {
	        return Datatables::of($archivos)->addIndexColumn()
	                ->addColumn('action', function($row){
                          $btn = '<a href="javascript:void(0)" class="btn btn-primary btn-sm">Ver</a>';
                         return $btn;
                        })
			->rawColumns(['action'])
			->make(true);
	    }
      }else{
	      $archivos= null;
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
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function show(Archivo $archivo)
    {
	//
	return $archivo->load('user');
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
	    // Borro el archivo del storage
	    // 
            $file= $archivo->nombre;
            if(Storage::delete($file)){
		    Log::info('Se borró el archivo: '.$archivo->nombre_original);
	    }else{
		    Log::error('NO se borró el archivo: '.$file);
	    }
	    $archivo->delete();
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
}
