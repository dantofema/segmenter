<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
             DB::unprepared('DROP TABLE IF EXISTS '.$esquema.'.listado');
             DB::unprepared('ALTER TABLE '.$esquema.'.'.$tabla.' RENAME TO listado');
             DB::unprepared('ALTER TABLE '.$esquema.'.listado ADD COLUMN id serial');
             DB::unprepared('ALTER TABLE '.$esquema.'.listado RENAME cod_tipo_v TO tipoviv');
             DB::unprepared("Select indec.cargar_conteos('".$esquema."')");
             DB::unprepared("Select indec.generar_adyacencias('".$esquema."')");
             DB::commit();
	}

    


}
