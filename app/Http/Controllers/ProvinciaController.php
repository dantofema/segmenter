<?php

namespace App\Http\Controllers;

use App\Model\Provincia;
use App\Model\Departamento;
use Illuminate\Http\Request;
use Redirect,Response,DB,Config;
use Datatables;


class ProvinciaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		//
//        $provincias = Provincia::withCount('departamentos')->orderBy('codigo','asc')->get();
//        $category = Departamento::find(3);
// dd($product);

// $provincia = $product->departamentos;
//dd($provincias);
     return view('provs');
    }


    public function provsList()
    {   
        $provsQuery = Provincia::query();
        $codigo = (!empty($_GET["codigo"])) ? ($_GET["codigo"]) : ('');
        if($codigo){

         $provsQuery->where('codigo','=',$codigo);
        }

/* 
        $start_date = (!empty($_GET["start_date"])) ? ($_GET["start_date"]) : ('');
        $end_date = (!empty($_GET["end_date"])) ? ($_GET["end_date"]) : ('');
 
        if($start_date && $end_date){
     
         $start_date = date('Y-m-d', strtotime($start_date));
         $end_date = date('Y-m-d', strtotime($end_date));
          
//         $provsQuery->whereRaw("date(provs.created_at) >= '" . $start_date . "' AND date(provs.created_at) <= '" . $end_date . "'");
        }
*/
        $provs = $provsQuery->select('*')->withCount('departamentos');
        return datatables()->of($provs)
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
     * @param  \App\Model\Provincia  $provincia
     * @return \Illuminate\Http\Response
     */
    public function show(Provincia $provincia)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Provincia  $provincia
     * @return \Illuminate\Http\Response
     */
    public function edit(Provincia $provincia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Provincia  $provincia
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Provincia $provincia)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Provincia  $provincia
     * @return \Illuminate\Http\Response
     */
    public function destroy(Provincia $provincia)
    {
        //
    }
}
