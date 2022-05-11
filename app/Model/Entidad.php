<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Entidad extends Model
{
    //
    protected $table='entidad';

    protected $fillable = [
        'id','codigo','nombre'
    ];

    // Sin fecha de creación o modificación
    //
    public $timestamps = false;

     /**
     * Get the localidad de la entidad.
     */
    public function localidad()
    {
        return $this->hasOne('App\Model\Localidad');
    }
}
