<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Model\Radio;
use Symfony\Component\Process\Process;
use Auth;
use Illuminate\Database\QueryException;

class MyDB extends Model
{

    // Muestrea el esquema
    //
    public static function muestrear($esquema)
    {
        try{
            DB::beginTransaction();
            DB::statement(" SELECT indec.muestrear('".$esquema."');");
            DB::statement(" SELECT indec.segmentos_desde_hasta('".$esquema."');");
            $result = DB::statement(" SELECT * from indec.describe_despues_de_muestreo('".$esquema."');");
            DB::commit();
        }catch(QueryException $e){
            DB::Rollback();
                $result=null;
                Log::error('No se pudo muestrar el esquema '.$esquema.' Error:'.$e);
            }
            Log::debug('Se muestreo el esquema '.$esquema.' !');
            return $result;
        }

        // Segmenta a listado los lados excedidos segun umbral
        //
        public static function
        segmentar_excedidos_ffrr($esquema,$frac,$radio,$umbral=20,$deseado=20)
        {
            try{
                Log::info('Resegmentando segmentos excedidos de fraccion
                '.$frac.', radio '.$radio);
                return DB::statement(" SELECT indec.segmentar_excedidos_ffrr(
                'e".$esquema."',".$frac.",".$radio.",".$umbral.",".$deseado.");");
            }catch(QueryException $e){
                Log::warning('No se pudo segmentar segmentos excedidos, reintentando...');
                self::cambiarSegmentarBigInt($esquema);
                self::recrea_vista_segmentos_lados_completos($esquema);
                try{
                    return DB::statement(" SELECT indec.segmentar_excedidos_ffrr(
                    'e".$esquema."',".$frac.",".$radio.",".$umbral.",".$deseado.");");
                }catch(QueryException $e){
                    Log::error('No se pudo segmentar segmentos excedidos'.$e);
                    return 0;
                }
            }
            Log::debug('Se resegmentaron los segmentos excedidos!');
        }

        // Propaga la segmentacion a partir de lados completos hacia la tabla de
        // segmentacion.
        public static function
        lados_completos_a_tabla_segmentacion_ffrr($esquema,$frac,$radio)
        {
            try{
                DB::statement("SELECT
                indec.lados_completos_a_tabla_segmentacion_ffrr('e".$esquema."',".$frac.",".$radio.");");
                DB::statement("SELECT indec.segmentos_desde_hasta('e".$esquema."');");
            }catch(QueryException $e){
                self::generarSegmentacionVacia($esquema);
                self::generarR3Vacia($esquema);
                self::addSequenceSegmentos('e'.$esquema);
                Log::warning('Create sequence xq no exisitia...');
                self::recrea_vista_segmentos_lados_completos($esquema);
                DB::statement("SELECT
                indec.lados_completos_a_tabla_segmentacion_ffrr('e".$esquema."',".$frac.",".$radio.");");

            }
            Log::debug('Propagando segmentacion lados completos de tabla arc a
            tabla segmentacion -> '.$esquema);
        }

        // crea o reemplaza la vista de segmentos generados por lados completos.
        public static function recrea_vista_segmentos_lados_completos($esquema)
        {
            DB::statement("SELECT
            indec.v_segmentos_lados_completos('e".$esquema."');");
            Log::debug('Creando vista manzana lado numero segmento en radio, cant
            viviendas -> '.$esquema);
        }

        // Obtengo segmentos excedidos.
        public static function segmentos_excedidos($esquema,$vivs,Radio $radio=null)
        {
                if ($radio){
                    $filtro=" ppdddcccffrr like
                    '".substr($radio->codigo,0,5)."___".substr($radio->codigo,-4)."'";
                    Log::debug('Filtro excedidos del radio: '.$radio->codigo.'
                                aplicando '.$filtro);
                    try{
                       $result = DB::select("SELECT * FROM ".$esquema.".v_segmentos_lados_completos
                        WHERE vivs > ".$vivs." and ".$filtro.";");
                    }catch(QueryException $e){
                      Log::error('ERROR Buscando segmentos excedidos del esquema-> '.$esquema.' con el filtro '.$filtro.$e);
                      $result=[];
                    }
                 }
                 else{
                    try{
                     $result = DB::select("SELECT * FROM ".$esquema.".v_segmentos_lados_completos
                           WHERE vivs > ".$vivs.";");
                    }catch(QueryException $e){
                      Log::error('ERROR Buscando segmentos excedidos del esquema-> '.$esquema.' sin filtro '.$e);
                      $result=[];
                    }
                }
            return $result;
        }

        // Junta los segmentos con 0 vivendas al segmneto menor cercano.
        public static function juntar_segmentos($esquema)
  {
            try{
              $result = DB::statement("SELECT indec.juntar_segmentos('".$esquema."')");
              Log::debug('Juntando segmentos del esquema-> '.$esquema);
              return $result;
            }catch(QueryException $e){
              Log::error('ERROR Juntando segmentos del esquema-> '.$esquema);
        return false;
      }
        }

        //Crea el esquema si no existe y asigna los permisos.
        public static function createSchema($esquema)
        {
            if (!DB::select('SELECT 1 from information_schema.schemata where schema_name = ?',['e'.$esquema])){
              DB::statement('CREATE SCHEMA IF NOT EXISTS "e'.$esquema.'"');
              Log::debug('Creando esquema-> e'.$esquema);
              self::darPermisos('e'.$esquema);
              return true;
            }else{
              Log::debug('Encontrado esquema-> e'.$esquema);
              return true;
            }
            return false;
        }

        //Dar permisos a una tabla.
        public static function darPermisosTabla($tabla,$rol='geoestadistica')
        {
            try{
                DB::statement("GRANT SELECT ON TABLE  ".$tabla." TO ".$rol);
            }catch(QueryException $e){
                Log::error('No se pudo dar permiso a '.$rol.' sobre '.$tabla.'.'.$e);
          return false;
            }
      Log::info('Se dió permiso a '.$rol.' sobre '.$tabla.'.');
      return true;
  }

        //Develve data del DBF subido.
        public static function infoDBF($tabla,$esquema)
        {
            return json_encode(DB::select('
                            SELECT prov,dpto,nom_loc,codaglo, codloc, nom_loc, codent,nom_ent,count(*) registros,
                            count(distinct frac||radio) as radios,
                            count(indec.contar_vivienda(tipoviv)) as viviendas
                        --,count(*) vivs
                        ,count(distinct prov||dpto||codloc||frac||radio||mza) as mzas
                        --,array_agg(distinct prov||dpto||codloc||frac||radio||mza||lado),count(distinct lado) as lados
FROM
                        e'.$esquema.'.'.$tabla.'
                        GROUP BY 1,2,3,4,5,6,7,8;'));
    }

    public static function getAglo($tabla,$esquema,$filtro='')
    {
        return (DB::select('SELECT distinct codaglo FROM
        '.$esquema.'.'.$tabla.$filtro.';')[0]->codaglo);
    }

    public static function getDataAglo($tabla,$esquema,$codloc)
    {
      if(isset($codloc)){
          log::debug(' Aglomerado para la localidad : '.$codloc);
                $filtro=" WHERE codprov||coddepto||codloc= '".$codloc."'";
      }else{$filtro='';}
        try {
            return (DB::select('SELECT codaglo as codigo, nombre FROM
                         '.$esquema.'.'.$tabla.
                         $filtro.
                         ' group by 1,2 order by count(*) desc Limit 1;')[0]);
        }catch (\Illuminate\Database\QueryException $exception) {
    Log::warning('Aglomerado Sin Nombre: '.$exception);
    //Supongo sin Nombre
    $codaglo=self::getAglo($tabla,$esquema,$filtro);
      return (object) ['codigo'=>$codaglo,'nombre'=>'Sin Nombre'];
  }
    }

    public static function getProv($tabla,$esquema)
    {
        return (DB::select('SELECT prov as link FROM
        '.$esquema.'.'.$tabla.' Limit 1;')[0]->link);
    }

    public static function getDataProv($tabla,$esquema)
    {
        try {
            return (DB::select('SELECT codprov as codigo,nomprov as nombre FROM
            '.$esquema.'.'.$tabla.' group by 1,2 order by count(*) desc Limit 1;')[0]);
        }catch (\Illuminate\Database\QueryException $exception) {
    Log::error('Error: '.$exception);
    //Supongo codprov sin Nombre
    $codprov=self::getCodProv($tabla,$esquema);
      return ['codigo'=>$codprov,'nombre'=>'Sin Nombre'];
  }
    }

    public static function getCodProv($tabla,$esquema)
    {
        try {
            return (DB::select('SELECT codprov as link FROM
            '.$esquema.'.'.$tabla.' group by 1 order by count(*) Limit 1;')[0]->link);
        }catch (\Illuminate\Database\QueryException $exception) {
      Log::error('Error: '.$exception);
      return '0';
  }
    }

    public static function getDataDepto($tabla,$esquema)
    {
        try {
            return (DB::select('SELECT codprov||coddepto as codigo,nomdepto as nombre FROM
            '.$esquema.'.'.$tabla.' group by 1,2 order by codprov||coddepto asc,count(*) desc ;'));
        }catch (\Illuminate\Database\QueryException $exception) {
            if (Schema::hasColumn($esquema.'.'.$tabla , 'coddepto')){
                DB::unprepared('ALTER TABLE '.$esquema.'.'.$tabla.' RENAME coddepto23 TO coddepto');
                Log::warning('Cuidado!! Se utilizó coddepto23 cono coddepto : '.$exception);
                return (DB::select('SELECT codprov||coddepto as codigo,nomdepto as nombre FROM
              '.$esquema.'.'.$tabla.' group by 1,2 order by codprov||coddepto asc,count(*) desc ;'));
      }
      Log::error('Error: '.$exception);
      // Loguea error y devuelve array nulo
      return [];
      }
    }

    public static function getDataFrac($tabla,$esquema,$codigo_dpto=null)
    {
      if(isset($codigo_dpto)){
          log::debug(' Fracciones del departamento: '.$codigo_dpto);
          $filtro=" WHERE codprov||coddepto= '".$codigo_dpto."'";
      }else{$filtro='';}
        try {
          return (DB::select('SELECT codprov||coddepto||frac2020 as codigo,
                       codprov||coddepto||codloc||frac2020 as nombre FROM
                     '.$esquema.'.'.$tabla.' '.$filtro.' group by 1,2 order by codprov||coddepto||codloc||frac2020 asc, count(*) desc ;'));
        }catch (\Illuminate\Database\QueryException $exception) {
          Log::error('Error: '.$exception);
          //
          return [];
        }
    }

    public static function getDataRadio($tabla,$esquema,$codigo_loc=null)
    {
      log::debug(' Radios de la Localidad: '.$codigo_loc);
     if(isset($codigo_loc)){ $filtro=" WHERE codprov||coddepto||codloc= '".$codigo_loc."'";
     }else{$filtro='';}
     try {
         return (DB::select('SELECT codprov||coddepto||frac2020||radio2020 as codigo,
                 codprov||coddepto||codloc||frac2020||radio2020 as nombre,upper(tiporad20) as tipo FROM
                 '.$esquema.'.'.$tabla.' '.$filtro.' group by 1,2,3 order by codprov||coddepto||codloc||frac2020||radio2020 asc, count(*) desc ;'));
     }catch (\Illuminate\Database\QueryException $exception) {
      Log::warning('Malabares : '.$exception);
      flash('Puede que no se haya encontrado el tipo de radio, se asúme todo Urbano')->important()->warning();
      // Se intenta asumiendo que es urbano y falta el tiporad20
      try {
         return (DB::select('SELECT codprov||coddepto||frac2020||radio2020 as codigo,
                codprov||coddepto||codloc||frac2020||radio2020 as nombre,\'U\' as tipo FROM
                '.$esquema.'.'.$tabla.' '.$filtro.' group by 1,2,3 order by codprov||coddepto||codloc||frac2020||radio2020 asc, count(*) desc ;'));
                //
      }catch (\Illuminate\Database\QueryException $exception) {
          Log::error('Error : '.$exception);
          return [];
      }
     }
    }

    public static function getDataLoc($tabla,$esquema,$codigo_depto=null)
    {
      log::debug(' Localidades del depto: '.$codigo_depto);
      if(isset($codigo_depto)){ $filtro=" WHERE codprov||coddepto= '".$codigo_depto."'";
      }else{$filtro='';}
        try {
            return (DB::select('SELECT codprov||coddepto||codloc as codigo,nomloc as nombre FROM
            '.$esquema.'.'.$tabla.' '.$filtro.' group by 1,2 order by codprov||coddepto||codloc asc, count(*) desc ;'));
        }catch (\Illuminate\Database\QueryException $exception) {
    Log::error('Error: '.$exception);
    //Supongo codprov sin Nombre
    //
      return null;;
  }
    }

    // Devuelve el link de localidad de mayor ocurrencia
    public static function getLoc($tabla,$esquema){
      return self::getLocs($tabla,$esquema)[0]->link;
    }

    // Devuelve link de localidad y cantidad de ocurrencias
    public static function getLocs($tabla,$esquema)
    {
        try {
            return (DB::select('SELECT prov||dpto||codloc as link,count(*) FROM
                    "'.$esquema.'".'.$tabla.' group by prov||dpto||codloc order by count(*);'));
        }catch (QueryException $exception) {
           try {
               return (DB::select('SELECT prov||depto||codloc as link,count(*) FROM
                       "'.$esquema.'".'.$tabla.' group by prov||depto||codloc order by count(*);'));
           }catch (QueryException $exception) {
               Log::error('No se pudo encontrar localidades: '.$exception);
               return [];
           }
         }
    }

    // Mueve de esquema temporal a otro
    public static function moverEsquema($de_esquema,$a_esquema)
    {
    try {
      return (DB::unprepared('ALTER SCHEMA  "'.$de_esquema.'" RENAME TO "'.$a_esquema.'"'));
    }catch (QueryException $exception) {
    if ($exception->getCode() == '42P06'){
      Log::debug('Ya existe el Esquema. Intento mover tablas ARC y LAB');
      try{
          DB::beginTransaction();
          (DB::unprepared('ALTER TABLE  "'.$de_esquema.'".arc SET SCHEMA "'.$a_esquema.'" '));
          (DB::unprepared('ALTER TABLE  "'.$de_esquema.'".lab SET SCHEMA "'.$a_esquema.'" '));
          DB::commit();
      }catch (QueryException $exception) {
           if ($exception->getCode() == '42P07'){
             Log::warning('Ya hay tablas cargadas, se pisarán los datos! ');
             DB::Rollback();
             try{
                    DB::beginTransaction();
                    DB::unprepared('DROP TABLE IF EXISTS '.$a_esquema.'.arc CASCADE');
                    DB::unprepared('DROP TABLE IF EXISTS '.$a_esquema.'.lab CASCADE');
                    DB::unprepared('ALTER TABLE  "'.$de_esquema.'".arc SET SCHEMA "'.$a_esquema.'" ');
                    DB::unprepared('ALTER TABLE  "'.$de_esquema.'".lab SET SCHEMA "'.$a_esquema.'" ');
                    DB::unprepared('DROP SCHEMA "'.$de_esquema.'"');
                    DB::commit();
                  Log::info('Se movieron tablas ARC Y LAB a '.$a_esquema.' y se borro el esquema '.$de_esquema);
              }catch (QueryException $exception) {
                Log::error('Error: '.$exception);
                DB::Rollback();
              }
          }
      }

    }else{
              Log::error('Error: '.$exception);
    }
   }
  }

    // Copia de esquema temporal a otro
    //
    public static function copiaraEsquema($de_esquema,$a_esquema)
    {
        try {
             DB::beginTransaction();
             DB::unprepared('DROP TABLE IF EXISTS '.$a_esquema.'.arc CASCADE');
             DB::unprepared('DROP TABLE IF EXISTS '.$a_esquema.'.lab CASCADE');
             DB::unprepared('CREATE TABLE "'.$a_esquema.'".arc AS SELECT * FROM "'.$de_esquema.'".arc ');
             DB::unprepared('CREATE TABLE "'.$a_esquema.'".lab AS SELECT * FROM "'.$de_esquema.'".lab ');
             DB::commit();
         }catch (QueryException $exception) {
             DB::Rollback();
             Log::error('Error: '.$exception);
        }
    }

    public static function getEntidades($tabla,$esquema,$localidad=null)
    {
  if(isset($localidad)) {
      $filtro=" WHERE codprov||coddepto||codloc= '".$localidad->codigo."'";
  }else{$filtro='';}
        try {
            return (DB::select('SELECT distinct prov||dpto||codloc||codent as codigo, noment as nombre FROM
        '.$esquema.'.'.$tabla.' '.$filtro.' group by 1,2
        order by codprov||coddepto||codloc||codent asc, count(*) desc ;'));
        }catch (\Illuminate\Database\QueryException $exception) {
            Log::error('Error: '.$exception);
      return null;
  }
    }

    public static function procesarPxRad($tabla,$esquema)
    {
      try {
        $resumen = DB::select('SELECT * FROM
                   '.$esquema.'.'.$tabla.' limit 1;');
        Log::debug('Se pudo leer el registro en '.$tabla.' . Ejemplo : '.
          (collect($resumen)->toJson(JSON_UNESCAPED_UNICODE))
        );
            }catch (\Illuminate\Database\QueryException $exception) {
      Log::error('No se cargó correctamente la PxRad: '.$exception);
      flash( $resumen='NO se cargó correctamente la PxRad')->error()->important();
      }
      try {
      $radios = DB::select('SELECT codprov, coddepto, codloc, codent, codaglo,
        frac2001, radio2001,
                    frac2010, radio2010, tiporad10,
                    tiporad20, frac2020, radio2020, tiporad20,
                    nomloc, noment
                   FROM
       '.$esquema.'.'.$tabla.' ;');
      $resumen = DB::select('SELECT array_agg(distinct codprov) prov,
        array_agg( distinct codprov|| coddepto) depto,
        array_length( array_agg( distinct codprov|| coddepto|| codloc),1) localidades,
        array_length( array_agg( distinct codprov|| coddepto|| frac2020),1) frac2020,
        array_length( array_agg( distinct codprov|| coddepto|| frac2020 || radio2020),1) rad2020 FROM
                   '.$esquema.'.'.$tabla.' ;');

       flash('Resumen de lo cargado: '.collect($resumen)->toJson());
            }catch (\Illuminate\Database\QueryException $exception) {
        Log::error('No se valido correctamente la PxRad: ('.collect($resumen)->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).' )' .$exception);
        flash('No se valido correctamente la PxRad ')->error()->important();
      }
      try{
        $resumen=DB::select('SELECT codprov,coddepto,codloc,count(*) as radios FROM
        '.$esquema.'.'.$tabla.' GROUP BY codprov,coddepto,codloc ;');
            }catch (\Illuminate\Database\QueryException $exception) {
        Log::error('No se cargó correctamente la PxRad: ('.collect($resumen)->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).') ' .$exception);
      flash( $resumen='NO se cargó correctamente la PxRad')->error()->important();
      }
    return collect($resumen)->toJson();
    }

//         $tabla = strtolower( substr($file_name,strrpos($file_name,'/')+1,-4) );
    public static function moverDBF($file_name,$esquema)
    {
        self::createSchema($esquema);
        $tabla = strtolower( substr($file_name,strrpos($file_name,'/')+1,-4) );
        $esquema = 'e'.$esquema;
        Log::debug('Cargando dbf en esquema-> '.$esquema);
            DB::beginTransaction();
//            DB::unprepared('ALTER TABLE "'.$tabla.'" SET SCHEMA '.$esquema);
            DB::unprepared('CREATE TABLE "'.$esquema.'"."'.$tabla.'" AS SELECT * FROM "'.$tabla.'"');
            DB::unprepared('DROP TABLE IF EXISTS '.$esquema.'.listado CASCADE');
            DB::unprepared('ALTER TABLE "'.$esquema.'"."'.$tabla.'" RENAME TO listado');
            DB::unprepared('ALTER TABLE "'.$esquema.'".listado ADD COLUMN id serial');
            if (! Schema::hasColumn($esquema.'.listado' , 'tipoviv')){
                if (Schema::hasColumn($esquema.'.listado' , 'cod_tipo_2')){
                    DB::unprepared('ALTER TABLE '.$esquema.'.listado RENAME cod_tipo_2 TO tipoviv');
                }elseif (Schema::hasColumn($esquema.'.listado' , 'cod_tipo_v')){
                        DB::unprepared('ALTER TABLE '.$esquema.'.listado RENAME cod_tipo_v TO tipoviv');
                }elseif (Schema::hasColumn($esquema.'.listado' , 'cod_viv')){
                        DB::unprepared('ALTER TABLE '.$esquema.'.listado RENAME cod_viv TO tipoviv');
                    }elseif (Schema::hasTable($esquema.'.listado')){
                        DB::statement('ALTER TABLE '.$esquema.'.listado ADD COLUMN tipoviv text;');
                    }
            }
            if (! Schema::hasColumn($esquema.'.listado' , 'cod_subt_v')){
                if (Schema::hasColumn($esquema.'.listado' , 'cod_subt_2')){
                    DB::unprepared('ALTER TABLE '.$esquema.'.listado RENAME
                    cod_subt_2 TO cod_subt_v');
                }else{
                  DB::statement('ALTER TABLE '.$esquema.'.listado ADD COLUMN cod_subt_v text;');
                  Log::debug('No se encontró cod_subt_v, se agrega');
                }
            }elseif (Schema::hasColumn($esquema.'.listado' , 'cod_subt_2')){
                  DB::statement('ALTER TABLE '.$esquema.'.listado ADD COLUMN
                    cod_subt_v_original text ;');

                  DB::statement("UPDATE ".$esquema.".listado SET
                  cod_subt_v_original=cod_subt_v,
                    cod_subt_v=COALESCE(nullif(trim(cod_subt_2),''),nullif(trim(cod_subt_v),''));");
                  Log::debug('Se encontró cod_subt_v y cod_subt_2, se usa el _2
                    y si esta vacio se usa el _v');
                }

            else{
                  DB::statement('UPDATE '.$esquema.'.listado SET
                  cod_subt_v=trim(cod_subt_v);');
                  Log::debug('Se quitan espacios en cod_subt_v');
            }


            if (! Schema::hasColumn($esquema.'.listado' , 'codent')){
                        DB::statement('ALTER TABLE '.$esquema.'.listado ADD
                        COLUMN codent text;');
            }
            if (! Schema::hasColumn($esquema.'.listado' , 'nom_ent')){
                        DB::statement('ALTER TABLE '.$esquema.'.listado ADD
                        COLUMN nom_ent text;');
            }
            if (! Schema::hasColumn($esquema.'.listado' , 'piso')){
                    if (  Schema::hasColumn($esquema.'.listado' , 'pisoredef')){
                        DB::unprepared('ALTER TABLE '.$esquema.'.listado RENAME pisoredef TO piso');
                    }else{
                        DB::statement('ALTER TABLE '.$esquema.'.listado ADD
                            COLUMN piso text;');
                    }
      }elseif (Schema::hasColumn($esquema.'.listado' , 'pisoredef')){
                  DB::statement('ALTER TABLE '.$esquema.'.listado ADD COLUMN
                   piso_original text ;');

                  DB::statement("UPDATE ".$esquema.".listado SET
                  piso_original=piso,
                    piso=COALESCE(nullif(trim(pisoredef),''),nullif(trim(piso),''));");
                  Log::debug('Se encontró piso y pisoredef, se usa el pisoredef
                    y si esta vacio se usa el piso');

      }



                if (! Schema::hasColumn($esquema.'.listado' , 'nrocatastr')){
                        if (Schema::hasColumn($esquema.'.listado' , 'nro_catast')){
                        DB::unprepared('ALTER TABLE '.$esquema.'.listado RENAME
                        nro_catast TO nrocatastr');
                        }else{
                        DB::statement('ALTER TABLE '.$esquema.'.listado ADD
                            COLUMN nrocatastr text;');
                        }
                }


                if (! Schema::hasColumn($esquema.'.listado' , 'descripci2')){
                            DB::statement('ALTER TABLE '.$esquema.'.listado ADD
                                COLUMN descripci2 text;');
                }

                if (Schema::hasColumn($esquema.'.listado' , 'mza')){
                            $resultado = DB::statement('UPDATE '.$esquema.'.listado SET
                            mza=right(mza,3);');
                            Log::debug('Se adaptaron lados tomando 3 ultimos caractares ignorando primera letra -> '.$resultado);

                }

                // Tomo nro_listado en lugar de orden reco para CABA
                    if (MyDB::getProv('listado',$esquema)=='02'){
                        Log::debug('Se detecto CABA');
                        if ( Schema::hasColumn($esquema.'.listado' , 'nro_listad')){
                            Log::debug('campo nro_listad');
                            if ( Schema::hasColumn($esquema.'.listado' , 'orden_reco')){
                                DB::statement('ALTER TABLE '.$esquema.'.listado
                                RENAME COLUMN orden_reco TO orden_reco_bak;');
                                Log::debug('Se backupea orden_reco original');
                            }
                                DB::statement('ALTER TABLE '.$esquema.'.listado
                                RENAME COLUMN nro_listad TO orden_reco;');
                                DB::statement('ALTER TABLE '.$esquema.'.listado ADD
                                    COLUMN nro_listad integer;');
                                Log::debug('Se cambia orden_reco x nro_listado');
                        }
                }

                DB::commit();
            self::juntaListadoGeom($esquema);

        }

        public static function juntaListadoGeom($esquema){
            if (Schema::hasTable($esquema.'.arc') and Schema::hasTable($esquema.'.listado')){
                Log::debug('Geometrias y listado cargado, se comienza posprocesos');
                // Comienzan posprocesos de carga
                DB::beginTransaction();
                    try {
                        if (! Schema::hasColumn($esquema.'.arc' , 'nomencla10')){
                                    DB::statement('ALTER TABLE '.$esquema.'.arc ADD COLUMN nomencla10 text;');
                                    Log::debug('Invento nomencla10 en arc de esquema '.$esquema);
                        }
                        if (! Schema::hasColumn($esquema.'.listado' , 'nomencla10')){
                                    DB::statement('ALTER TABLE '.$esquema.'.listado ADD COLUMN nomencla10 text;');
                                    Log::debug('Invento nomencla10 en listado de esquema '.$esquema);
                        }
                        if (! Schema::hasColumn($esquema.'.arc' , 'segi')){
                                    DB::statement('ALTER TABLE '.$esquema.'.arc ADD COLUMN IF NOT EXISTS segi integer;');
                        }
                        if (! Schema::hasColumn($esquema.'.arc' , 'segd')){
                                DB::statement('ALTER TABLE '.$esquema.'.arc ADD COLUMN IF NOT EXISTS segd integer;');
                        }
                    }catch (\Illuminate\Database\QueryException $exception) {
                            Log::error('No se pudieron cargar lados '.$exception);
                            DB::Rollback();
                    };
                DB::commit();
                DB::beginTransaction();
                    try {
                        DB::unprepared("Select indec.cargar_lados('".$esquema."')");
                        DB::unprepared("Select indec.cargar_conteos('".$esquema."')");
                        DB::unprepared("Select indec.generar_adyacencias('".$esquema."')");
                        Log::info('Se procesaron lados, conteos y adyacencias!');
                    }catch (\Illuminate\Database\QueryException $exception) {
                            Log::error('No se pudieron cargar lados '.$exception);
                            DB::Rollback();
                    };
                DB::commit();
                // Comienzan posprocesos de carga
                DB::beginTransaction();
                    try {
                        DB::unprepared("Select indec.descripcion_segmentos('".$esquema."')");
                    }catch (\Illuminate\Database\QueryException $exception) {
                        Log::error('No se pudo crear la descripcion de los segmentos: '.$exception);
                        DB::Rollback();
                    }
                self::addSequenceSegmentos($esquema);
                self::generarSegmentacionNula($esquema);

                DB::commit();

            // Indices y georef.
            $schema=$esquema;
            self::addIndexListado($schema);
            flash('Se creo el indice para lados en listado en '.$schema);
            self::addIndexListadoId($schema);
            flash('Se creo el indice para id listado en '.$schema);
            self::addIndexListadoRadio($schema);
            flash('Se creo el indice para radio en listado en '.$schema);

            if (self::cargarTopologia($schema)) {
                flash('Se creo la topología para '.$schema);
            }

            self::georeferenciar_listado($schema);
            flash('Se georeferencio el listado del esquema '.$schema);


            }
        }

        public static function borrarTabla($tabla)
        {
        // Borrar tabla "temporal"
        try {
               DB::beginTransaction();
              DB::statement('DROP TABLE "'.$tabla.'" CASCADE;');
              DB::commit();
               Log::info('Se eliminó el tabla '.$tabla);
              return true;
                }catch (\Illuminate\Database\QueryException $exception) {
                    Log::error('No se pudo borrar la tabla: '.$exception);
                    DB::Rollback();
              return false;
                }
        }

        public static function limpiar_esquema($esquema)
        {
        // Comienzan limíeza de esquema
        try {
               DB::beginTransaction();
              DB::statement('DROP SCHEMA "'.$esquema.'" CASCADE;');
              DB::commit();
               Log::info('Se eliminó el esquema '.$esquema);
              return true;
                }catch (\Illuminate\Database\QueryException $exception) {
                    Log::error('No se pudo limpiar el esquema: '.$exception);
                    DB::Rollback();
              return false;
                }
        }

        public static function agregarsegisegd(String $esquema)
        {
            if (Schema::hasTable('e'.$esquema.'.arc')) {
            DB::statement('ALTER TABLE e'.$esquema.'.arc ADD COLUMN IF NOT EXISTS segi integer;');
            DB::statement('ALTER TABLE e'.$esquema.'.arc ADD COLUMN IF NOT EXISTS segd integer;');
            return true;
            }
            else{
            return false;
            }
        }


        public static function generarSegmentacionNula($esquema)
        {
            if (Schema::hasTable($esquema.'.listado')) {
            DB::statement('create TABLE if not exists '.$esquema.'.segmentacion as
                select id as listado_id, Null::integer as segmento_id
                from '.$esquema.'.listado
                ;');
            return true;
            }
            else{
            return false;
            }
        }

        public static function generarSegmentacionVacia($esquema)
        {
            if (Schema::hasTable('e'.$esquema.'.listado')) {
            DB::statement('create TABLE if not exists
                e'.$esquema.'.segmentacion (listado_id integer, segmento_id integer)
                ;');
            return true;
            }
            else{
            return false;
            }
        }

        public static function generarR3Vacia($esquema)
        {
            if (! Schema::hasTable('e'.$esquema.'.r3')) {
            DB::statement('create TABLE if not exists
                e'.$esquema.".r3 as select * from indec.describe_segmentos_con_direcciones_ffrr('e".$esquema."', 0, 0)
                ;"); // crea la R3 con una consulta que devuelve la esctructura vacía
            return true;
            }
            else{
            return false;
            }
        }

        public static function grabarSegmentacion($esquema,$frac=null,$radio=null)
        {
            if ($frac!=null) {
              try{
                 DB::statement("select indec.sincro_r3_ffrr('e".$esquema."', $frac, $radio);");
              }catch(QueryException $e){
                 Log::error($e);
                 return false;
              }
        // guarda indec.describe_segmentos_con_direcciones_ffrr en esquema.r3 (hace delete & insert)a
      }else{
        DB::statement("SELECT indec.sincro_r3('e".$esquema."');");
            }
            Log::info('Se actualizó la R3!');
            return true;
        }


        public static function segmentar_equilibrado($esquema,$deseado = 10)
        {
            $AppUser= Auth::user();
            $processLog = Process::fromShellCommandline('echo "$tiempo: $usuario_name ($usuario_id) -> va a segmentar a manzana independiente: $info_segmenta"  >> segmentaciones.log');
            $processLog->run(null, ['info_segmenta' => " Aglomerado: ".$esquema ,
                                    'usuario_id' => $AppUser->id,
                                    'usuario_name' => $AppUser->name,
                                    'tiempo' => date('Y-m-d H:i:s')]);

            try{
                self::addSequenceSegmentos('e'.$esquema,false);
                self::generarSegmentacionNula('e'.$esquema);
                if ( DB::statement("SELECT indec.segmentar_equilibrado('e".$esquema."',".$deseado.");") ){
                // llamar generar r3 como tabla resultado de function indec.r3(agl)
                    ( DB::statement("SELECT indec.descripcion_segmentos('e".$esquema."');") );
                    ( DB::statement("SELECT indec.segmentos_desde_hasta('e".$esquema."');") );
//               self::georeferenciar_segmentacion($esquema);
    //
                 flash('Resultado: '.self::juntar_segmentos('e'.$esquema));
             // Llamar a función guardar segmentación para actualizar la r3 con los resultados...
                 // $esquema para el esquema completo.
             self::grabarSegmentacion($esquema);
                    return true;
                }else{
                    return false; }
            }catch (QueryException $e){
                  LOG::error('Se produjo algún error luego de segmentar equilibrado a manzanas independientes '.$e);
      flash('Se produjo algún error luego de segmentar equilibrado a manzanas independientes')->error()->important();
                  return false;
            }


        // SQL retrun: Select segmento_id,count(*) FROM e0777.segmentacion GROUP BY segmento_id;
        }

        public static function
        segmentar_equilibrado_ver($esquema,$max=1000,Radio $radio = Null)
        {
            $esquema = 'e'.$esquema;
            if ($radio){
                $esquema=$radio->esquema;
                $filtro= ' where (frac::integer,radio::integer) =
                    ('.$radio->CodigoFrac.','.$radio->CodigoRad.') ';
                if (Schema::hasTable($esquema.'.r3')){
                  $funcion_describe= ' "'.$esquema.'".r3 '.$filtro;
                }else{
                  $funcion_describe= " indec.describe_segmentos_con_direcciones_ffrr('".$esquema."',".$radio->CodigoFrac.",".$radio->CodigoRad.") ";
                }
            }else{ 
              $filtro = '';
              if (Schema::hasTable($esquema.'.r3')){
                $funcion_describe= ' "'.$esquema.'".r3 ';
              }else{
                $funcion_describe= " indec.describe_segmentos_con_direcciones('".$esquema."') ";
              }
            }

            try{
                return DB::select("SELECT segmento_id, lpad(frac::text,2,'0') frac,
                        lpad(radio::text,2,'0') radio, viviendas vivs,
                            descripcion detalle, lpad(seg,2,'0') seg, null ts
                            FROM
                            indec.describe_despues_de_muestreo('".$esquema."')
                            ".$filtro." ;");
                }catch(QueryException $e){
                    Log::info('Sin muestreo...');
            try{
                return DB::select("
                        SELECT segmento_id, lpad(frac::text,2,'0') frac,
                        lpad(radio::text,2,'0') radio, viviendas vivs,
                            descripcion detalle, lpad(seg,2,'0') seg, null ts
                            FROM
                            ".$funcion_describe."
                            order by frac,radio,seg,segmento_id
                            LIMIT ".$max.";");
                }catch(QueryException $e){
                    Log::warning('Se detecto una carga medio antigua. Se encontro tabla de
                    "segmentos desde hasta". Pero sin vivendas... Se hace lo
                    que se puede.');
                    try{
                        return DB::select("SELECT segmento_id, frac, radio, mza, lado,
                            CASE  WHEN completo THEN 'Lado Completo'
                            ELSE 'Desde ' ||
                            indec.descripcion_domicilio('".$esquema."',seg_lado_desde) || '
                                hasta ' ||
                                indec.descripcion_domicilio('".$esquema."',seg_lado_hasta)
                            END detalle,
                            null vivs, segmento_id seg, null ts
                            FROM ".$esquema.".segmentos_desde_hasta
                            ".$filtro."
                            order by frac,radio,segmento_id,mza,lado
                            LIMIT ".$max.";");
                    }catch(QueryException $e){

                        Log::warning('Se detecto una carga antigua. No se encontro tabla de
                            "segmentos desde hasta". Se hace lo que se puede.');
                        try{
                        return DB::select('
                            SELECT segmento_id,l.frac,l.radio,count(*)
                            vivs,count(distinct mza) as mza,array_agg(distinct
                            prov||dpto||codloc||frac||radio||mza||lado)
                            detalle,count(distinct lado) as lado,
                            segmento_id seg,
                            null ts
                            FROM
                            '.$esquema.'.segmentacion s JOIN
                            '.$esquema.'.listado l ON s.listado_id=l.id
                            '.$filtro.'
                            GROUP BY segmento_id,l.frac,l.radio
                            ORDER BY count(*) asc, array_agg(mza), segmento_id
                            LIMIT '.$max.';');
                        }catch(QueryException $e){
                            Log::error('No hubo modo de encontrar una segmentación! '.$e);
                            return [];
                        }
                }
            }
            }
        }

        public static function segmentar_equilibrado_ver_resumen($esquema)
        {
            $esquema = 'e'.$esquema;
            return DB::select('SELECT vivs,count(*) cant_segmentos,
                            string_agg(distinct array_to_string(en_lados,\' \'),\',\') en_lados FROM (
                            SELECT segmento_id,count(indec.contar_vivienda(tipoviv)) vivs,count(distinct mza) as
                            mzas,array_agg(distinct
                            frac||radio) en_lados,count(distinct lado) as lados FROM
                            '.$esquema.'.
                            segmentacion s JOIN
                            '.$esquema.'.
                            listado l ON s.listado_id=l.id
                            GROUP BY segmento_id
                            ORDER BY count(*) asc, array_agg(mza), segmento_id) foo GROUP BY vivs order by vivs asc;');
        // SQL retrun:
        }

        public static function segmentar_lados_ver($esquema)
        {
            $esquema = 'e'.$esquema;
            return DB::select('
                            SELECT substr(lados.mza,1,12) radio, lados.seg,count(*)
                            lados,count(distinct lados.mza) as
                            mzas_count,array_agg(distinct substr(lados.mza,13,3))
                            mzas,sum(conteo) as vivs, d.descripcion FROM
                (SELECT segi seg,mzai mza,ladoi lado FROM '.$esquema.'.arc WHERE segi is not null
                UNION SELECT segd,mzad,ladod FROM '.$esquema.'.arc WHERE segd is not null) lados
                        JOIN  '.$esquema.'.conteos c ON (c.prov,c.dpto,c.codloc,c.frac,c.radio,c.mza,c.lado)=(
                        substr(lados.mza,1,2)::integer,substr(lados.mza,3,3)::integer,substr(lados.mza,6,3)::integer,
                        substr(lados.mza,9,2)::integer,substr(lados.mza,11,2)::integer,substr(lados.mza,13,3)::integer,lados.lado::integer)
                        JOIN  '.$esquema.'.descripcion_segmentos d ON
                            (d.prov::integer,d.depto::integer,d.codloc::integer,d.frac::integer,d.radio::integer,d.seg)=(
                            substr(lados.mza,1,2)::integer,substr(lados.mza,3,3)::integer,substr(lados.mza,6,3)::integer,
                            substr(lados.mza,9,2)::integer,substr(lados.mza,11,2)::integer,lados.seg::integer)
                                WHERE substr(lados.mza,1,12)!=\'\'
                                GROUP BY  substr(lados.mza,1,12), lados.seg,descripcion');
            // SQL retrun:
            }

            public static function segmentar_lados_ver_resumen($esquema)
            {
                $esquema = 'e'.$esquema;
                return DB::select('SELECT vivs,count(seg) cant_segmentos FROM (
                    SELECT substr(lados.mza,1,12) radio, seg,count(*) lados,count(distinct lados.mza) as mzas_count,array_agg(distinct substr(lados.mza,13,3)) mzas, sum(c.conteo) vivs FROM
                    (SELECT segi seg,mzai mza,ladoi lado FROM '.$esquema.'.arc WHERE segi is not null
                    UNION SELECT segd,mzad,ladod FROM '.$esquema.'.arc WHERE segd is not null) lados
                                JOIN '.$esquema.'.conteos c ON (c.prov,c.dpto,c.codloc,c.frac,c.radio,c.mza,c.lado)=(
                                                                substr(lados.mza,1,2)::integer,substr(lados.mza,3,3)::integer,substr(lados.mza,6,3)::integer,
                                                                substr(lados.mza,9,2)::integer,substr(lados.mza,11,2)::integer,substr(lados.mza,13,3)::integer,lados.lado::integer)
                                WHERE lados.mza != \'\'
                                GROUP BY  substr(lados.mza,1,12), seg ) foo
                                GROUP BY vivs order by vivs asc;');
            // SQL retrun:
            }

            public static function georeferenciar_listado($esquema)
            {
        //   --ALTER TABLE ' ".$esquema." '.arc alter column wkb_geometry type geometry('LineString',22182) USING (st_setsrid(wkb_geometry,22182));

            try{

                DB::statement("DROP TABLE IF EXISTS ".$esquema.".listado_geo;");
      $query="
                WITH listado as (
            SELECT id, l.prov, nom_provin, l.dpto, nom_dpto, l.codaglo, l.codloc,
                nom_loc, codent, nom_ent, l.frac, l.radio, l.mza, l.lado,
                CASE WHEN nro_inicia='' THEN 0 ELSE nro_inicia::integer END
                ::integer as nro_inicia,
                CASE WHEN nro_final='' THEN 0 ELSE nro_final::integer END
                ::integer as nro_final,
                CASE WHEN orden_reco='' THEN 0 ELSE orden_reco::integer END ::integer as orden_reco,
                nro_listad, ccalle, ncalle,
                CASE WHEN l.nrocatastr='' or l.nrocatastr='S/N' THEN null::integer ELSE
                l.nrocatastr::integer END nrocatastr,
            piso, casa, dpto_habit, sector, edificio, entrada, tipoviv, descripcio, descripci2 ,
            row_number() over w_lado as nro_en_lado,
            count(*) over w_lado as cant_en_lado,
            count(*) over w as conteo,
            conteo as conteo_vivs,
            row_number() over w_nrocatastr as nro_en_numero

            FROM
            ".$esquema.".listado l
            LEFT JOIN ".$esquema.".conteos c ON
            (c.prov,c.dpto,c.codloc,c.frac,c.radio,c.mza,c.lado)=
            (l.prov::integer,l.dpto::integer,l.codloc::integer,l.frac::integer,l.radio::integer,l.mza::integer,l.lado::integer)
            WINDOW w_nrocatastr AS (partition by l.frac, l.radio, l.mza, l.lado ,
            nrocatastr
            order by CASE WHEN orden_reco='' THEN 1::integer ELSE
            orden_reco::integer END asc),
            w_lado AS (partition by l.frac, l.radio, l.mza, l.lado order by
            CASE WHEN orden_reco='' THEN 1::integer ELSE
            orden_reco::integer END asc),
            w AS (partition by l.frac, l.radio, l.mza, l.lado)

        ),
        arcos as (
      SELECT min(ogc_fid) ogc_fid, st_LineMerge(st_union(wkb_geometry)) wkb_geometry,
                   nomencla,codigo20,array_agg(distinct codigo10) codigo10, tipo, nombre,lado,min(desde) desde,
            max(hasta) hasta,mza
            FROM
      (SELECT ogc_fid,st_reverse(wkb_geometry) wkb_geometry,nomencla10 nomencla,codigo20,codigo10,
             tipo, nombre, ancho, anchomed, ladoi lado,desdei desde,
        hastai hasta,mzai mza, nomencla10,nomenclai nomenclax, codinomb, segi seg
        FROM ".$esquema.".arc
        UNION
  SELECT ogc_fid,wkb_geometry,nomencla10 nomencla,codigo20,codigo10,tipo, nombre,
               ancho, anchomed, ladod lado,desded desde,
               hastad hasta,mzad mza, nomencla10,nomenclad nomenclax, codinomb, segd seg
        FROM ".$esquema.".arc
        ) arcos_juntados
        GROUP BY nomencla,codigo20,tipo, nombre,lado,mza
        HAVING
        st_geometrytype(st_LineMerge(st_union(wkb_geometry)))='ST_LineString'
        and mza!=''
    )
    SELECT nro_en_lado, nro_en_numero, conteo,1.0*nro_en_lado/(conteo+1) interpolacion, l.orden_reco,
    case when 1.0*nro_en_lado/(conteo+1)>1 then
        ST_LineInterpolatePoint(st_reverse(st_offsetcurve(ST_LineSubstring(st_LineMerge(wkb_geometry),0.07,0.93),-8-nro_en_lado)),0.5)
    else
    CASE WHEN (
            e.mza like '%'||btrim(to_char(l.frac::integer, '09'::text))::character varying(3)||btrim(to_char(l.radio::integer, '09'::text))::character varying(3)||btrim(to_char(l.mza::integer, '099'::text))::character varying(3))
                    and l.lado::integer=e.lado and (l.tipoviv='LSV' or
                    l.tipoviv='')
                    THEN
                    ST_LineInterpolatePoint(st_reverse(st_offsetcurve(ST_LineSubstring(st_LineMerge(wkb_geometry),0.07,0.93),-8-(0.5*nro_en_numero))),0.5)
            WHEN ( e.mza like '%'||btrim(to_char(l.frac::integer, '09'::text))::character varying(3)||btrim(to_char(l.radio::integer, '09'::text))::character varying(3)||btrim(to_char(l.mza::integer, '099'::text))::character varying(3))
                    and l.lado::integer=e.lado
                    THEN
                    ST_LineInterpolatePoint(st_reverse(st_offsetcurve(ST_LineSubstring(st_LineMerge(wkb_geometry),0.07,0.93),-8-(0.5*nro_en_numero))),1.0*(nro_en_lado)/(conteo+1))
                end
                END as wkb_geometry, e.ogc_fid||'-'||l.id id ,e.ogc_fid id_lin,l.id id_list, wkb_geometry wkb_geometry_lado,
    CASE WHEN nro_final::integer-nro_inicia::integer>0 and (nrocatastr)>0 THEN
    row_number() OVER (PARTITION BY prov,dpto,codloc,frac,radio,l.mza,l.lado
    ORDER BY l.nrocatastr,l.piso)
    END orden_segun_numero,
    row_number() OVER (PARTITION BY prov,dpto,codloc,frac,radio,l.mza,l.lado, l.nrocatastr ORDER BY l.piso)
    orden_en_numero,

    CASE WHEN nro_final::integer-nro_inicia::integer>0 and (nrocatastr)>0 THEN
        CASE
        WHEN (((nrocatastr::integer-nro_inicia::integer)::numeric/(nro_final::integer-nro_inicia::integer)<0
                or (nrocatastr::integer-nro_inicia::integer)::numeric/(nro_final::integer-nro_inicia::integer)>1 )) THEN
            ST_LineInterpolatePoint(st_reverse(st_offsetcurve(ST_LineSubstring(st_LineMerge(wkb_geometry),0.07,0.93),-8)),0.5)
            ELSE
            ST_LineInterpolatePoint(st_reverse(st_offsetcurve(ST_LineSubstring(st_LineMerge(wkb_geometry),0.07,0.93),-8)),1-
                                    (nrocatastr::integer-nro_inicia::integer)::numeric/(nro_final::integer-nro_inicia::integer))
        END
    ELSE
    ST_LineInterpolatePoint(st_reverse(st_offsetcurve(ST_LineSubstring(st_LineMerge(wkb_geometry),0.07,0.93),-8)),
        0.5 --deberia usarse la posicion del anterior.. tiro null quizas ?
        )
        END geom_segun_nro_catastral,

                    codigo10, nomencla, codigo20,
                    tipo, nombre, e.lado ladoe, desde, hasta,e.mza mzae,
                    frac, radio, l.mza, l.lado, ccalle, ncalle, l.nrocatastr, piso,casa,dpto_habit,sector,edificio,entrada,tipoviv,
                    descripcio,descripci2,
                    cant_en_lado
        INTO ".$esquema.".listado_geo
        FROM arcos e JOIN listado l ON
        --l.ccalle::integer=e.codigo20 and
            (l.lado::integer=e.lado and
                e.mza like
                '%'||btrim(to_char(l.frac::integer, '09'::text))::character varying(3)||btrim(to_char(l.radio::integer, '09'::text))::character varying(3)||btrim(to_char(l.mza::integer, '099'::text))::character varying(3)
            );";
            Log::debug(' Georreferenciando: '.$query);

      $resultado= DB::select($query);

            }catch(QueryException $e){
                    Log::error('No se pudo georeferenciar el listado.'.$e);
                        flash('No se pudo georeferenciar el listado.
                        Reintente.')->error()->important();
                    return false;
            }
            try{
                DB::statement("GRANT SELECT ON TABLE  ".$esquema.".listado_geo TO geoestadistica");
            }catch(QueryException $e){
                Log::error('No se pudo dar permiso a geoestadistica sobre el listado.'.$e);
            }

                return $resultado;


            }

        public static function geo_translate($esquema)
        {
            try{
                DB::statement("UPDATE ".$esquema.".listado_geo SET
                    wkb_geometry=st_translate(wkb_geometry,-50,50),
                    geom_segun_nro_catastral=st_translate(geom_segun_nro_catastral,-50,50)
                    ;");
            }catch(QueryException $e){
                    Log::error('No se pudo trasladar la geometria.'.$e);
                        flash('No se pudo trasladar geometria')->error();
                    return false;
            }
                return true;
        }

            public static function georeferenciar_segmentacion($esquema)
            {
                //return true;
        //   --ALTER TABLE ' ".$esquema." '.arc alter column wkb_geometry type geometry('LineString',22182) USING (st_setsrid(wkb_geometry,22182));
                $esquema = 'e'.$esquema;
                DB::statement("DROP TABLE IF EXISTS ".$esquema.".listado_segmentado_geo;");
                $resultado= DB::select("
                WITH listado as (
            SELECT id, l.prov, nom_provin, ups, nro_area, l.dpto, nom_dpto, l.codaglo, l.codloc, nom_loc, codent, nom_ent, l.frac, l.radio, l.mza, l.lado,
            s.segmento_id as segmento_id, nro_inicia, nro_final, orden_reco,
            nro_listad, ccalle, ncalle,
            CASE WHEN l.nrocatastr='' or l.nrocatastr='S/N' THEN null::integer ELSE
            l.nrocatastr::integer END as nrocatastr,
            piso, casa, dpto_habit, sector, edificio, entrada, tipoviv, descripcio, descripci2 ,
            row_number() over(partition by l.frac, l.radio, l.mza, l.lado order by l.lado, orden_reco asc) nro_en_lado, conteo,
            FROM
            ".$esquema.".listado l
            JOIN ".$esquema.".segmentacion s ON s.listado_id=l.id
            LEFT JOIN ".$esquema.".conteos c ON
            (c.prov,c.dpto,c.codloc,c.frac,c.radio,c.mza,c.lado)=(l.prov::integer,l.dpto::integer,l.codloc::integer,l.frac::integer,l.radio::integer,l.mza::integer,l.lado::integer)
        ),
        arcos as (
            SELECT min(ogc_fid) ogc_fid, st_LineMerge(st_union(wkb_geometry)) wkb_geometry,nomencla,codigo20,array_agg(distinct codigo10) codigo10, tipo, nombre,lado,min(desde) desde,
            max(hasta) hasta,mza
            FROM
            (SELECT ogc_fid,st_reverse(wkb_geometry) wkb_geometry,nomencla10 nomencla,codigo20,codigo10,tipo, nombre, ancho, anchomed, ladoi lado,desdei desde,
            hastai hasta,mzai mza, nomencla10,nomenclai nomenclax, codinomb, segi seg
            FROM ".$esquema.".arc
        UNION
            SELECT ogc_fid,wkb_geometry,nomencla10 nomencla,codigo20,codigo10,tipo, nombre, ancho, anchomed, ladod lado,desded desde,
            hastad hasta,mzad mza, nomencla10,nomenclad nomenclax, codinomb, segd seg
            FROM ".$esquema.".arc) arcos_juntados
            GROUP BY nomencla,codigo20,tipo, nombre,lado,mza
        )
        SELECT segmento_id,nro_en_lado, conteo,1.0*nro_en_lado/(conteo+1) interpolacion, l.orden_reco,
        case when nro_en_lado/(conteo+1)>1
        then ST_LineInterpolatePoint(st_reverse(st_offsetcurve(ST_LineSubstring(st_LineMerge(wkb_geometry),0.07,0.93),-8-nro_en_lado)),0.5)
        else
        CASE WHEN (
            e.mza like '%'||btrim(to_char(l.frac::integer, '09'::text))::character varying(3)||btrim(to_char(l.radio::integer, '09'::text))::character varying(3)||btrim(to_char(l.mza::integer, '099'::text))::character varying(3))
                    and l.lado::integer=e.lado and l.tipoviv='LSV'
                    THEN ST_LineInterpolatePoint(st_reverse(st_offsetcurve(ST_LineSubstring(st_LineMerge(wkb_geometry),0.07,0.93),-8)),0.5)
            WHEN ( e.mza like '%'||btrim(to_char(l.frac::integer, '09'::text))::character varying(3)||btrim(to_char(l.radio::integer, '09'::text))::character varying(3)||btrim(to_char(l.mza::integer, '099'::text))::character varying(3))
                    and l.lado::integer=e.lado
                    THEN ST_LineInterpolatePoint(st_reverse(st_offsetcurve(ST_LineSubstring(st_LineMerge(wkb_geometry),0.07,0.93),-8)),1.0*nro_en_lado/(conteo+1))
                end
                END as wkb_geometry, e.ogc_fid||'-'||l.id id ,e.ogc_fid id_lin,l.id id_list, wkb_geometry wkb_geometry_lado,
                    codigo10, nomencla, codigo20,
                        tipo, nombre, e.lado ladoe, desde, hasta,e.mza mzae,
                        frac, radio, l.mza, l.lado, ccalle, ncalle, l.nrocatastr, piso,casa,dpto_habit,sector,edificio,entrada,tipoviv,
                    descripcio,descripci2 ,
        INTO ".$esquema.".listado_segmentado_geo
        FROM arcos e JOIN listado l ON l.ccalle::integer=e.codigo20
        and
            (l.lado::integer=e.lado and
                e.mza like
                '%'||btrim(to_char(l.frac::integer, '09'::text))::character varying(3)||btrim(to_char(l.radio::integer, '09'::text))::character varying(3)||btrim(to_char(l.mza::integer, '099'::text))::character varying(3)
            );");
            DB::statement("GRANT SELECT ON TABLE
            ".$esquema.".listado_segmentado_geo TO geoestadistica");
                return $resultado;
            }

            public static function getNodos($esquema,$radio = '%01103')
            {
                try{
                    return DB::select('SELECT distinct *, substr(mza_i,13,3)||\':\'||lado_i as label,c.conteo FROM (
                                                    SELECT mza_i,lado_i from '.$esquema.'.lados_adyacentes WHERE mza_i like :radio UNION
                                                    SELECT mza_j,lado_j from
                                                    '.$esquema.'.lados_adyacentes
                                                    WHERE mza_j like :radio2) foo
            LEFT JOIN
            '.$esquema.'.conteos c
            ON (c.prov,c.dpto,c.codloc,c.frac,c.radio,c.mza,c.lado)=
                (substr(mza_i,1,2)::integer,
                substr(mza_i,3,3)::integer,
                substr(mza_i,6,3)::integer,
                substr(mza_i,9,2)::integer,
                substr(mza_i,11,2)::integer,
                substr(mza_i,13,3)::integer,
                lado_i)

                                ',['radio'=>$radio.'%','radio2'=>$radio.'%']);
                }catch(QueryException $e){
                    Log::error('No se pudieron obtener los nodos en '.$esquema);
                }
            }

            public static function getAdyacencias($esquema,$radio = '%01103')
            {
                try{
                    return DB::select('SELECT * from '.$esquema.'.lados_adyacentes
                WHERE mza_i like :radio and mza_j like :radio;',['radio'=>$radio.'%']);
                }catch(QueryException $e){
                    Log::error('No se pudieron obtener las adyacencias');
                }
            }

            public static function getSegmentos($esquema,$radio = '%01103')
            {
            try{
                        return DB::select('SELECT array_agg(mza||\'-\'||lado) segmento
                                            FROM
                                            (SELECT
                                                mzai mza,ladoi lado, segi se
                                                FROM '.$esquema.'.arc
                                            UNION
                                                SELECT
                                                mzad mza,ladod lado, segd se
                                                FROM '.$esquema.'.arc
                                            ) segs
                                            WHERE mza like :radio
                                            GROUP BY se
                                            ;',['radio'=>$radio.'%']);
                }catch(QueryException $e){
                    Log::error('No se pudieron obtener los segmentos de los
                    arcos');
                    return [];
                }
                return [];
            }

            public static function getCantMzas(Radio $radio,$esquema=null){
               $esquemas=$radio->Esquemas;
                Log::debug('Buscando Mzas para radio '.$radio->codigo);
                $prov=substr($radio->codigo,0,2);
                $dpto=substr($radio->codigo,2,3);
                $frac=substr($radio->codigo,5,2);
                $rad=substr($radio->codigo,7,2);
                if(isset($esquema)){
                  if (in_array($esquema,$esquemas)){
                    Log::debug('Buscando Mzas para radio '.$radio->codigo.' en esquema'.$esquema);
                  }else{
                    Log::warning('Buscando Mzas para radio '.$radio->codigo.' en esquema '.$esquema.' fuera de lo esperado');
                  }
                  try{
                    return (int) DB::select("
                               SELECT count( distinct mza)  cant_mzas
                               FROM ".$esquema.".conteos WHERE prov=".$prov." and dpto = ".$dpto." and
                               frac=".$frac." and radio=".$rad." ;")[0]->cant_mzas;
                   }catch(QueryException $e){
                            if ($e->getCode() == '42P01'){
                              Log::debug('No existe o hay problemas con es conteos en esquema: '.$esquema);
                          }else{
                              Log::error('No se encontro conteo manzanas para radio '.$radio.$e);
                          }
                   }
                }else{
                    $sumas_mzas=[];
                    foreach($esquemas as $esquema){
                      try{
                        $mzas = (int) DB::select("
                               SELECT count( distinct mza)  cant_mzas
                               FROM ".$esquema.".conteos WHERE prov=".$prov." and dpto = ".$dpto." and
                               frac=".$frac." and radio=".$rad." ;")[0]->cant_mzas;
                         $sumas_mzas[]=$mzas;
                         Log::info('Manzanas para radio '.$radio->codigo.' contadas en equema '.$esquema.' : '.$mzas);
                       }catch(QueryException $e){
                            if ($e->getCode() == '42P01'){
                              Log::debug('No existe o hay problemas con tabla de conteo en esquema: '.$esquema);
                          }else{
                              Log::error('No se encontro conteo manzanas para radio '.$radio.$e);
                          }
                        }
                    }
                    if (count($sumas_mzas)>1){
                        Log::warning('Se encontraron mzas en mas de un esquema');
                        return max($sumas_mzas);
                    }elseif (count($sumas_mzas)==1){
                        return (int) $sumas_mzas[0];
                    }
              }
                Log::warning('NO se encontro conteo de mzas en ningún esquema de los buscados para el radio '.$radio->codigo);
                return -3;
            }

       public static function isSegmentado(Radio $radio=null,$esquema=null){
            $esquemas=$radio->Esquemas;
            if ($radio){
                $filtro = " and (frac,radio) = ('".$radio->CodigoFrac."','".$radio->CodigoRad."') ";
              } else {
                $filtro = '';
             }
             if(isset($esquema)){
                 if (in_array($esquema,$esquemas)){
                   Log::debug('Viendo si esta segmentado el radio '.$radio->codigo.' en esquema '.$esquema);
                 }else{
                   Log::warning('Raro! Buscando segmentacion para radio '.$radio->codigo.' en esquema '.$esquema.
                                ' fuera de los esperados ');
                 }
                  try{
                    return (int) DB::select("SELECT count(distinct segmento_id) FROM ".$esquema.".segmentacion s JOIN
                           ".$esquema.".listado l ON s.listado_id=l.id WHERE segmento_id is not null ".
                            ($filtro)
                           ." ;")[0]->count;
                   }catch(QueryException $e){
                            if ($e->getCode() == '42P01'){
                              Log::debug('No existe o hay problemas con segmentacion en esquema: '.$esquema);
                          }else{
                              Log::error('No se encontro segmentacion manzanas para radio '.$radio.$e);
                          }
                   }
             }else{
             $count=0;
             foreach($esquemas as $esquema){
                  try { 
                    $count += (int) DB::select("SELECT count(distinct segmento_id) FROM ".$esquema.".segmentacion s JOIN
                           ".$esquema.".listado l ON s.listado_id=l.id WHERE segmento_id is not null ".
                            ($filtro)
                           ." ;")[0]->count;
               Log:info('Buscando si está segmentado '.$radio->codigo.' en esquema: '.$esquema.' segmentos: '.$count);
                       } catch (QueryException $e)  {
                            if ($e->getCode() == '42P01'){
                              Log::debug('No existe o hay problemas con esquema: '.$esquema);
                          }
                       }
              }
              return $count;
        }
     }

        public static function darPermisos($esquema,$grupo='geoestadistica'){
                try {
                DB::statement('GRANT USAGE ON SCHEMA "'.$esquema.'" TO '.$grupo);
                DB::statement('GRANT SELECT ON ALL TABLES IN SCHEMA  "'.$esquema.'" TO '.$grupo);
                DB::statement('ALTER DEFAULT PRIVILEGES IN SCHEMA  "'.$esquema.'"
                                GRANT SELECT ON TABLES TO '.$grupo);
                    } catch (QueryException $e)  {
                        Log::Error('No se pudieron asignar permisos'.$e);
                        return false;}
                Log::Debug('Se establecieron permisos para geoestadistica');
                return true;
        }

        public static function addUser($usuario,$grupo='geoestadistica'){
                try {
                        DB::unprepared("GRANT ".$grupo." TO ".$usuario.";");

                    } catch (QueryException $e)  {
                        Log::Debug('No se pudo agregar al grupo '.$grupo.' al '.$usuario);
            return false;
        }
                Log::Debug('Se pudo agregar al grupo '.$grupo.' al '.$usuario);
                return true;
        }

        // Carga geometria en topologia y genera manzanas, fracciones y radios.
        // Necesita arc y lab.
        public static function cargarTopologia($esquema)
        {
            try{
                DB::statement(" SELECT indec.cargarTopologia(
                '".$esquema."','arc');");
                DB::beginTransaction();
                DB::statement(" DROP TABLE if exists ".$esquema.".manzanas;");
                DB::statement(" CREATE TABLE ".$esquema.".manzanas AS SELECT * FROM
                ".$esquema.".v_manzanas;");
                DB::statement(" DROP TABLE if exists ".$esquema.".fracciones;");
                DB::statement(" CREATE TABLE ".$esquema.".fracciones AS SELECT * FROM
                ".$esquema.".v_fracciones;");
                DB::statement(" DROP TABLE if exists ".$esquema.".radios;");
                DB::statement(" CREATE TABLE ".$esquema.".radios AS SELECT * FROM
    ".$esquema.".v_radios;");
                DB::commit();

      }catch(QueryException $e){
    DB::Rollback();
                Log::error('No se pudo cargar la topologia...'.$e);
                return false;
            }
            Log::debug('Se generaron fracciones, radios y manzanas ');
            return true;
        }

        // DROPEA esquema de topologia si quedo desfazado por rollback mal
        // hecho
        public static function dropTopologia($esquema)
        {
            try{
                DB::statement(" SELECT topology.dropTopology('".$esquema."');");
            }catch(QueryException $e){
                Log::error('No se pudo borrar la topologia de topology');
            }
            Log::debug('Se borro la topologia ');

            try{
                DB::statement(' DROP SCHEMA IF EXISTS "'.$esquema.'" CASCADE ;');
            }catch(Exception $e){
                Log::error('No se pudo borrar la topologia');
            }
            Log::debug('Se borro esquema con topos ');
        }


    // Crea secuencia para id de segmentos.
    //
    public static function addSequenceSegmentos($esquema,$reset = false)
    {
        try{
            if($reset){
                DB::unprepared('DROP sequence IF EXISTS '.$esquema.'.segmentos_seq CASCADE');
                }
            DB::unprepared('create sequence IF NOT EXISTS '.$esquema.'.segmentos_seq');
        }catch(QueryException $e){
            Log::error('No se pudo recrear la secuencia');
        }
        Log::debug('Se genero una nueva secuencia de segmentos, si no exisitia.');
    }


    // Cambio a bigint id segmentacion.
    public static function cambiarSegmentarBigInt($esquema)
    {
        try{
            DB::statement("ALTER TABLE \"e".$esquema."\".segmentacion ALTER
            COLUMN segmento_id SET DATA TYPE bigint ;");
        }catch(QueryException $e){
            Log::error('NO Se pudo realizar el cambio del tipo segmento_id a bigint');
        }
        Log::debug('Se cambio el tipo segmento_id a bigint');
    }

    // Generar indice en tabla de listados.
    public static function addIndexListado($esquema)
    {
        try{
            DB::statement(
            "create index IF NOT EXISTS listado_piso on ".$esquema.".listado
                (prov, dpto, codloc, frac, radio, mza, lado,
                nrocatastr, sector, edificio, entrada, piso);");
    }catch(QueryException $e){
     Log::debug('No se pudo generar indice de lado en '.$esquema);
    }
     Log::debug('Se creo indice de lado en '.$esquema);
    }

    // Generar indice en tabla de listados.
    public static function createIndex($esquema,$tabla,$campos)
    {
        try{
            DB::statement(
            "create index IF NOT EXISTS ".$esquema."_".$tabla." on ".$esquema.".".$tabla."
               (".$campos.");");
        }catch(QueryException $e){
            Log::debug('No se pudo generar indice de lado en '.$esquema);
        }
     Log::debug('Se creo indice de lado en '.$esquema);
    }

// Generar indice en tabla de listados.
public static function addIndexListadoId($esquema)
{
    try{
        DB::statement(
         "create index IF NOT EXISTS idx_listado_id on ".$esquema.".listado
            (id);");
    }catch(QueryException $e){
     Log::debug('No se pudo generar indice en id para '.$esquema);
    }
     Log::debug('Se creo indice en id para '.$esquema);
}

// Generar indice en tabla de listados x radio.
public static function addIndexListadoRadio($esquema)
{
    try{
        DB::statement(
         "create index IF NOT EXISTS listado_radio on ".$esquema.".listado
            (prov, dpto, codloc, frac, radio);");
    }catch(QueryException $e){
     Log::debug('No se pudo generar indice de radio en '.$esquema);
    }
     Log::debug('Se creo indice de radio en '.$esquema);
}

// Generar indice en id de tabla.
public static function addIndexId($tabla)
{
    try{
        DB::statement(
         "create index IF NOT EXISTS id_".$tabla." on ".$tabla."
            (id);");
    }catch(QueryException $e){
     Log::debug('No se pudo generar indice en id para '.$tabla);
    }
     Log::debug('Se creo indice en id para '.$tabla);
}

// Generar salida pxseg -> tabla.
public static function getPxSeg($esquema)
{
    try{
        return DB::select(
               "select row_number() over() id,* FROM (
           select r3.prov::character(2),null::character(4) codmuni,null::character(4) catmuni,
                       '1046'::character(4) codaglo,null::character(2) nroentidad, lpad(r3.dpto::text,3,'0')::character(3) depto,
                       lpad(r3.codloc::text,3,'0')::character(3) codloc, lpad(r3.frac::text,2,'0'::text)::character(2) frac,
                       lpad(r3.radio::text,2,'0'::text)::character(2) radio,'U'::character(1) tiporad,l.mza mza,
           l.lado lado,'P' tipoform,seg seg,
           string_agg(distinct case when tipoviv in ('VE','CC','BC','CA') then tipoviv
                                                when indec.contar_vivienda(tipoviv) is not null then null
                                                when tipoviv in ('LSV',null,'') then null
                                                else 'incluye' end
                        ,' ') ve_cc_bc_ca, 0 rural,
                       count(indec.contar_vivienda(tipoviv)) vivs
                 from ".$esquema.".r3 JOIN ".$esquema.".segmentacion s On s.segmento_id=r3.segmento_id
                               JOIN ".$esquema.".listado l ON l.id=s.listado_id
                 group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14
                 order by frac,radio,seg,mza,lado) foo ;
                ");

       }catch(QueryException $e){
            Log::error('Error al generar la PxSeg '.$esquema.$e);
            return 'Sin pxseg';
       }
}

    public static function setSRID($esquema,$srid_id)
    {
    try{
        DB::statement("UPDATE ".$esquema.".arc SET wkb_geometry=st_setsrid(wkb_geometry,".$srid_id.");");
        DB::statement("UPDATE ".$esquema.".lab SET wkb_geometry=st_setsrid(wkb_geometry,".$srid_id.");");
    }catch(QueryException $e){
      Log::warning('Problemas al establecer el SRS: '.$srid_id.' en '.$esquema.': '.$e);
      return;
    }
     Log::debug('Se estableció el SRS: '.$srid_id.' en '.$esquema);
    }

    //Generar R3.
    public static function generarR3Esquema($esquema)
    {
//        try{
//    self::generarR3($esquema);
//        }catch(QueryException $e){
            Log::error('TODO Función sin definir, generar R3 del esquema completo para el equema '.$esquema);
            return false;
//        }
//        Log::info('Se dió permiso a '.$rol.' sobre '.$tabla.'.');
//        return true;
    }

    // Generar informe de avances del uso del segmentador.
    public static function getAvances($filtro=null)
    {
        try{
            return DB::select(
           "SELECT l.codigo, l.nombre localidad,
      a.codigo codaglo,a.nombre aglomerado,
            count(*) radios,
            count(r.resultado) probados,
            round((count(r.resultado)/(1.0*count(*)))*100,1) segmentado,
             max(date(updated_at)) fecha
        from localidad l JOIN aglomerados a ON a.id=l.aglomerado_id
             JOIN radio_localidad ON l.id=localidad_id
             JOIN radio r ON r.id=radio_localidad.radio_id
        WHERE r.updated_at is not null
        GROUP BY 1,2,3,4
        ORDER BY count(r.resultado) desc,a.codigo,l.codigo;");
       }catch(QueryException $e){
            Log::error('Error al consultar avances en radios '.$filtro.$e);
            return 'Sin resultados de avances';
       }
    }

}

