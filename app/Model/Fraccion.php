<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Fraccion extends Model
{
    //
    protected $table='fraccion';

    protected $fillable = [
        'id','codigo','nombre'
    ];

     /**
      * Relación con Radios, una Fraccion tiene uno o varios radios.
      *
      */

     public function radios()
     {
         return $this->hasMany('App\Model\Radio');
     }

     /**
      * Relación con Departamento, una Fraccion pertenece a Un departamento. 
      *
      */

     public function departamento()
     {
         return $this->belongsTo('App\Model\Departamento');
     }

}
