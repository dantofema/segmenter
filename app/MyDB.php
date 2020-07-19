<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MyDB extends Model
{
    //a
	public static function createSchema($esquema)
	{
		DB::statement('CREATE SCHEMA IF NOT EXISTS e'.$esquema);
	}

    public static function infoDBF($file_name,$esquema)
    {
         $tabla = strtolower( substr($file_name,strrpos($file_name,'/')+1,-4) );
    	return json_encode(DB::select('
                        SELECT prov,dpto,nom_loc,codaglo, codloc, nom_loc, codent,nom_ent,count(*) registros, 
                        count(distinct frac||radio) as radios,
                        count(indec.contar_vivienda(cod_tipo_v)) as vivendas 
                        --,count(*) vivs
                        ,count(distinct prov||dpto||codloc||frac||radio||mza) as mzas
                        --,array_agg(distinct prov||dpto||codloc||frac||radio||mza||lado),count(distinct lado) as lados 
FROM 
                        '.$tabla.'
                        GROUP BY 1,2,3,4,5,6,7,8;')); 
          
    }
	public static function moverDBF($file_name,$esquema)
	{
         $tabla = strtolower( substr($file_name,strrpos($file_name,'/')+1,-4) );
         $esquema = 'e'.$esquema;
             DB::beginTransaction();
             DB::unprepared('ALTER TABLE '.$tabla.' SET SCHEMA '.$esquema);
             DB::unprepared('DROP TABLE IF EXISTS '.$esquema.'.listado CASCADE');
             DB::unprepared('ALTER TABLE '.$esquema.'.'.$tabla.' RENAME TO listado');
             DB::unprepared('ALTER TABLE '.$esquema.'.listado ADD COLUMN id serial');
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
             if (! Schema::hasColumn($esquema.'.listado' , 'piso')){
                     DB::unprepared('ALTER TABLE '.$esquema.'.listado RENAME pisoredef TO piso');
             }
             if (Schema::hasTable($esquema.'.arc') and Schema::hasTable($esquema.'.listado')){
                if (! Schema::hasColumn($esquema.'.arc' , 'nomencla10')){
                            DB::statement('ALTER TABLE '.$esquema.'.arc ADD COLUMN IF NOT EXISTS nomencla10 text;');
                }
                if (! Schema::hasColumn($esquema.'.arc' , 'segi')){
    	                    DB::statement('ALTER TABLE '.$esquema.'.arc ADD COLUMN IF NOT EXISTS segi integer;');
                }
                if (! Schema::hasColumn($esquema.'.arc' , 'segd')){
                            DB::statement('ALTER TABLE '.$esquema.'.arc ADD COLUMN IF NOT EXISTS segd integer;');
                }
                 DB::unprepared("Select indec.cargar_conteos('".$esquema."')");
                 DB::unprepared("Select indec.generar_adyacencias('".$esquema."')");
                 DB::unprepared("Select indec.descripcion_segmentos('".$esquema."')");
             }
             DB::commit();
	}

    
	public static function agregarsegisegd($esquema)
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

	public static function segmentar_equilibrado($esquema,$deseado = 10)
	{
    	if ( DB::statement("SELECT indec.segmentar_equilibrado('e".$esquema."',".$deseado.");") ){
        //    MyDB::georeferenciar_segmentacion($esquema);
        // llamar generar r3 como tabla resultado de function indec.r3(agl)
        // unir con aglo.descripcion_segmentos usando una estructura para ambas
//        if( DB::statement("SELECT indec.funcion_que_genera_la_descripcion('e".$esquema."');") )            
            return true;
        }else{ return false; }

     // SQL retrun: Select segmento_id,count(*) FROM e0777.segmentacion GROUP BY segmento_id;
	}
	
    public static function segmentar_equilibrado_ver($esquema)
	{
        $esquema = 'e'.$esquema;
    	return DB::select('
                        SELECT segmento_id,count(*) vivs,count(distinct mza) as mzas,array_agg(distinct prov||dpto||codloc||frac||radio||mza||lado),count(distinct lado) as lados FROM 
                        '.$esquema.'.
                        segmentacion s JOIN 
                        '.$esquema.'.
                        listado l ON s.listado_id=l.id 
                        GROUP BY segmento_id 
                        ORDER BY count(*) asc, array_agg(mza), segmento_id ;');
     // SQL retrun: 
    }

    public static function segmentar_equilibrado_ver_resumen($esquema)
	{
        $esquema = 'e'.$esquema;
    	return DB::select('SELECT vivs,count(*) cant_segmentos FROM (
                        SELECT segmento_id,count(*) vivs,count(distinct mza) as mzas,array_agg(distinct prov||dpto||codloc||frac||radio||mza||lado),count(distinct lado) as lados FROM 
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
                        SELECT substr(lados.mza,1,12) radio, seg,count(*) lados,count(distinct lados.mza) as mzas_count,array_agg(distinct substr(lados.mza,13,3)) mzas,sum(conteo) as vivs FROM 
			(SELECT segi seg,mzai mza,ladoi lado FROM '.$esquema.'.arc WHERE segi is not null 
			UNION SELECT segd,mzad,ladod FROM '.$esquema.'.arc WHERE segd is not null) lados
                       JOIN  '.$esquema.'.conteos c ON (c.prov,c.dpto,c.codloc,c.frac,c.radio,c.mza,c.lado)=(
                                                        substr(lados.mza,1,2)::integer,substr(lados.mza,3,3)::integer,substr(lados.mza,6,3)::integer,
                                                        substr(lados.mza,9,2)::integer,substr(lados.mza,11,2)::integer,substr(lados.mza,13,3)::integer,lados.lado::integer)

                        WHERE substr(lados.mza,1,12)!=\'\'
                        GROUP BY  substr(lados.mza,1,12), seg');
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
        //return true;
//   --ALTER TABLE ' ".$esquema." '.arc alter column wkb_geometry type geometry('LineString',22182) USING (st_setsrid(wkb_geometry,22182));
        $esquema = 'e'.$esquema;
    	DB::statement("DROP TABLE IF EXISTS ".$esquema.".listado_geo;");
        $resultado= DB::select("
        WITH listado as (
    SELECT id, l.prov, nom_provin, ups, nro_area, l.dpto, nom_dpto, l.codaglo, l.codloc, nom_loc, codent, nom_ent, l.frac, l.radio, l.mza, l.lado, 
    nro_inicia, nro_final, orden_reco, nro_listad, ccalle, ncalle, nro_catast, 
    CASE WHEN nrocatastr='' or nrocatastr='S/N' THEN null ELSE nrocatastr END nrocatastr, 
    piso, pisoredef, casa, dpto_habit, sector, edificio, entrada, tipoviv, cod_subt_v, cod_subt_2, descripcio, descripci2 , 
    row_number() over(partition by l.frac, l.radio, l.mza, l.lado order by l.lado, orden_reco asc) nro_en_lado, conteo, accion
    FROM
    ".$esquema.".listado l
    LEFT JOIN ".$esquema.".conteos c ON 
    (c.prov,c.dpto,c.codloc,c.frac,c.radio,c.mza,c.lado)=(l.prov::integer,l.dpto::integer,l.codloc::integer,l.frac::integer,l.radio::integer,l.mza::integer,l.lado::integer)
), 
arcos as (
    SELECT min(ogc_fid) ogc_fid, st_LineMerge(st_union(wkb_geometry)) wkb_geometry,nomencla,codigo20,array_agg(distinct codigo10) codigo10, tipo, nombre,lado,min(desde) desde,
    max(hasta) hasta,mza,codloc20 
    FROM 
    (SELECT ogc_fid,st_reverse(wkb_geometry) wkb_geometry,nomencla,codigo20,codigo10,tipo, nombre, ancho, anchomed, ladoi lado,desdei desde,
     hastai hasta,mzai mza, codloc20, nomencla10,nomenclai nomenclax, codinomb, segi seg 
    FROM ".$esquema.".arc
    UNION
     SELECT ogc_fid,wkb_geometry,nomencla,codigo20,codigo10,tipo, nombre, ancho, anchomed, ladod lado,desded desde,
     hastad hasta,mzad mza, codloc20, nomencla10,nomenclad nomenclax, codinomb, segd seg 
    FROM ".$esquema.".arc) arcos_juntados
    GROUP BY nomencla,codigo20,tipo, nombre,lado,mza,codloc20
)
  SELECT nro_en_lado, conteo,1.0*nro_en_lado/(conteo+1) interpolacion, l.orden_reco,
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
            tipo, nombre, e.lado ladoe, desde, hasta,e.mza mzae, codloc20,
            frac, radio, l.mza, l.lado, ccalle, ncalle, nrocatastr, pisoredef piso,casa,dpto_habit,sector,edificio,entrada,tipoviv, 
            descripcio,descripci2 , accion
INTO ".$esquema.".listado_geo
FROM arcos e JOIN listado l ON l.ccalle::integer=e.codigo20 
and
     (l.lado::integer=e.lado and 
         e.mza like 
         '%'||btrim(to_char(l.frac::integer, '09'::text))::character varying(3)||btrim(to_char(l.radio::integer, '09'::text))::character varying(3)||btrim(to_char(l.mza::integer, '099'::text))::character varying(3) 
       );");
       DB::statement("GRANT SELECT ON TABLE  ".$esquema.".listado_geo TO sig");
        return $resultado;
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
    s.segmento_id as segmento_id, nro_inicia, nro_final, orden_reco, nro_listad, ccalle, ncalle, nro_catast, 
    CASE WHEN nrocatastr='' or nrocatastr='S/N' THEN null ELSE nrocatastr END nrocatastr, 
    piso, pisoredef, casa, dpto_habit, sector, edificio, entrada, tipoviv, cod_subt_v, cod_subt_2, descripcio, descripci2 , 
    row_number() over(partition by l.frac, l.radio, l.mza, l.lado order by l.lado, orden_reco asc) nro_en_lado, conteo, accion
    FROM
    ".$esquema.".listado l
    JOIN ".$esquema.".segmentacion s ON s.listado_id=l.id
    LEFT JOIN ".$esquema.".conteos c ON 
    (c.prov,c.dpto,c.codloc,c.frac,c.radio,c.mza,c.lado)=(l.prov::integer,l.dpto::integer,l.codloc::integer,l.frac::integer,l.radio::integer,l.mza::integer,l.lado::integer)
), 
arcos as (
    SELECT min(ogc_fid) ogc_fid, st_LineMerge(st_union(wkb_geometry)) wkb_geometry,nomencla,codigo20,array_agg(distinct codigo10) codigo10, tipo, nombre,lado,min(desde) desde,
    max(hasta) hasta,mza,codloc20 
    FROM 
    (SELECT ogc_fid,st_reverse(wkb_geometry) wkb_geometry,nomencla,codigo20,codigo10,tipo, nombre, ancho, anchomed, ladoi lado,desdei desde,
     hastai hasta,mzai mza, codloc20, nomencla10,nomenclai nomenclax, codinomb, segi seg 
    FROM ".$esquema.".arc
UNION
     SELECT ogc_fid,wkb_geometry,nomencla,codigo20,codigo10,tipo, nombre, ancho, anchomed, ladod lado,desded desde,
     hastad hasta,mzad mza, codloc20, nomencla10,nomenclad nomenclax, codinomb, segd seg 
    FROM ".$esquema.".arc) arcos_juntados
    GROUP BY nomencla,codigo20,tipo, nombre,lado,mza,codloc20
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
            tipo, nombre, e.lado ladoe, desde, hasta,e.mza mzae, codloc20,
            frac, radio, l.mza, l.lado, ccalle, ncalle, nrocatastr, pisoredef piso,casa,dpto_habit,sector,edificio,entrada,tipoviv, 
            descripcio,descripci2 , accion
INTO ".$esquema.".listado_segmentado_geo
FROM arcos e JOIN listado l ON l.ccalle::integer=e.codigo20 
and
     (l.lado::integer=e.lado and 
         e.mza like 
         '%'||btrim(to_char(l.frac::integer, '09'::text))::character varying(3)||btrim(to_char(l.radio::integer, '09'::text))::character varying(3)||btrim(to_char(l.mza::integer, '099'::text))::character varying(3) 
       );");
       DB::statement("GRANT SELECT ON TABLE  ".$esquema.".listado_segmentado_geo TO sig");
        return $resultado;
    }

    public static function getNodos($esquema,$radio = '%01103')
    {
        return DB::select('SELECT distinct *, substr(mza_i,13,3)||\':\'||lado_i as label,c.conteo FROM (
                                            SELECT mza_i,lado_i from e'.$esquema.'.lados_adyacentes WHERE mza_i like :radio UNION 
                                            SELECT mza_j,lado_j from e'.$esquema.'.lados_adyacentes WHERE mza_j like :radio) foo
LEFT JOIN 
    e'.$esquema.'.conteos c
    ON (c.prov,c.dpto,c.codloc,c.frac,c.radio,c.mza,c.lado)=
        (substr(mza_i,1,2)::integer,
         substr(mza_i,3,3)::integer,
         substr(mza_i,6,3)::integer,
         substr(mza_i,9,2)::integer,
         substr(mza_i,11,2)::integer,
         substr(mza_i,13,3)::integer,
         lado_i)

                           ',['radio'=>$radio.'%']);
    }

    public static function getAdyacencias($esquema,$radio = '%01103')
    {
                return DB::select('SELECT * from e'.$esquema.'.lados_adyacentes
         WHERE mza_i like :radio and mza_j like :radio;',['radio'=>$radio.'%']);
    }
    
    public static function getSegmentos($esquema,$radio = '%01103')
    {
                return DB::select('SELECT array_agg(mza||\'-\'||lado) segmento 
FROM
(SELECT 
mzai mza,ladoi lado, segi seg
FROM e'.$esquema.'.arc
UNION
 SELECT
mzad mza,ladod lado, segd seg
FROM e'.$esquema.'.arc
) segs
WHERE mza like :radio
GROUP BY seg
;',['radio'=>$radio.'%']);
    }

    public static function getCantMzas($radio,$esquema){
        $prov=substr($radio,0,2);
        $dpto=substr($radio,2,3);
        $frac=substr($radio,5,2);
        $radio=substr($radio,7,2);
        return DB::select("
SELECT count( distinct mza)  cant_mzas 
FROM ".$esquema.".conteos WHERE prov=".$prov." and dpto = ".$dpto." and frac=".$frac." and radio=".$radio." ;");
    }

    public static function isSegmentado($radio,$esquema){
        return DB::select("SELECT true FROM ".$esquema.".arc WHERE (substr(mzad,1,5)||substr(mzad,9,4)='".$radio."' and segd is not null and segd>0) 
                            or (substr(mzai,1,5)||substr(mzai,9,4)='".$radio."' and segi is not null and segi>0)
        limit 1;");
    }
}
