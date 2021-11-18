<?php

namespace App\Http\Controllers;

use App\Model\Archivo;
use Illuminate\Http\Request;
use DataTables;

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
            $archivos=Archivo::all();
	    if ($request->ajax()) {
	        return Datatables::of($archivos)->addIndexColumn()
	                ->addColumn('action', function($row){
                          $btn = '<a href="javascript:void(0)" class="btn btn-primary btn-sm">Ver</a>';
                         return $btn;
                        })
			->rawColumns(['action'])
			->make(true);
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
	    //
	    //
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
