<?php

namespace App\Http\Controllers;

use App\Model\Localidad;
use Illuminate\Http\Request;

class LocalidadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //  
            $localidades=Localidad::all();
            return $localidades;
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
     * @param  \App\Model\Localidad  $localidad
     * @return \Illuminate\Http\Response
     */
    public function show(Localidad $localidad)
    {
        //p
            if ($localidad->departamentos()->count()>1) {
                flash($localidad->nombre.' MULTIDEPARTAMENTAL ')->success();
            return view('localidad.deptos_view',[
                        'localidad'=>$localidad,
                        'deptos'=>$localidad->departamentos()->get(),
                        'carto'=>null,'listado'=>null
                        ]);

                return($localidad->departamentos()->get( 
                        //['codigo','nombre']
                        ));
            }
//            dd($localidad->radios->count());
            return view('localidad.radios',[
                        'localidad'=>$localidad,
                        'aglomerado'=>$localidad->aglomerado,
                        'radios'=>$localidad->radios,
                        'carto'=>null,'listado'=>null
                        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Localidad  $localidad
     * @return \Illuminate\Http\Response
     */
    public function edit(Localidad $localidad)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Localidad  $localidad
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Localidad $localidad)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Localidad  $localidad
     * @return \Illuminate\Http\Response
     */
    public function destroy(Localidad $localidad)
    {
        //
    }
}
