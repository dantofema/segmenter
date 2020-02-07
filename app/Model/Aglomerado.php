<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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
        /// do your magic
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

        return false;
    }

    public function setCartoAtribute()
    {
        if (Schema::hasTable('e'.$this->codigo.'.arc')) {
            $this->attributes['carto'] = true;
        }else{
            $this->attributes['carto'] = false;
        }

    }
}
