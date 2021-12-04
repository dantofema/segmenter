<?php

namespace App\Http\Controllers;

use App\Model\Localidad;
use App\Model\Radio;
use App\MyDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

            }
            return view('localidad.radios',[
                        'localidad'=>$localidad,
                        'aglomerado'=>$localidad->aglomerado,
                        'radios'=>$localidad->radios,
                        'carto'=>null,'listado'=>null
                        ]);
    }

    public function segmenta_post(Localidad $localidad)
    {
            //
            $carto=$localidad->Carto;
            $listado=$localidad->Listado;
            $radios=$localidad->ComboRadios;
            return view('localidad.segmenta',['localidad' => $localidad,'carto' => $carto,'listado'=>$listado,'radios'=>$radios]);
    }

    public function run_segmentar(Request $request, Localidad $localidad)
    {
            if($request->optalgoritmo=='listado'){
                // Segmentacion x listado
                return $this->run_segmentar_equilibrado($request,$localidad);
            }elseif($request->optalgoritmo=='lados'){
                // Segmentacion x lado completo. Esto se realiza x radio o puedo hacerse para los radios del request.
                return $this->run_segmentar_x_lado($request,$localidad);
                }else{
                    $radio=null;
                    $result =
                    $this->run_segmentar_x_lado($request,$localidad,$radio,true);
                    Log::debug("Segmentacion lucky: ".$radio);
                    $excedidos =
                    MyDB::segmentos_excedidos($localidad->codigo,$request['vivs_max'],$radio);
                    $mensajes_excedidos = '';
                    foreach ($excedidos as $segmento){
                    $mensajes_excedidos .= 'El lado '.$segmento->lado.' de la manzana '.$segmento->mza.
                    ' debe segmentarse x listado, ya que tienen '.$segmento->vivs.' viviendas.
                                    ';
                        Log::debug(json_encode($segmento));
                    }
                    if ($radio){
                        if ($mensajes_excedidos!=''){
                        $radio->resultado.= '
    '.$mensajes_excedidos;
                        }
                        return app('App\Http\Controllers\SegmentacionController')->ver($localidad,$radio);
                    }
              return
              app('App\Http\Controllers\SegmentacionController')->index($localidad);
            }
    }

    public function run_segmentar_equilibrado(Request $request, Localidad $localidad)
    {
        if(MyDB::segmentar_equilibrado($localidad->codigo,$request['vivs_deseadas'])) {
           flash('Segmentado ('.$localidad->codigo.') '.$localidad->nombre.'!');
           return redirect()->route('ver-segmentacion', [$localidad]); 
        };

    }

    public function ver_segmentacion(Localidad $localidad)
    {
            $segmentacion=MyDB::segmentar_equilibrado_ver($localidad->codigo);
            $segmenta_data = json_encode ($segmentacion);
            return view('segmentacion.info',['segmentacion'=>$segmenta_data,'localidad'=>$localidad]);
    }

    public function ver_segmentacion_lados(Localidad $localidad)
    {
            $segmentacion=MyDB::segmentar_lados_ver($localidad->codigo);
            $segmenta_data = json_encode ($segmentacion);
            return view('segmentacion.lados_info',['segmentacion'=>$segmenta_data,'localidad'=>$localidad]);
    }

    public function ver_segmentacion_grafico(Request $request, Localidad $localidad)
    {
            $segmentacion=MyDB::segmentar_equilibrado_ver($localidad->codigo);
            $segmenta_data = json_encode ($segmentacion);
            if ($request->isMethod('post')) {
                return response()->json($segmentacion);
            }else{
                return view('segmentacion.grafico',['segmentacion'=>$segmenta_data,'localidad'=>$localidad]);
            }
    }

    public function ver_segmentacion_grafico_resumen(Request $request, Localidad $localidad)
    {
            $segmentacion=MyDB::segmentar_equilibrado_ver_resumen($localidad->codigo);
            $segmenta_data = json_encode ($segmentacion);
            if ($request->isMethod('post')) {
                return response()->json($segmentacion);
            }else{
                return view('segmentacion.grafico2',['segmentacion'=>$segmenta_data,'localidad'=>$localidad]);
            }
    }

    public function ver_segmentacion_lados_grafico_resumen(Request $request, Localidad $localidad)
    {
            $segmentacion=MyDB::segmentar_lados_ver_resumen($localidad->codigo);
            $segmenta_data = json_encode ($segmentacion);
            if ($request->isMethod('post')) {
                return response()->json($segmentacion);
            }else{
                return view('segmentacion.grafico2_lados',['segmentacion'=>$segmenta_data,'localidad'=>$localidad]);
            }
    }


    public function run_segmentar_x_lado(Request $request, Localidad
    $localidad,Radio &$radio=null,$lucky=null)
    {
      if($request->checkallradios){
        Log::debug('Se van a segmentar todos los radios del
        localidad: ('.$localidad->codigo.') '.$localidad->nombre );
      }
        elseif($request->radios){
           $radio= Radio::where('codigo',$request->radios)->first();
            if ($radio==null){
                $radio = new Radio(['id'=>null,'codigo'=>$request->radios,'nombre'=>'Nuevo: '.$request->radios]);
                Log::debug('No se encontró el radio: '.$request->radios.' Se
                crea temporalmente.');
            }
            if ($lucky!=true){
           $resultado = $radio->segmentar($localidad->codigo,
                                          $request['vivs_deseadas'],
                                          $request['vivs_max'],
                                          $request['vivs_min'],
                                          $request['mzas_indivisibles']);
                                          }else{
           $resultado = $radio->segmentarLucky($localidad->codigo,
                                          $request['vivs_deseadas'],
                                          $request['vivs_max'],
                                          $request['vivs_min'],
                                          $request['mzas_indivisibles']);
                                          }
            return  app('App\Http\Controllers\SegmentacionController')->ver($localidad,$radio);
        }else{
           flash('No selecciono ningún radio valido!');
           dd($request);
        }

    }

    public function ver_pxseg(Localidad $localidad)
    {
            $pxseg=MyDB::getPxSeg('e'.$localidad->codigo);
            $data = collect($pxseg); //json_encode ($pxseg);
            return view('segmentacion.pxseg',['pxseg'=>$data,'localidad'=>$localidad]);
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
