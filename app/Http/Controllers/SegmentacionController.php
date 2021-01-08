<?php

namespace App\Http\Controllers;

use App\Archivo;
use Illuminate\Http\Request;
use Auth;
use App\MyDB;
use App\Model\Aglomerado;
use App\Model\Radio;


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
        return
        view('segmentacion.grafico2',['nodos'=>$nodos,'relaciones'=>$edges,'segmentacion'=>$segmentacion,'aglomerado'=>$aglomerado]);
    }
    
    public function ver_grafo(Aglomerado $aglomerado,Radio $radio)
    {
//        $aglomerado=$radio->getAglomerado();
//        $radio=$aglomerado->Localidades()->Radios()->first();
        $filtro_radio = substr($radio->codigo,0,5).'___'.substr($radio->codigo,5,4);
        $nodos = MyDB::getNodos($aglomerado->codigo,$filtro_radio);
        $edges = MyDB::getAdyacencias($aglomerado->codigo,$filtro_radio);
        $segmentacion_data = MyDB::getSegmentos($aglomerado->codigo,$filtro_radio);
        $segmentacion=[];
        foreach ($segmentacion_data as $data){ 
                $segmentacion[]=explode(',',str_replace('}','',str_replace('{','',$data->segmento)));
                }
        $segmentacion_listado=MyDB::segmentar_equilibrado_ver($aglomerado->codigo,100,$radio);
//        $segmentacion_data_listado = json_encode ($segmentacion_listado, JSON_PRETTY_PRINT);

        $radio->refresh();
        return
        view('grafo.show',['nodos'=>$nodos,'relaciones'=>$edges,'segmentacion'=>$segmentacion,'segmentacion_data_listado'=>$segmentacion_listado,'aglomerado'=>$aglomerado,'radio'=>$radio]);
    }

    public function ver_grafico(Aglomerado $aglomerado,Radio $radio) {

                $segmentacion=MyDB::segmentar_lados_ver_resumen($aglomerado->codigo);
                return view('segmentacion.grafico2_lados',['segmentacion'=>$segmenta_data,'aglomerado'=>$aglomerado]);
    }
}
