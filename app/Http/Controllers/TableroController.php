<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MyDB;

class TableroController extends Controller
{
    // Primer tablero de informe por provincias.
    // Histograma radios segmentados.
    public function GraficoProvincias(Request $request) {
        $titulo = "Informe de Avances";
        if ($request->isMethod('post')) {
             $avances = MyDB::getAvancesProv();
             $data = json_encode ($avances);
             return response()->json($avances);
         }else{
             return view('grafico.show',['titulo'=>$titulo,'url_data'=>'prov']);
         }
    }
 
    // Segunta tablero de informe por avances.
    // Histograma .
    public function GraficoAvances(Request $request,Provincia $oProv=null) {
        if ($request->isMethod('post')) {
             $avances = MyDB::getAvances();
             $data = json_encode ($avances);
             return response()->json($avances);
         }else{
             return view('grafico.show',['provincia'=>$oProv,'url_data'=>'avances']);
         }
    }
    
}
