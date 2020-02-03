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
use App\Imports\CsvImport;
use Maatwebsite\Excel;

class SegmenterController extends Controller
{
  private $epsgs=[];

    public function __construct()
    {
        $this->middleware('auth');
        $this->epsgs['22182']='EPSG:22182';
        $this->epsgs['22183']='EPSG:22183';
        $this->epsgs['22184']='EPSG:22184';
        $this->epsgs['22185']='EPSG:22185';
        $this->epsgs['22186']='EPSG:22186';
        $this->epsgs['22187']='EPSG:22187';
    }

    public function index()
    {
	    $data['whoami'] = exec('whoami');
        return view('segmenter/index',['data' => $data,'epsgs'=> $this->epsgs]);
    }
    public function store(Request $request)
    {
        $AppUser= Auth::user();
        $data = [];
        $epsg_id = $request->input('epsg_id');
        if ($request->hasFile('shp')) {
            if ($request->file('shp')->isValid()) {
        		$data['file']['shp_msg'] = "Subió un shape ";
	            $extension = $request->shp->extension();
        		$data['file']['shp_msg'] .= " con extensión: ".$extension;
	            $original_name = $request->shp->getClientOriginalName();
        		$data['file']['shp_msg'] .= ". y nombre original: ".$original_name;
                $original_extension = $request->shp->getClientOriginalExtension();
    		    $data['file']['shp_msg'] .= ". y extension  original: ".$original_extension;
                $original_name = $request->shp->getClientOriginalName();

		if ($original_extension == 'shp'){
    	    $random_name='t_'.$request->shp->hashName();
   	    	$data['file']['shp'] = $request->shp->storeAs('segmentador', $random_name.'.'.$request->shp->getClientOriginalExtension());
        	if ($request->hasFile('shx')) {
                $data['file']['shx'] = $request->shx->storeAs('segmentador', $random_name.'.'.$request->shx->getClientOriginalExtension());
            }
            if ($request->hasFile('prj')) {
                $data['file']['prj'] = $request->prj->storeAs('segmentador', $random_name.'.'.$request->prj->getClientOriginalExtension());
            }
            if ($request->hasFile('dbf')) {
                $data['file']['dbf'] = $request->dbf->storeAs('segmentador', $random_name.'.'.$request->dbf->getClientOriginalExtension());
            }

			$process = Process::fromShellCommandline('echo "$name"  >> archivos.log');
			$process->run(null, ['name' => "Archivo: ".$original_name." subido como: ".$data['file']['shp']]);
			
            $codaglo = substr($original_name,1,4);
			MyDB::createSchema($codaglo);
			
            $processOGR2OGR = Process::fromShellCommandline('/usr/bin/ogr2ogr -f "PostgreSQL" PG:"dbname=$db host=$host user=$user active_schema=e$e00 password=$pass" --config PG_USE_COPY YES -lco OVERWRITE=YES --config OGR_TRUNCATE YES -dsco PRELUDE_STATEMENTS="SET client_encoding TO latin1;CREATE SCHEMA IF NOT EXISTS e$e00;" -dsco active_schema=e$e00 -lco PRECISION=NO -lco SCHEMA=e$e00 -s_srs epsg:$epsg -t_srs epsg:$epsg -nln arc -skipfailures -update -overwrite $file ');
            $processOGR2OGR->setTimeout(3600);
			$processOGR2OGR->run(null, ['epsg' => $epsg_id, 'file' => storage_path().'/app/'.$data['file']['shp'],'e00'=>$codaglo,'db'=>Config::get('database.connections.pgsql.database'),'host'=>Config::get('database.connections.pgsql.host'),'user'=>Config::get('database.connections.pgsql.username'),'pass'=>Config::get('database.connections.pgsql.password')]);

		}elseif ($original_extension == 'e00'){
    	    $random_name='t_'.$request->shp->hashName();
   	    	$data['file']['shp'] = $request->shp->storeAs('segmentador', $random_name.'.'.$request->shp->getClientOriginalExtension());
//            dd($request);
			$process = Process::fromShellCommandline('echo "E00: $name"  >> archivos.log');
			$process->run(null, ['name' => "Archivo: ".$original_name." subido como: ".$data['file']['shp']]);
//			$processOGR = new Process(['/usr/bin/ogrinfo']);
			$processOGR = Process::fromShellCommandline('/usr/bin/ogrinfo -so $file ARC');
			$processOGR->run(null, ['file' => storage_path().'/app/'.$data['file']['shp']]);
			$data['file']['e00_info'] = $processOGR->getOutput();

			$codaglo = substr($original_name,1,4);
			MyDB::createSchema($codaglo);
			
			$processOGR2OGR = Process::fromShellCommandline('/usr/bin/ogr2ogr -f "PostgreSQL" PG:"dbname=$db host=$host user=$user active_schema=e$e00 password=$pass" --config PG_USE_COPY YES -lco OVERWRITE=YES --config OGR_TRUNCATE YES -dsco PRELUDE_STATEMENTS="SET client_encoding TO latin1;CREATE SCHEMA IF NOT EXISTS e$e00;" -dsco active_schema=e$e00 -lco PRECISION=NO -lco SCHEMA=e$e00 -s_srs epsg:$epsg -t_srs epsg:$epsg -skipfailures -update -overwrite $file ARC');
            $processOGR2OGR->setTimeout(3600);
			$processOGR2OGR->run(null, ['epsg' => $epsg_id, 'file' => storage_path().'/app/'.$data['file']['shp'],'e00'=>$codaglo,'db'=>Config::get('database.connections.pgsql.database'),'host'=>Config::get('database.connections.pgsql.host'),'user'=>Config::get('database.connections.pgsql.username'),'pass'=>Config::get('database.connections.pgsql.password')]);
			
            $processOGR2OGR_lab = Process::fromShellCommandline('/usr/bin/ogr2ogr -f "PostgreSQL" PG:"dbname=$db host=$host user=$user active_schema=e$e00 password=$pass" --config PG_USE_COPY YES -lco OVERWRITE=YES --config OGR_TRUNCATE YES -dsco PRELUDE_STATEMENTS="SET client_encoding TO latin1;CREATE SCHEMA IF NOT EXISTS e$e00;" -dsco active_schema=e$e00 -lco PRECISION=NO -lco SCHEMA=e$e00 -s_srs epsg:$epsg -t_srs epsg:$epsg -skipfailures -update -overwrite $file LAB');
            $processOGR2OGR_lab->setTimeout(3600);
			$processOGR2OGR_lab->run(null, ['epsg' => $epsg_id, 'file' => storage_path().'/app/'.$data['file']['shp'],'e00'=>$codaglo,'db'=>Config::get('database.connections.pgsql.database'),'host'=>Config::get('database.connections.pgsql.host'),'user'=>Config::get('database.connections.pgsql.username'),'pass'=>Config::get('database.connections.pgsql.password')]);
			$data['file']['ogr2ogr_lab'] = $processOGR2OGR_lab->getErrorOutput().'<br />'.$processOGR2OGR_lab->getOutput();

			$data['file']['ogr2ogr'] = $processOGR2OGR->getErrorOutput().'<br />'.$processOGR2OGR->getOutput();

		}
	    }
        }
        if ($request->hasFile('c1')) {
          $random_name='t_'.$request->c1->hashName();
          $data['file']['c1'] = $request->c1->storeAs('segmentador', $random_name); //.'.'.$request->c1->getClientOriginalExtension());
	      $original_extension = $request->c1->getClientOriginalExtension();
	      $original_name = $request->c1->getClientOriginalName();
		  if ($original_extension == 'csv'){
			$data['file']['csv_info'] = 'Se Cargo un csv.';
            $process = Process::fromShellCommandline('echo "C1 CSV: $name"  >> archivos.log');
   			$process->run(null, ['name' => "Archivo: ".$original_name." subido como: ".$data['file']['c1']]);
/*
			$data['file']['csv_detail'] = Listado::cargar_csv( storage_path().'/app/'.$data['file']['c1']);
			 return view('listado/all', ['listado' => $data['file']['csv_detail'],'epsgs'=> $epsgs]);
*/
			$import = new CsvImport;
			Excel::import($import,  storage_path().'/app/'.$data['file']['c1']);
		//	dd('Row count: ' . $import->getRowCount()); 
			return view('listado/all', ['listado' => $data['file']['csv_detail'],'epsgs'=> $epsgs]);

		   }elseif ($original_extension == 'dbf'){
                        $data['file']['csv_info'] = 'Se Cargo un DBF.';
			            $processLog = Process::fromShellCommandline('echo "C1 DBF: $name"  >> archivos.log');
            			$processLog->run(null, ['name' => "Archivo: ".$original_name." subido como: ".$data['file']['c1']]);
            			$process = Process::fromShellCommandline('pgdbf -s latin1 $c1_dbf_file | psql -h $host -U $user laravel');
            			$process->run(null, ['c1_dbf_file' => storage_path().'/app/'.$data['file']['c1'],'db'=>Config::get('database.connections.pgsql.database'),'host'=>Config::get('database.connections.pgsql.host'),'user'=>Config::get('database.connections.pgsql.username'),'PGPASSWORD'=>Config::get('database.connections.pgsql.password')]);
                        // executes after the command finishes
                        if (!$process->isSuccessful()) {
                   //         throw new ProcessFailedException($process);
                                dd($process);
                        }else{
                            MyDB::moverDBF(storage_path().'/app/'.$data['file']['c1'],$codaglo);
                        }
                    
           }else{$data['file']['csv_info'] = 'Se Cargo un archivo de formato no esperado!';}
        }

        if (Archivo::cargar($request, Auth::user())) {
            return view('segmenter/index', ['data' => $data,'epsgs'=> $this->epsgs]);
        } else {
            echo "Error en el modelo cargar";
        }
    }
}
