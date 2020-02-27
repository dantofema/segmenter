<?php

namespace App\Http\Controllers;

use App\Model\Aglomerado;
use App\Model\Radio;
use Illuminate\Http\Request;
use App\MyDB;

class AglomeradoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Listado de Aglomerados.
        return view('aglos');
    }

    public function aglosList()
    {
        $aglosQuery = Aglomerado::query();
        $codigo = (!empty($_REQUEST["codigo"])) ? ($_REQUEST["codigo"]) : ('');
        if($codigo){
         $aglosQuery->where('codigo','=',$codigo);
        }
        $aglos = $aglosQuery->select('*', \DB::raw('false carto,false listado,false segmentadolistado,false segmentadolados'))
                            ->withCount(['localidades']);
//        $carto=$aglos->Carto;
//        $listado=$aglos->Listado;
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
        return view('aglo.view',['aglomerado' => $aglomerado]);
    }
    
    public function show_post(Aglomerado $aglomerado)
    {
        //
        return view('aglo.info',['aglomerado' => $aglomerado]);
    }

    public function segmenta_post(Aglomerado $aglomerado)
    {
        //
        $carto=$aglomerado->Carto;
        $listado=$aglomerado->Listado;
        $radios=$aglomerado->Radios;
        return view('aglo.segmenta',['aglomerado' => $aglomerado,'carto' => $carto,'listado'=>$listado,'radios'=>$radios]);
    }

    public function run_segmentar(Request $request, Aglomerado $aglomerado)
    {
        if($request->optalgoritmo=='listado'){
            // Segmentacion x listado
            return $this->run_segmentar_equilibrado($request,$aglomerado); 
        }elseif($request->optalgoritmo=='lados'){
            // Segmentacion x lado completo. Esto se realiza x radio o puedo hacerse para los radios del request.
            return $this->run_segmentar_x_lado($request,$aglomerado); 
            }else{
             return 'Not today!';
            }

    }
    public function run_segmentar_equilibrado(Request $request, Aglomerado $aglomerado)
    {
        if(MyDB::segmentar_equilibrado($aglomerado->codigo,$request['vivs_deseadas'])) {
           flash('Segmentado ('.$aglomerado->codigo.') '.$aglomerado->nombre.'!'); 
           return redirect()->route('ver-segmentacion', [$aglomerado]); //$this->ver_segmentacion($aglomerado);
        };
        
    }

    public function ver_segmentacion(Aglomerado $aglomerado)
    {
            $segmentacion=MyDB::segmentar_equilibrado_ver($aglomerado->codigo);
            $segmenta_data = json_encode ($segmentacion);
            return view('segmentacion.info',['segmentacion'=>$segmenta_data,'aglomerado'=>$aglomerado]);
    }

    public function ver_segmentacion_lados(Aglomerado $aglomerado)
    {
            $segmentacion=MyDB::segmentar_lados_ver($aglomerado->codigo);
            $segmenta_data = json_encode ($segmentacion);
            return view('segmentacion.lados_info',['segmentacion'=>$segmenta_data,'aglomerado'=>$aglomerado]);
    }

    public function run_segmentar_x_lado(Request $request, Aglomerado $aglomerado)
    {
        if($request->radios){
           $radio= Radio::where('codigo',$request->radios)->first();
           $radio->segmentar($aglomerado->codigo,$request['vivs_deseadas'],$request['vivs_max'],$request['vivs_min'],$request['mzas_indivisibles']);
        
        }else{
           flash('No selecciono ningún radio valido!'); 
           dd($request);
        }
            //return view('segmentacion.info',['segmentacion'=>$segmentacion]);
//        };
        
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
