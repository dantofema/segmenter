<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Config;
//use App\Imports\CsvImport;
use Maatwebsite\Excel;
use App\MyDB;

class Archivo extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [
            'user_id','nombre_original','nombre','tipo','checksum','size','mime','tabla'
    ];
    protected $attributes = [
	    'procesado' => false,
	    'tabla' => null
    ];

    // Funcion para cargar información de archivo en la base de datos.
    public static function cargar($request_file, $user, $tipo=null){
		$original_extension = strtolower($request_file->getClientOriginalExtension());
		$original_name = $request_file->getClientOriginalName();
		$random_name= 't_'.$request_file->hashName();
		$random_name = substr($random_name,0,strpos($random_name,'.'));
		$file_storage = $request_file->storeAs('segmentador', $random_name.'.'.$request_file->getClientOriginalExtension());
		return self::create([
                            'user_id' => $user->id,
			    'nombre_original' => $original_name,
			    'nombre' => $file_storage,
			    'tabla' => $random_name,
			    'tipo' => $request_file->guessClientExtension()?$request_file->guessClientExtension():$original_extension,
			    'checksum'=> md5_file($request_file->getRealPath()),
                            'size' => $request_file->getClientSize(),
                            'mime' => $request_file->getClientMimeType()
                        ]);
	//return false;
    }

    public function descargar(){
	    flash('Descargando... '.$this->nombre_original);
	    $file= storage_path().'/app/'.$this->nombre;
	    $name= 'mandarina_'.time().'_'.$this->nombre_original;
	    $headers=['Content-Type: '.$this->mime];
	    return response()->download($file, $name, $headers);

    }

    public function procesar(){
       if ($this->tipo == 'csv'){
            //$data['file']['csv_info'] = 'Se Cargo un csv.';
            //$process = Process::fromShellCommandline('echo "C1 CSV: $name" >> archivos.log');
            //$process->run(null, ['name' => "Archivo: ".$original_name." subido como: ".$data['file']['c1']]);
    /*
                    $data['file']['csv_detail'] = Listado::cargar_csv( storage_path().'/app/'.$data['file']['c1']);
                     return view('listado/all', ['listado' => $data['file']['csv_detail'],'epsgs'=> $epsgs]);
    */
             $import = new CsvImport;
             Excel::import($import, storage_path().'/app/'.$data['file']['c1']);
            //  dd('Row count: ' . $import->getRowCount());
             return view('listado/all', ['listado' => $data['file']['csv_detail'],'epsgs'=> $epsgs]);
        }elseif ($this->tipo == 'dbf'){
            // Mensaje de subida de DBF y logeo en archivo.
            flash($data['file']['csv_info'] = 'Se Cargo un DBF.');
//            $processLog = Process::fromShellCommandline('echo "C1 DBF: $name" >> archivos.log');
//            $processLog->run(null, ['name' => "Archivo: ".$original_name." subido como: ".$data['file']['c1']]);

            // Subo DBF con pgdbf a una tabla temporal.
            $process = Process::fromShellCommandline('pgdbf -s latin1 $c1_dbf_file | psql -h $host -p $port -U $user $db');
            $process->run(null, ['c1_dbf_file' => storage_path().'/app/'.$this->nombre,'db'=>Config::get('database.connections.pgsql.database'),'host'=>Config::get('database.connections.pgsql.host'),'user'=>Config::get('database.connections.pgsql.username'),'port'=>Config::get('database.connections.pgsql.port'),'PGPASSWORD'=>Config::get('database.connections.pgsql.password')]);
	    // executes after the command finishes
	    $this->procesado=true;
	    return true;
	}else{
	     flash($data['file']['csv_info'] = 'Se Cargo un archivo de formato
		     no esperado!')->error()->important();
            $this->procesado=false;
            return false;
        }
	    
    }
   
    public function moverData(){
            // Leo dentro del csv que aglo/s viene/n o localidad depto CABA

            $tabla =  $this->tabla;
            $aglo_interno=MyDB::getAglo($tabla,'public');
            $codprov=MyDB::getProv($tabla,'public');
            if ($codprov=='02'){
                $ppdddlll=MyDB::getLoc($tabla,'public');
                flash($data['file']['caba']='Se detecto CABA: '.$ppdddlll);
                $codaglo=$ppdddlll;
                $segmenta_auto=true;
            }elseif ($codprov=='06'){
                $ppdddlll=MyDB::getLoc($tabla,'public');
                flash($data['file']['data']='Se detecto PBA: '.$ppdddlll);
                $codaglo=substr($ppdddlll,0,5);
            }else{
                $codaglo=$aglo_interno;
            }

            MyDB::createSchema($codaglo);

            MyDB::moverDBF(storage_path().'/app/'.$this->nombre,$codaglo);
            $data['file']['info_dbf']=MyDB::infoDBF('listado',$codaglo);
            return $data['file']['codigo_usado']=$codaglo;
    }
   
}
