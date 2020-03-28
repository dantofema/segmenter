<?php

namespace App\Http\Controllers;

use App\Archivo;
use Illuminate\Http\Request;
use Auth;
use App\MyDB;
use App\Model\Aglomerado;
use App\Radio;

class SegmentacionController extends Controller
{
    
    public function index(Aglomerado $aglomerado)
    {
        $nodos = MyDB::getNodos($aglomerado->codigo);
        $edges = MyDB::getAdyacencias($aglomerado->codigo);
        $segmentacion_data = MyDB::getSegmentos($aglomerado->codigo);
        $segmentacion=[];
        foreach ($segmentacion_data as $data){ 
                $segmentacion[]=explode(',',str_replace('}','',str_replace('{','',$data->segmento)));
                }
        return view('grafo.show',['nodos'=>$nodos,'relaciones'=>$edges,'segmentacion'=>$segmentacion]);
    }
}
