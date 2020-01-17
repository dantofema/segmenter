<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

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

        return false;
    }

    public function getListadoAttribute($value)
    {
        /// do your magic

        return false;
    }
}
