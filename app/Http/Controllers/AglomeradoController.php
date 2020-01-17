<?php

namespace App\Http\Controllers;

use App\Model\Aglomerado;
use Illuminate\Http\Request;

class AglomeradoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view('aglos');
    }

    public function aglosList()
    {
        $aglosQuery = Aglomerado::query();
        $codigo = (!empty($_GET["codigo"])) ? ($_GET["codigo"]) : ('');
        if($codigo){

         $aglosQuery->where('codigo','=',$codigo);
        }

        $aglos = $aglosQuery->select('*', \DB::raw('false carto,false listado'));
        return datatables()->of($aglos)
/*            ->addColumn('actions', function ($data) {
                return "<a class='btn btn-xs btn-success' href='/segmentar/$data->id'>Segmentar</a>";
            })
*/
            ->make(true);
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
     * @param  \App\Model\Aglomerado  $aglomerado
     * @return \Illuminate\Http\Response
     */
    public function show(Aglomerado $aglomerado)
    {
        //
        return view('agloview',['aglomerado' => $aglomerado]);
    }
    
    public function show_post(Aglomerado $aglomerado)
    {
        //
        return view('agloinfo',['aglomerado' => $aglomerado]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Aglomerado  $aglomerado
     * @return \Illuminate\Http\Response
     */
    public function edit(Aglomerado $aglomerado)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Aglomerado  $aglomerado
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Aglomerado $aglomerado)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Aglomerado  $aglomerado
     * @return \Illuminate\Http\Response
     */
    public function destroy(Aglomerado $aglomerado)
    {
        //
    }
}
