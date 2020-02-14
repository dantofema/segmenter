<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Radio extends Model
{
    //
    protected $table='radio';

    protected $fillable = [
        'id','codigo','nombre'
    ];

     /**
      * Relación con Departamento, una Fraccion pertenece a Un departamento. 
      *
      */

     public function fraccion()
     {
         return $this->belongsTo('App\Model\Fraccion');
     }



}
