<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Aglomerado extends Model
{
    //
    protected $table='aglomerados';

    protected $fillable = [
        'id','codigo','nombre'
    ];
    public $carto;
    public $listado;

     /**
      * Relación con Localidades, un Aglomerados tiene una o varias localidad.
      *
      */

     public function localidades()
     {
         return $this->hasMany('App\Model\Localidad');
     }


    public function getCartoAttribute($value)
    {
        //return true;
        /// do your magic
        /*
        $schemas=Config::get('database.connections.pgsql.schema');
        array_push($schemas,'e'.$this->codigo);
//        dd($schemas);
        config(['database.connections.pgsql.schema'=>$schemas]); 
        //dd(Config::get('database.connections.pgsql.schema'));
*/
//select * from information_schema.tables where table_schema = 'e0777' and table_name = 'arc' and table_type = 'BASE TABLE'
        if (Schema::hasTable('e'.$this->codigo.'.arc')) {
            //
            return true;
        }else{
            return false;
        }
    }

    public function getListadoAttribute($value)
    {
        /// do your magic
        if (Schema::hasTable('e'.$this->codigo.'.listado')) {
            //
            return true;
        }else{
            return false;
        }
        return false;
    }

    public function setCartoAtribute()
    {   return true;
        if (Schema::hasTable('e'.$this->codigo.'.arc')) {
            return $this->attributes['carto'] = true;
        }else{
            return $this->attributes['carto'] = false;
        }

    }

    public function getRadiosAttribute()
    {
        $radios= null;
        if ($this->Listado==1){
            $radios = DB::table('e'.$this->codigo.'.listado')
                                ->select(DB::raw("prov||dpto||codloc||frac||radio as link,'('||dpto||') '||nom_dpto||': '||frac||' '||radio as nombre,
             count(distinct mza) as cant_mzas,
             count(*) as vivs,
             count(CASE WHEN tipoviv='A' THEN 1 else null END) as vivs_a,
             count(CASE WHEN (tipoviv='B1' or tipoviv='B2') THEN 1 else null END) as vivs_b,
             count(CASE WHEN tipoviv='CA/CP' THEN 1 else null END) as vivs_c,
             count(CASE WHEN tipoviv='CO' THEN 1 else null END) as vivs_co,
             count(CASE WHEN (tipoviv='D'  or tipoviv='J'  or tipoviv='VE' )THEN 1 else null END) as vivs_djve,
             count(CASE WHEN tipoviv='' THEN 1 else null END) as vivs_unclas
    "))
                                ->groupBy('prov','dpto','codloc','nom_dpto','frac','radio')
                                ->get();
        }
        return $radios;

    }


}
