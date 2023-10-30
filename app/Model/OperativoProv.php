<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OperativoProv extends Model
{
    //
    protected $table='operativo_provincia';


    /**
     * Relación con Radios, una TipoRadio tiene varios radios.
     *
     */

    public function operativos()
    {
        return $this->hasMany('App\Model\Provincia','provincia_id');
    }

}
