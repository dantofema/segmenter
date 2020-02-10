<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Localidad extends Model
{
    //
    protected $table = 'localidad';

    /**
     * Relación con Departamento, una localidad puede estar en muchos departamentos (caso CABA).
     */
    public function departamentos()
    {
        return $this->belongsToMany('App\Departamento','localidad_departamento');
    }

    /**
     * Relación con Aglomerados, una localidad pertenece a un aglomerado.
     *
     */

    public function aglomerado()
    {
        return $this->belongsTo('App\Model\Aglomerado');
    }
}
