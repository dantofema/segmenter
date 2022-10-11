<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException; 
use Illuminate\Support\Facades\Config;
use App\Imports\SegmentosImport;
use App\Imports\CsvImport;
use Maatwebsite\Excel\Facades\Excel;
use App\MyDB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Exceptions\GeoestadisticaException;

class Archivo extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [
            'user_id', 'nombre_original', 'nombre', 'tipo', 'checksum', 'size', 'mime', 'tabla'
    ];
    protected $attributes = [
        'procesado' => false,
        'epsg_def' => 'epsg:22195'
    ];

    //Relación con usuario que subió el archivo.
    public function user() {
        return $this->belongsTo('App\User');
    }

    // Funcion para cargar información de archivo en la base de datos.
    public static function cargar($request_file, $user, $tipo=null, $shape_files = []) {
        $original_extension = strtolower($request_file->getClientOriginalExtension());
        
        $file = self::where('checksum', '=', md5_file(($request_file)->getRealPath()))->first();
        if (!$file) {
            flash("Nuevo archivo ".$original_extension);
            $guess_extension = strtolower($request_file->guessClientExtension());
            $original_name = $request_file->getClientOriginalName();
            $random_name= 't_'.$request_file->hashName();
            $random_name = substr($random_name,0,strpos($random_name,'.'));
            $file_storage = $request_file->storeAs('segmentador', $random_name.'.'.$request_file->getClientOriginalExtension());
            if ($tipo == 'shape'){
                if ($shape_files != null){
                    foreach ($shape_files as $shape_file) {
                        //Almacenar archivos asociados a shapefile con igual nombre
                        //según extensión.
                        if ($shape_file != null){
                            $extension = strtolower($shape_file->getClientOriginalExtension());
                            $data_files[] = $shape_file->storeAs('segmentador', $random_name.'.'.$extension);
                        };
                    }
                }
            }
            $file_storage = $request_file->storeAs('segmentador', $random_name.'.'.$request_file->getClientOriginalExtension());
            $file = self::create([
                'user_id' => $user->id,
                'nombre_original' => $original_name,
                'nombre' => $file_storage,
                'tabla' => $random_name,
                'tipo' => ($guess_extension!='bin' and $guess_extension!='')?$guess_extension:$original_extension,
                'checksum'=> md5_file($request_file->getRealPath()),
                'size' => $request_file->getSize(),
                'mime' => $request_file->getClientMimeType()
            ]);
            $user->visible_files()->attach($file->id);
        } else {
            if (!$user->visible_files()->get()->contains($file)){
                $user->visible_files()->attach($file->id);
            }
            flash("Archivo ".$original_extension." ya existente. No se cargará de nuevo ")->info();
        }
        return $file;
    }

    public function descargar() {
        flash('Descargando... '.$this->nombre_original);
        $file = storage_path().'/app/'.$this->nombre;
        $name = 'mandarina_'.time().'_'.$this->nombre_original;
        $headers = ['Content-Type: '.$this->mime];
        return response()->download($file, $name, $headers);
    }

    public function procesar()
    {
        if (!$this->procesado or true) {
            if ($this->tipo == 'csv' or $this->tipo == 'dbf') {
                if (( strtolower(substr($this->nombre_original, 0, 8)) == 'tablaseg')
                    or ( strtolower(substr($this->nombre_original, 0, 7))    == 'segpais')
                    or ( strtolower(substr($this->nombre_original, 0, 21))    == 'tabla_de_segmentacion')
                    or ( strtolower(substr($this->nombre_original, 0, 14))    == 'segmento_total')
                ) {
                    return $this->procesarSegmentos();
                } else {
                    return $this->procesarDBF();
                }
            } elseif ($this->tipo == 'e00' or $this->tipo == 'bin') {
                return $this->procesarGeomE00();
            } elseif ($this->tipo == 'pxrad/dbf') {
                return $this->procesarPxRad();
            } elseif ($this->tipo == 'shp') {
                return $this->procesarGeomSHP();
            } elseif ($this->tipo == 'shp/arc') {
                return $this->procesarGeomSHP();
            } elseif ($this->tipo == 'shp/lab') {
                return $this->procesarGeomSHP('lab');
            } elseif ($this->tipo == 'shp/pol') {
                return $this->procesarGeomSHP('pol');
            } else {
                flash('No se encontro qué hacer para procesar '.$this->nombre_original.'. tipo = '.$this->tipo)->warning();
                return false;
            }
        } else {
            flash('Archivo ya fue procesado: '.$this->nombre_original)->warning();
            return true;
        }
    }

    public function procesarDBF()
    {
        if ($this->tipo == 'csv'){
            $mensaje = 'Se Cargo un csv.';
            $import = new CsvImport;
            $import->delimiter = "|";
            Excel::import($import, storage_path().'/app/'.$this->nombre);
            $this->procesado=true;
            $this->save();
            return true;
        } elseif ($this->tipo == 'dbf' or $this->tipo='pxrad/dbf' ){
            // Mensaje de subida de DBF.
            flash('Procesando DBF.')->info();

            // Subo DBF con pgdbf a una tabla temporal.
            $process = Process::fromShellCommandline('pgdbf -s $encoding $dbf_file | psql -h $host -p $port -U $user $db');
            try {
                $process->run(null, [
                    'encoding'=>'latin1',
                    'dbf_file' => storage_path().'/app/'.$this->nombre,
                    'db'=>Config::get('database.connections.pgsql.database'),
                    'host'=>Config::get('database.connections.pgsql.host'),
                    'user'=>Config::get('database.connections.pgsql.username'),
                    'port'=>Config::get('database.connections.pgsql.port'),
                    'PGPASSWORD'=>Config::get('database.connections.pgsql.password')]
                );
                //    $process->mustRun();
          // executes after the command finishes
          if (Str::contains($process->getErrorOutput(),['ERROR'])){
              $process->run(null, [
                    'encoding'=>'utf8',
                    'dbf_file' => storage_path().'/app/'.$this->nombre,
                    'db'=>Config::get('database.connections.pgsql.database'),
                    'host'=>Config::get('database.connections.pgsql.host'),
                    'user'=>Config::get('database.connections.pgsql.username'),
                    'port'=>Config::get('database.connections.pgsql.port'),
                    'PGPASSWORD'=>Config::get('database.connections.pgsql.password')]);
              Log::warning('Error cargando DBF.',[$process->getOutput(),$process->getErrorOutput()]);
              if (Str::contains($process->getErrorOutput(),['ERROR'])){
                  Log::error('Error cargando DBF.',[$process->getOutput(),$process->getErrorOutput()]);
                  flash('Error cargando DBF. '.$process->getErrorOutput())->important()->error();
                  return false;
              }
          } else {
            $this->procesado=true;
            $this->save();
            Log::debug($process->getOutput().$process->getErrorOutput());
            return true;
          }
      } catch (ProcessFailedException $exception) {
          Log::error($process->getErrorOutput().$exception);
      } catch (RuntimeException $exception) {
          Log::error($process->getErrorOutput().$exception);
      }
    } else {
    flash($data['file']['csv_info'] = 'Se Cargo un archivo de formato
         no esperado!')->error()->important();
          $this->procesado=false;
          return false;
        }
    }

    public function procesarGeomSHP($capa = 'arc') {
        MyDB::createSchema('_'.$this->tabla);
        flash('Procesando Geom desde Shape en reestructuración, disculpe las molestias, estamos trabajando!')->warning();
        $mensajes = '';
        $processOGR2OGR = Process::fromShellCommandline(
            '(/usr/bin/ogr2ogr -f \
            "PostgreSQL" PG:"dbname=$db host=$host user=$user port=$port \
            active_schema=e$esquema password=$pass" --config PG_USE_COPY YES \
            -lco OVERWRITE=YES --config OGR_TRUNCATE YES -dsco \
            PRELUDE_STATEMENTS="SET client_encoding TO latin1;CREATE SCHEMA \
            IF NOT EXISTS e$esquema;" -dsco active_schema=e$esquema -lco \
            PRECISION=NO -lco SCHEMA=e$esquema \
            -nln $capa \
            -skipfailures \
            -overwrite $file )'
        );
        $processOGR2OGR->setTimeout(1800);
        
        //Cargo etiquetas
        try{
            $processOGR2OGR->run(null,[
                'capa'=>$capa,
                'epsg'=> $this->epsg_def,
                'file' => storage_path().'/app/'.$this->nombre,
                'esquema'=>'_'.$this->tabla,
                'encoding'=>'cp1252',
                'db'=>Config::get('database.connections.pgsql.database'),
                'host'=>Config::get('database.connections.pgsql.host'),
                'user'=>Config::get('database.connections.pgsql.username'),
                'pass'=>Config::get('database.connections.pgsql.password'),
                'port'=>Config::get('database.connections.pgsql.port')
            ]);
            $mensajes.='<br />'.$processOGR2OGR->getErrorOutput().'<br />'.$processOGR2OGR->getOutput();
            flash($mensajes)->info();
            $this->procesado=true;
        } catch (ProcessFailedException $exception) {
            Log::error($processOGR2OGR->getErrorOutput());
            flash('Error Importando Shape '.$this->nombre_original)->info();
            $this->procesado=false;
        } catch (RuntimeException $exception) {
            Log::error($processOGR2OGR-->getErrorOutput().$exception);
            flash('Error Importando Runtime Shape '.$this->nombre_original)->info();
            $this->procesado=false;
        } catch(ProcessTimedOutException $exception){
            Log::error($processOGR2OGR->getErrorOutput().$exception);
            flash('Se agotó el tiempo Importando Shape de... etiquetas '.$this->nombre_original)->info();
            $this->procesado=false;
        }
        $this->save();
        return $this->procesado;
    }

    public function procesarGeomE00() {
        flash('Procesando Arcos y Etiquetas (Importando E00.) ')->info();
        MyDB::createSchema('_'.$this->tabla);
        $processOGR2OGR = Process::fromShellCommandline(
            '/usr/bin/ogr2ogr -f "PostgreSQL" \
            PG:"dbname=$db host=$host user=$user port=$port active_schema=e_$esquema \
            password=$pass" --config PG_USE_COPY YES -lco OVERWRITE=YES \
            --config OGR_TRUNCATE YES -dsco PRELUDE_STATEMENTS="SET client_encoding TO $encoding; \
            CREATE SCHEMA IF NOT EXISTS e_$esquema;" -dsco active_schema=e_$esquema \
            -lco PRECISION=NO -lco SCHEMA=e_$esquema -s_srs $epsg -t_srs $epsg \
            -nln $capa -addfields -overwrite $file $capa'
        );
        $processOGR2OGR->setTimeout(1800);
        // -skipfailures
        //Cargo arcos
        try {
            $processOGR2OGR->run(null,[
                'capa'=>'arc',
                'epsg'=> $this->epsg_def,
                'file' => storage_path().'/app/'.$this->nombre,
                'esquema'=>$this->tabla,
                'encoding'=>'cp1252',
                'db'=>Config::get('database.connections.pgsql.database'),
                'host'=>Config::get('database.connections.pgsql.host'),
                'user'=>Config::get('database.connections.pgsql.username'),
                'pass'=>Config::get('database.connections.pgsql.password'),
                'port'=>Config::get('database.connections.pgsql.port')
            ]);
            $mensajes=$processOGR2OGR->getErrorOutput().'<br />'.$processOGR2OGR->getOutput();
        } catch (ProcessFailedException $exception) {
            Log::error($processOGR2OGR->getErrorOutput());
            flash('Error Importando Falló E00 '.$this->nombre_original)->info();
            return false;
        } catch (RuntimeException $exception) {
            Log::error($processOGR2OGR->getErrorOutput().$exception);
            flash('Error Importando Runtime E00 '.$this->nombre_original)->info();
            return false;
        } catch(ProcessTimedOutException $exception){
            Log::error($processOGR2OGR->getErrorOutput().$exception);
            flash('Se agotó el tiempo Importando E00 de arcos '.$this->nombre_original)->info();
            return false;
        }
        //Cargo etiquetas
        try{
            $processOGR2OGR->run(null,[
                'capa'=>'lab',
                'epsg'=> $this->epsg_def,
                'file' => storage_path().'/app/'.$this->nombre,
                'esquema'=>$this->tabla,
                'encoding'=>'cp1252',
                'db'=>Config::get('database.connections.pgsql.database'),
                'host'=>Config::get('database.connections.pgsql.host'),
                'user'=>Config::get('database.connections.pgsql.username'),
                'pass'=>Config::get('database.connections.pgsql.password'),
                'port'=>Config::get('database.connections.pgsql.port')
            ]);
            $mensajes.='<br />'.$processOGR2OGR->getErrorOutput().'<br />'.$processOGR2OGR->getOutput();
            $this->procesado=true;
        } catch (ProcessFailedException $exception) {
            Log::error($processOGR2OGR->getErrorOutput());
            flash('Error Importando Falló E00 '.$this->nombre_original)->info();
            $this->procesado=false;
            return false;
        } catch (RuntimeException $exception) {
            Log::error($processOGR2OGR-->getErrorOutput().$exception);
            flash('Error Importando Runtime E00 '.$this->nombre_original)->info();
            $this->procesado=false;
            return false;
        } catch(ProcessTimedOutException $exception){
            Log::error($processOGR2OGR->getErrorOutput().$exception);
            flash('Se agotó el tiempo Importando E00 de etiquetas '.$this->nombre_original)->info();
            return false;
        }
        $this->save();
        return $mensajes;
    }

    // Copia o Mueve listados de una C1 al esquema de las localidades encontradas
    // Retorna Array $ppdddlls con codigos de localidades
    public function moverData() {
        // Busca dentro de la tabla las localidades
        $ppdddllls = MyDB::getLocs($this->tabla,'public');
        $count = 0;
        foreach ($ppdddllls as $ppdddlll) {
            flash('Se encontró loc en C1: '.$ppdddlll->link);
            MyDB::createSchema($ppdddlll->link);
            if (substr($ppdddlll->link, 0, 2) == '02') {
                flash($data['file']['caba']='Se detecto CABA: '.$ppdddlll->link);
                $codigo_esquema=$ppdddlll->link;
                $segmenta_auto=true;
            } elseif (substr($ppdddlll->link, 0, 2) == '06') {
                flash($data['file']['data']='Se detecto PBA: '.$ppdddlll->link);
                //$codigo_esquema=substr($ppdddlll->link, 0, 5);
                // Se utiliza el código de localidad también para PBA
                $codigo_esquema=$ppdddlll->link;
            } else {
                $codigo_esquema=$ppdddlll->link;
            }
            MyDB::moverDBF(storage_path().'/app/'.$this->nombre,$codigo_esquema,$ppdddlll->link);
            $count++;
        }
        Log::debug('C1 se copió en '.$count.' esquemas');
        MyDB::borrarTabla($this->tabla);
        return $ppdddllls;
    }

    // Pasa data geo, arcos y labels al esquema de las localidades encontradas
    // Retorna Array $ppdddlls con codigos de localidades
    public function pasarData(){
        // Leo dentro de la tabla de etiquetas la/s localidades
        $ppdddllls=MyDB::getLocs('lab','e_'.$this->tabla);
        $count=0;
        // Si no encuentro localidades en lab.
        if ($ppdddllls==[]) {
            // Intento cargar pais x depto :D
            $coddeptos = MyDB::getDptos('lab', 'e_'.$this->tabla);
            flash('Puede ser una pais con deptos: '.count($coddeptos));
            foreach ($coddeptos as $coddepto){
                flash('Se encontró Departamento : '.$coddepto->link);
                MyDB::createSchema($coddepto->link);
                MyDB::copiaraEsquemaPais('e_'.$this->tabla,'e'.$coddepto->link,$coddepto->link);
                $count++;
            }
            MyDB::limpiar_esquema('e_'.$this->tabla);
            return $coddeptos;
        } else {
            // Para cada localidad encontrada
            // creo esquema y copio datos a esquema según codigo.
            foreach ($ppdddllls as $ppdddlll) {
                flash('Se encontró loc Etiquetas: '.$ppdddlll->link);
                MyDB::createSchema($ppdddlll->link);
                //MyDB::moverEsquema('e_'.$this->tabla,'e'.$ppdddlll->link);
                MyDB::copiaraEsquema('e_'.$this->tabla,'e'.$ppdddlll->link,$ppdddlll->link);
                $count++;
            }
            flash('Se encontraron '.$count.' localidaes en la cartografía');
            MyDB::limpiar_esquema('e_'.$this->tabla);
            return $ppdddllls;
        }
    }

    public function infoData(){
         return MyDB::infoDBF('listado',$this->tabla);
    }

    // Archivos csv con tabla de segmentación generada en modo manual.
    // x provincia juntando /y corrigiendo) segmentación Urbana y Urbano-Mixta con
    // segmentos Rural, Rural-Mixto.
    public function procesarSegmentos() {
        $mensaje = 'Se procesa un csv. Segmentos completos? Resultado: ';
        $import = new SegmentosImport($this->user);
        Excel::import($import, storage_path().'/app/'.$this->nombre);
        $this->procesado=true;
        $this->save();
        flash($mensaje.$import->getRowCount());
        return true;
    }

    public function procesarPxRad() {
        flash('TODO: Procesar PxRad en archivo')->warning();
        $dbf = $this->procesarDBF();
        try {
            return $procesar_result = MyDB::procesarPxRad(strtolower($this->tabla),'public');
        } catch ( GeoestadisticaException $e ) {
            flash('('.$e->GetCode().') Error. NO SE PUDO PROCESAR PXRAD: '.$e->getMessage())->error()->important();
            $data['file']['pxrad']='none';
            Log::debug('Error cargando data Radio '.$e);
            return view('segmenter/index', ['data' => $data,'epsgs'=> $this->epsgs]);
        }
    }

    public function asociar(Archivo $lab_file_asoc){
        $resulta = MyDB::moverEsquema('e_'.$this->tabla,'e_'.$lab_file_asoc->tabla);
        $ex_tabla = $this->tabla;
        $this->tabla = Str($lab_file_asoc->tabla);
        $this->save();
        MyDB::limpiar_esquema('e_'.$ex_tabla);
        return $resulta;
    }

}

