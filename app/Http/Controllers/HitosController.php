<?php

namespace App\Http\Controllers;

use App\Archivo;
use Illuminate\Http\Request;
use Auth;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\MyDB;
use App\Listado;
use App\Imports\CsvImport;
use Maatwebsite\Excel;
use App\Model\Aglomerado;
use App\Model\Provincia;
use Illuminate\Support\Facades\Log;

class HitosController extends Controller
{
    private $epsgs=[];

    public function __construct()
    {
        $segmenta_auto=false;
        $this->middleware('auth');
        $this->epsgs['22182']='(EPSG:22182) POSGAR 94/Argentina 2 - San Juan, Mendoza, Neuquén, Chubut, Santa Cruz y Tierra del Fuego...';
        $this->epsgs['22183']='(EPSG:22183) POSGAR 94/Argentina 3 - Jujuy, Salta, Tucuman, Catamárca, La Rioja, San Luis, La Pampa y Río Negro';
        $this->epsgs['22184']='(EPSG:22184) POSGAR 94/Argentina 4 - Santiago del Estero y Córdoba';
        $this->epsgs['22185']='(EPSG:22185) POSGAR 94/Argentina 5 - Formosa, Chaco, Santa Fe, Entre Ríos y Buenos Aires';
        $this->epsgs['22186']='(EPSG:22186) POSGAR 94/Argentina 6 - Corrientes';
        $this->epsgs['22187']='(EPSG:22187) POSGAR 94/Argentina 7 - Misiones';
        $this->epsgs['8333']='(SR-ORG:8333) Gauss Krugger BA';
    }

    public function index()
    {
    //	  $data['whoami'] = exec('whoami');
	    $data=null;
	    //dd(App()->make('App\Model\Radio'));
        return view('hitos/upload',['data' => $data,'epsgs'=> $this->epsgs]);
    }

    public function store(Request $request)
    {
    $AppUser= Auth::user();
    $data = [];
    $segmenta_auto=false;
    $epsg_id = $request->input('epsg_id')?$request->input('epsg_id'):'22183';
    $data['epsg']['id']=$epsg_id;
    flash('SRS: '.$data['epsg']['id']);


    if ($request->hasFile('hitos_shp')) {
        if ($request->file('hitos_shp')->isValid() or true) {
            $data['file']['hitos_shp_msg'] = "Subió una base geográfica ";
            $original_name = $request->hitos_shp->getClientOriginalName();
            $data['file']['hitos_shp_msg'] .= " y nombre original: ".$original_name;
            $original_extension = strtolower($request->hitos_shp->getClientOriginalExtension());
            $data['file']['hitos_shp_msg'] .= ". Extension original: ".$original_extension;
            flash($data['file']['hitos_shp_msg']);

        if ($original_extension == 'shp'){
            $random_name='t_'.$request->hitos_shp->hashName();
            $data['file']['hitos_shp'] = $request->hitos_shp->storeAs('segmentador', $random_name.'.'.$request->hitos_shp->getClientOriginalExtension());
            if ($request->hasFile('hitos_shx')) {
                $data['file']['hitos_shx'] = $request->hitos_shx->storeAs('segmentador', $random_name.'.'.$request->hitos_shx->getClientOriginalExtension());
            }
            if ($request->hasFile('hitos_prj')) {
                $data['file']['hitos_prj'] = $request->hitos_prj->storeAs('segmentador', $random_name.'.'.$request->hitos_prj->getClientOriginalExtension());
            }
            if ($request->hasFile('hitos_dbf')) {
                $data['file']['hitos_dbf'] = $request->hitos_dbf->storeAs('segmentador', $random_name.'.'.$request->hitos_dbf->getClientOriginalExtension());
            }

            $process = Process::fromShellCommandline('echo "$tiempo: $usuario_name ($usuario_id) -> $log" >> archivos.log');
            $process->run(null, ['log' => "Archivo: ".$original_name." subido como: ".$data['file']['hitos_shp'],
                                 'usuario_id' => $AppUser->id,
                                 'usuario_name' => $AppUser->name,
                                 'tiempo' => date('Y-m-d H:i:s')]);
                        
            $codaglo=isset($codaglo)?$codaglo:'test'; //$random_name;
            MyDB::createSchema($codaglo);

            if ($epsg_id=='8333'){
                Log::debug('Proyeccion de CABA en '.$codaglo.', con SRID: '.$epsg_id);
                // USO .prj 8333.prj
                $hitos_prj_file='./app/developer_docs/8333.prj';
                    $epsg_def='epsg:'.$epsg_id;
                    $epsg_def='+proj=tmerc +lat_0=-34.6297166 +lon_0=-58.4627 +k=1 +x_0=100000 +y_0=100000 +ellps=intl +units=m +no_defs';
                    $srs_name='sr-org:8333';

                $processOGR2OGR =
                Process::fromShellCommandline('(/usr/bin/ogr2ogr -f \
                "PostgreSQL" PG:"dbname=$db host=$host user=$user port=$port \
                active_schema=e$e00 password=$pass" --config PG_USE_COPY YES \
                -lco OVERWRITE=YES --config OGR_TRUNCATE YES -dsco \
                PRELUDE_STATEMENTS="SET client_encoding TO latin1;CREATE SCHEMA \
                IF NOT EXISTS e$e00;" -dsco active_schema=e$e00 -lco \
                PRECISION=NO -lco SCHEMA=e$e00 \
                 -s_srs epsg:$epsg -t_srs epsg:$epsg \
                -nln arc \
                -skipfailures \
                -overwrite $file )');
                $processOGR2OGR->setTimeout(3600);
                $processOGR2OGR->run(null, ['epsg'=>$epsg_def,'file' => storage_path().'/app/'.$data['file']['hitos_shp'],'e00'=>$codaglo,'db'=>Config::get('database.connections.pgsql.database'),'host'=>Config::get('database.connections.pgsql.host'),'user'=>Config::get('database.connections.pgsql.username'),'pass'=>Config::get('database.connections.pgsql.password'),'port'=>Config::get('database.connections.pgsql.port')]);
            }else{
                $processOGR2OGR = Process::fromShellCommandline('/usr/bin/ogr2ogr -f "PostgreSQL" PG:"dbname=$db host=$host user=$user port=$port active_schema=e$e00 password=$pass" --config PG_USE_COPY YES -lco OVERWRITE=YES --config OGR_TRUNCATE YES -dsco PRELUDE_STATEMENTS="SET client_encoding TO latin1;CREATE SCHEMA IF NOT EXISTS e$e00;" -dsco active_schema=e$e00 -lco PRECISION=NO -lco SCHEMA=e$e00 -s_srs epsg:$epsg -t_srs epsg:$epsg -nln arc -overwrite $file ');
                $processOGR2OGR->setTimeout(3600);
                $processOGR2OGR->run(null, ['epsg' => $epsg_id, 'file' => storage_path().'/app/'.$data['file']['hitos_shp'],'e00'=>$codaglo,'db'=>Config::get('database.connections.pgsql.database'),'host'=>Config::get('database.connections.pgsql.host'),'user'=>Config::get('database.connections.pgsql.username'),'pass'=>Config::get('database.connections.pgsql.password'),'port'=>Config::get('database.connections.pgsql.port')]);

            }
            if (!$processOGR2OGR->isSuccessful()) {
                $epsg_def=isset($epsg_def)?$epsg_def:'No definido';
                dd($processOGR2OGR,'epsg '.$epsg_id,'epsg_def '.$epsg_def.
                'file '.storage_path().'/app/'.$data['file']['hitos_shp'],'e00 '.$codaglo);
                throw new ProcessFailedException($processOGR2OGR);
            }
            MyDB::agregarsegisegd($codaglo);

        }elseif ($original_extension == 'e00'){
            $random_name='t_'.$request->hitos_shp->hashName();
            $data['file']['hitos_shp'] = $request->hitos_shp->storeAs('segmentador', $random_name.'.'.$request->hitos_shp->getClientOriginalExtension());
            $process = Process::fromShellCommandline('echo "E00: $name" >> archivos.log');
            $process->run(null, ['name' => "Archivo: ".$original_name." subido como: ".$data['file']['hitos_shp']]);
            $processOGR = Process::fromShellCommandline('/usr/bin/ogrinfo -so $file ARC');
            $processOGR->run(null, ['file' => storage_path().'/app/'.$data['file']['hitos_shp']]);
            $data['file']['e00_info'] = $processOGR->getOutput();

            $original_name = substr($original_name,1,4);
            $codaglo=isset($codaglo)?$codaglo:$original_name;
            MyDB::createSchema($codaglo);

            $processOGR2OGR = Process::fromShellCommandline('/usr/bin/ogr2ogr -f "PostgreSQL" PG:"dbname=$db host=$host user=$user port=$port active_schema=e$e00 password=$pass port=$port" --config PG_USE_COPY YES -lco OVERWRITE=YES --config OGR_TRUNCATE YES -dsco PRELUDE_STATEMENTS="SET client_encoding TO latin1;CREATE SCHEMA IF NOT EXISTS e$e00;" -dsco active_schema=e$e00 -lco PRECISION=NO -lco SCHEMA=e$e00 -s_srs epsg:$epsg -t_srs epsg:$epsg -skipfailures -addfields -overwrite $file ARC');
            $processOGR2OGR->setTimeout(3600);
            $processOGR2OGR->run(null, ['epsg' => $epsg_id, 'file' => storage_path().'/app/'.$data['file']['hitos_shp'],'e00'=>$codaglo,'db'=>Config::get('database.connections.pgsql.database'),'host'=>Config::get('database.connections.pgsql.host'),'user'=>Config::get('database.connections.pgsql.username'),'pass'=>Config::get('database.connections.pgsql.password'),'port'=>Config::get('database.connections.pgsql.port')]);
            //		dd($processOGR2OGR);	
            $processOGR2OGR_lab = Process::fromShellCommandline('/usr/bin/ogr2ogr -f "PostgreSQL" PG:"dbname=$db host=$host user=$user port=$port active_schema=e$e00 password=$pass" --config PG_USE_COPY YES -lco OVERWRITE=YES --config OGR_TRUNCATE YES -dsco PRELUDE_STATEMENTS="SET client_encoding TO latin1;CREATE SCHEMA IF NOT EXISTS e$e00;" -dsco active_schema=e$e00 -lco PRECISION=NO -lco SCHEMA=e$e00 -s_srs epsg:$epsg -t_srs epsg:$epsg -skipfailures -addfields -overwrite $file LAB');
            $processOGR2OGR_lab->setTimeout(3600);
            $processOGR2OGR_lab->run(null, ['epsg' => $epsg_id, 'file' => storage_path().'/app/'.$data['file']['hitos_shp'],'e00'=>$codaglo,'db'=>Config::get('database.connections.pgsql.database'),'host'=>Config::get('database.connections.pgsql.host'),'user'=>Config::get('database.connections.pgsql.username'),'pass'=>Config::get('database.connections.pgsql.password'),'port'=>Config::get('database.connections.pgsql.port')]);
            //dd($processOGR2OGR_lab->getErrorOutput());
            flash($data['file']['ogr2ogr_lab'] = $processOGR2OGR_lab->getErrorOutput().'<br />'.$processOGR2OGR_lab->getOutput())->important();
            flash($data['file']['ogr2ogr'] = $processOGR2OGR->getErrorOutput().'<br />'.$processOGR2OGR->getOutput())->important();
            MyDB::agregarsegisegd($codaglo);
        }else {//dd($request->file('hitos_shp')); 
            flash('File geo not valid')->error()->important();
        }
        if (isset($codaglo)){
            MyDB::juntaListadoGeom('e'.$codaglo);
            if($segmenta_auto) {
                    MyDB::segmentar_equilibrado($codaglo,36);
                    flash('Segmentado automáticamente a 36 viviendas x segmento')->important();
                    flash('Resultado: '.MyDB::juntar_segmentos('e'.$codaglo));
            }
        }
    }
   }


     if (Archivo::cargar($request, Auth::user())) {
        return view('segmenter/index', ['data' => $data,'epsgs'=> $this->epsgs]);
     } else {
        echo "Error en el modelo cargar";
     }
    }
}
