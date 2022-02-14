<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    //
    protected $table='provincia';

    protected $fillable = [
        'id','codigo','nombre'
    ];

    // Sin fecha de creación o modificación
    //
    public $timestamps = false;

     /**
     * Get the departamentos de la provincia.
     */
    public function departamentos()
    {
        return $this->hasMany('App\Model\Departamento');
    }

    /**
     * Relación con Fracciones, a través de departamentos puede tener muchas fracciones.
     */
    public function fracciones()
    {
        return $this->hasManyThrough('App\Model\Fraccion','App\Model\Departamento');
    }

    /**
     * Relación con Fracciones, a través de departamentos puede tener muchas fracciones.
     */
//    public function radios()
//    {
//        return $this->hasManyThrough('App\Model\Radio','App\Model\Departamento');
//    }
}
