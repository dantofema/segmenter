<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class Aglomerado extends Model
{
    //
    protected $table='aglomerados';

    protected $fillable = [
        'id','codigo','nombre'
    ];
    public $carto;
    public $listado;

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
}
