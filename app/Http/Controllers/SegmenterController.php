<?php

namespace App\Http\Controllers;

use App\Archivo;
use Illuminate\Http\Request;
use Auth;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\MyDB;
use App\Listado;

class SegmenterController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
  $epsgs['22182']='EPSG:22182';
  $epsgs['22183']='EPSG:22183';
  $epsgs['22184']='EPSG:22184';
  $epsgs['22185']='EPSG:22185';
  $epsgs['22186']='EPSG:22186';
  $epsgs['22187']='EPSG:22187';
	$data['whoami'] = exec('whoami');
        return view('segmenter/index',['data' => $data,'epsgs'=> $epsgs]);
    }
    public function store(Request $request)
    {
  $epsgs['22182']='EPSG:22182';
  $epsgs['22183']='EPSG:22183';
  $epsgs['22184']='EPSG:22184';
  $epsgs['22185']='EPSG:22185';
  $epsgs['22186']='EPSG:22186';
  $epsgs['22187']='EPSG:22187';

        $data = [];
        if ($request->hasFile('shp')) {
            if ($request->file('shp')->isValid()) {
		$data['file']['shp_msg'] = "Subió un shape ";
	        $extension = $request->shp->extension();
		$data['file']['shp_msg'] .= " con extenión: ".$extension;
	        $original_name = $request->shp->getClientOriginalName();
		$data['file']['shp_msg'] .= ". y nombre original: ".$original_name;
                $original_extension = $request->shp->getClientOriginalExtension();
		$data['file']['shp_msg'] .= ". y extension  original: ".$original_extension;

		$data['file']['shp'] = $request->shp->storeAs('segmentador', $request->shp->hashName().'.'.$request->shp->getClientOriginalExtension());

		$epsg_id = $request->input('epsg_id');

		if ($original_extension == 'shp'){
			$process = Process::fromShellCommandline('echo "$name"  >> archivos.log');
			$process->run(null, ['name' => "Archivo: ".$request->shp->getClientOriginalName()." subido como: ".$data['file']['shp']]);
//	            exec("echo 'Archivo: ".$request->shp->getClientOriginalName()." subido como: ".$data['file']['shp']."' >> archivos.log");

		}elseif ($original_extension == 'e00'){
			$process = Process::fromShellCommandline('echo "E00: $name"  >> archivos.log');
			$process->run(null, ['name' => "Archivo: ".$request->shp->getClientOriginalName()." subido como: ".$data['file']['shp']]);
//			$processOGR = new Process(['/usr/bin/ogrinfo']);
			$processOGR = Process::fromShellCommandline('/usr/bin/ogrinfo -so $file ARC');
			$processOGR->run(null, ['file' => storage_path().'/app/'.$data['file']['shp']]);
			$data['file']['e00_info'] = $processOGR->getOutput();

			$codaglo = substr($original_name,1,4);
//			$data['file']['schema'] = DB::statement('CREATE SCHEMA IF NOT EXISTS e'.$codaglo);			
		
			MyDB::createSchema($codaglo);
			//Debugbar::info("Codaglo: ".$codaglo);

			
			$processOGR2OGR_lab = Process::fromShellCommandline('/usr/bin/ogr2ogr -f "PostgreSQL" PG:"dbname=$db host=$host user=$user active_schema=e$e00" --config PG_USE_COPY YES -lco OVERWRITE=YES --config OGR_TRUNCATE YES -dsco PRELUDE_STATEMENTS="SET client_encoding TO latin1;CREATE SCHEMA IF NOT EXISTS e$e00;" -dsco active_schema=e$e00 -lco PRECISION=NO -lco SCHEMA=e$e00 -s_srs epsg:$epsg -t_srs epsg:$epsg -skipfailures -update -append $file LAB');
			$processOGR2OGR_lab->run(null, ['epsg' => $epsg_id, 'file' => storage_path().'/app/'.$data['file']['shp'],'e00'=>$codaglo,'db'=>Config::get('database.connections.pgsql.database'),'host'=>Config::get('database.connections.pgsql.host'),'user'=>Config::get('database.connections.pgsql.username')]);
			$data['file']['ogr2ogr_lab'] = $processOGR2OGR_lab->getErrorOutput().'<br />'.$processOGR2OGR_lab->getOutput();

			$processOGR2OGR = Process::fromShellCommandline('/usr/bin/ogr2ogr -f "PostgreSQL" PG:"dbname=$db host=$host user=$user active_schema=e$e00" --config PG_USE_COPY YES -lco OVERWRITE=YES --config OGR_TRUNCATE YES -dsco PRELUDE_STATEMENTS="SET client_encoding TO latin1;CREATE SCHEMA IF NOT EXISTS e$e00;" -dsco active_schema=e$e00 -lco PRECISION=NO -lco SCHEMA=e$e00 -s_srs epsg:$epsg -t_srs epsg:$epsg -skipfailures -update -append $file ARC');
			$processOGR2OGR->run(null, ['epsg' => $epsg_id, 'file' => storage_path().'/app/'.$data['file']['shp'],'e00'=>$codaglo,'db'=>Config::get('database.connections.pgsql.database'),'host'=>Config::get('database.connections.pgsql.host'),'user'=>Config::get('database.connections.pgsql.username')]);
			$data['file']['ogr2ogr'] = $processOGR2OGR->getErrorOutput().'<br />'.$processOGR2OGR->getOutput();

		}
	    }
        }
	if ($request->hasFile('shx')) {
            $data['file']['shx'] = $request->shx->store('segmentador');
        }
        if ($request->hasFile('prj')) {
            $data['file']['prj'] = $request->prj->store('segmentador');
        }
        if ($request->hasFile('dbf')) {
            $data['file']['dbf'] = $request->dbf->store('segmentador');
        }
        if ($request->hasFile('c1')) {
            $data['file']['c1'] = $request->c1->store('segmentador');
	    $original_extension = $request->c1->getClientOriginalExtension();
	    $original_name = $request->c1->getClientOriginalName();
		if ($original_extension == 'csv'){
			$data['file']['csv_info'] = 'Se Cargo un csv.';
			$process = Process::fromShellCommandline('echo "CSV: $original_name"  >> archivos.log');
//			$process = Process::fromShellCommandline('
			$data['file']['csv_detail'] = Listado::cargar_csv( storage_path().'/app/'.$data['file']['c1']);
			 return view('listado/all', ['listado' => $data['file']['csv_detail'],'epsgs'=> $epsgs]);
		}elseif ($original_extension == 'dbf'){
                        $data['file']['csv_info'] = 'Se Cargo un DBF.';
                        $process = Process::fromShellCommandline('echo "DBF: $original_name"  >> archivos.log');
//                      $process = Process::fromShellCommandline('
                }else{$data['file']['csv_info'] = 'Se Cargo un archivo de formato no esperado!';}
        }

        if (Archivo::cargar($request, Auth::user())) {
            return view('segmenter/index', ['data' => $data,'epsgs'=> $epsgs]);
        } else {
            echo "Error en el modelo cargar";
        }
    }
}
