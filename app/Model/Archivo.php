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
use Illuminate\Support\Facades\Log;


class Archivo extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [
            'user_id','nombre_original','nombre','tipo','checksum','size','mime','tabla'
    ];
    protected $attributes = [
	    'procesado' => false,
	    'tabla' => null,
	    'epsg_def' => 'epsg:22195'
    ];

    //Relación con usuario que subió el archivo.
    public function user()
    {
        return $this->belongsTo('App\User');
    }


    // Funcion para cargar información de archivo en la base de datos.
    public static function cargar($request_file, $user, $tipo=null){
		$original_extension = strtolower($request_file->getClientOriginalExtension());
        $guess_extension = strtolower($request_file->guessClientExtension());
		$original_name = $request_file->getClientOriginalName();
		$random_name= 't_'.$request_file->hashName();
		$random_name = substr($random_name,0,strpos($random_name,'.'));
		$file_storage = $request_file->storeAs('segmentador', $random_name.'.'.$request_file->getClientOriginalExtension());
		return self::create([
                'user_id' => $user->id,
			    'nombre_original' => $original_name,
			    'nombre' => $file_storage,
			    'tabla' => $random_name,
			    'tipo' => $guess_extension!='bin'?$guess_extension:$original_extension,
			    'checksum'=> md5_file($request_file->getRealPath()),
                'size' => $request_file->getClientSize(),
                'mime' => $request_file->getClientMimeType()
                ]);
    }

    public function descargar(){
	    flash('Descargando... '.$this->nombre_original);
	    $file= storage_path().'/app/'.$this->nombre;
	    $name= 'mandarina_'.time().'_'.$this->nombre_original;
	    $headers=['Content-Type: '.$this->mime];
	    return response()->download($file, $name, $headers);

    }

    public function procesar(){
       if ($this->tipo == 'csv' or $this->tipo == 'dbf'){
            return $this->procesarC1();
        }elseif($this->tipo == 'e00' or $this->tipo == 'bin') {
            return $this->procesarGeomE00();
        }else{
            flash('No se encontro qué hacer para procesar '.$this->nombre_original )->warning();
            return false;
        }
    }

    public function procesarC1(){
        if ($this->tipo == 'csv'){
            $mensaje = 'Se Cargo un csv.';
            $import = new CsvImport;
            Excel::import($import, storage_path().'/app/'.$this->nombre);
	    $this->procesado=true;
	    $this->save();	    
	    return true;
        }elseif ($this->tipo == 'dbf'){
            // Mensaje de subida de DBF.
            flash('Procesando DBF.')->info();

	    // Subo DBF con pgdbf a una tabla temporal.
            $process = Process::fromShellCommandline('pgdbf -s latin1 $c1_dbf_file | psql -h $host -p $port -U $user $db');
            try {
                $process->run(null, [
                    'c1_dbf_file' => storage_path().'/app/'.$this->nombre,
                    'db'=>Config::get('database.connections.pgsql.database'),
                    'host'=>Config::get('database.connections.pgsql.host'),
                    'user'=>Config::get('database.connections.pgsql.username'),
                    'port'=>Config::get('database.connections.pgsql.port'),
                    'PGPASSWORD'=>Config::get('database.connections.pgsql.password')]);
                //    $process->mustRun();
	        // executes after the command finishes
	        $this->procesado=true;
	        $this->save();
	        Log::debug($process->getOutput());
	        return true;
	    } catch (ProcessFailedException $exception) {
	        Log::error($process->getErrorOutput());
	    }
	}else{
	  flash($data['file']['csv_info'] = 'Se Cargo un archivo de formato
		     no esperado!')->error()->important();
          $this->procesado=false;
          return false;
        }
    }

    public function procesarGeomSHP(){
          flash('Procesando Geom . TODO: No implementado!')->error();
                
          $processOGR2OGR =
                Process::fromShellCommandline('(/usr/bin/ogr2ogr -f \
                "PostgreSQL" PG:"dbname=$db host=$host user=$user port=$port \
                active_schema=e$e00 password=$pass" --config PG_USE_COPY YES \
                -lco OVERWRITE=YES --config OGR_TRUNCATE YES -dsco \
                PRELUDE_STATEMENTS="SET client_encoding TO latin1;CREATE SCHEMA \
                IF NOT EXISTS e$e00;" -dsco active_schema=e$e00 -lco \
                PRECISION=NO -lco SCHEMA=e$e00 \
                -nln $capa \
                -skipfailures \
                -overwrite $file )');
           $processOGR2OGR->setTimeout(3600);
	    $this->procesado=false;
	    $this->save();	    
           
           }
    public function procesarGeomE00(){
          flash('Procesando Arcos y Etiquetas (Importando E00.) ')->info();
          MyDB::createSchema('_'.$this->tabla);
          $processOGR2OGR = Process::fromShellCommandline('/usr/bin/ogr2ogr -f "PostgreSQL" \
                     PG:"dbname=$db host=$host user=$user port=$port active_schema=e_$esquema \
                     password=$pass" --config PG_USE_COPY YES -lco OVERWRITE=YES \
                     --config OGR_TRUNCATE YES -dsco PRELUDE_STATEMENTS="SET client_encoding TO $encoding; \
                     CREATE SCHEMA IF NOT EXISTS e_$esquema;" -dsco active_schema=e_$esquema \
                     -lco PRECISION=NO -lco SCHEMA=e_$esquema -s_srs $epsg -t_srs $epsg \
                     -nln $capa -addfields -overwrite $file $capa');
           $processOGR2OGR->setTimeout(3600);
                     // -skipfailures           
	  //Cargo arcos
	  try{
           $processOGR2OGR->run(null, 
            ['capa'=>'arc',
             'epsg'=> $this->epsg_def,
             'file' => storage_path().'/app/'.$this->nombre,
             'esquema'=>$this->tabla,
             'encoding'=>'latin1',
             'db'=>Config::get('database.connections.pgsql.database'),
             'host'=>Config::get('database.connections.pgsql.host'),
             'user'=>Config::get('database.connections.pgsql.username'),
             'pass'=>Config::get('database.connections.pgsql.password'),
             'port'=>Config::get('database.connections.pgsql.port')]);
            $mensajes=$processOGR2OGR->getErrorOutput().'<br />'.$processOGR2OGR->getOutput();
	   } catch (ProcessFailedException $exception) {
	       Log::error($processOGR2OGR->getErrorOutput());
           }

	  //Cargo etiquetas
	  try{
           $processOGR2OGR->run(null, 
            ['capa'=>'lab',
             'epsg'=> $this->epsg_def,
             'file' => storage_path().'/app/'.$this->nombre,
             'esquema'=>$this->tabla,
             'encoding'=>'latin1',
             'db'=>Config::get('database.connections.pgsql.database'),
             'host'=>Config::get('database.connections.pgsql.host'),
             'user'=>Config::get('database.connections.pgsql.username'),
             'pass'=>Config::get('database.connections.pgsql.password'),
             'port'=>Config::get('database.connections.pgsql.port')]);
              $mensajes.='<br />'.$processOGR2OGR->getErrorOutput().'<br />'.$processOGR2OGR->getOutput();
	      $this->procesado=true;
	   } catch (ProcessFailedException $exception) {
	      Log::error($processOGR2OGR->getErrorOutput());
	      $this->procesado=false;
     }
     $this->save();
     return $mensajes;
    }

    // Copia o Mueve listados de una C1 al esquema de las localidades encontradas
    // Retorna Array $ppdddlls con codigos de localidades
    public function moverData(){
       // Busca dentro de la tabla las localidades
       $ppdddllls=MyDB::getLocs($this->tabla,'public');
       $count=0;
       foreach ($ppdddllls as $ppdddlll){
          	flash('Se encontró loc en C1: '.$ppdddlll->link);
            MyDB::createSchema($ppdddlll->link);

            if (substr($ppdddlll->link,0,2)=='02'){
                flash($data['file']['caba']='Se detecto CABA: '.$ppdddlll->link);
                $codigo_esquema=$ppdddlll->link;
                $segmenta_auto=true;
            }elseif (substr($ppdddlll->link,0,2)=='06'){
                flash($data['file']['data']='Se detecto PBA: '.$ppdddlll->link);
                $codigo_esquema=substr($ppdddlll->link,0,5);
            }else{
                $codigo_esquema=$ppdddlll->link;
            }
            MyDB::moverDBF(storage_path().'/app/'.$this->nombre,$codigo_esquema);
            $count++;
        }
        Log::debug('C1 se copió en '.$count.' esqumas');
        MyDB::borrarTabla($this->tabla);
        return $ppdddllls;
    }


    // Pasa data geo, arcos y labels al esquema de las localidades encontradas
    // Retorna Array $ppdddlls con codigos de localidades
    public function pasarData(){
             // Leo dentro de la tabla de etiquetas la/s localidades
            $ppdddllls=MyDB::getLocs('lab','e_'.$this->tabla);
            $count=0;
            foreach ($ppdddllls as $ppdddlll){
             	flash('Se encontró loc Etiquetas: '.$ppdddlll->link);
              MyDB::createSchema($ppdddlll->link);
            	//MyDB::moverEsquema('e_'.$this->tabla,'e'.$ppdddlll->link);
              MyDB::copiaraEsquema('e_'.$this->tabla,'e'.$ppdddlll->link);
              $count++;
            }
            MyDB::limpiar_esquema('e_'.$this->tabla);
            return $ppdddllls;
    }

    public function infoData(){
       return MyDB::infoDBF('listado',$this->tabla);
    }
}
