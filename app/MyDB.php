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
                 if (Schema::hasColumn($esquema.'.listado' , 'cod_tipo_v')){
                     DB::unprepared('ALTER TABLE '.$esquema.'.listado RENAME cod_tipo_v TO tipoviv');
                 }elseif (Schema::hasColumn($esquema.'.listado' , 'cod_viv')){
                            DB::unprepared('ALTER TABLE '.$esquema.'.listado RENAME cod_viv TO tipoviv');
                     }else{
                         DB::statement('ALTER TABLE '.$esquema.'.listado ADD COLUMN tipoviv text;');
                     }
             }
             DB::unprepared("Select indec.cargar_conteos('".$esquema."')");
             DB::unprepared("Select indec.generar_adyacencias('".$esquema."')");
             DB::commit();
	}

    
	public static function agregarsegisegd($esquema)
	{
	 DB::statement('ALTER TABLE e'.$esquema.'.arc ADD COLUMN segi integer;');
	 DB::statement('ALTER TABLE e'.$esquema.'.arc ADD COLUMN segd integer;');
	}


}
