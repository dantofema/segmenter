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
        return $this->belongsToMany('App\Model\Departamento','localidad_departamento');
    }

    /**
     * Relación con Aglomerados, una localidad pertenece a un aglomerado.
     */

    public function aglomerado()
    {
        return $this->belongsTo('App\Model\Aglomerado');
    }

    /**
     * Relación con Radios, una localidad tiene muchos radios (que pueden estas en mas de una localidad?).
     */

    public function radios()
    {
        return $this->belongsToMany('App\Model\Radio', 'radio_localidad');
    }

    //
    public function getCodigoLocAttribute($value){
        return $codloc= substr(trim($this->codigo), 5, 3);
    }

    


}
