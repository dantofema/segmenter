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
      * Relación con Fraccion , un Radio pertenece a Una fracción. 
      *
      */

     public function fraccion()
     {
         return $this->belongsTo('App\Model\Fraccion');
     }

     /**
      * Relación con Departamento, una Fraccion pertenece a Un departamento. 
      *
      */

     public function departamento()
     {
         return $this->fraccion->departamento();
     }

     /**
      * Relación con Localidad, un Radio puede pertenecer a varias localidades. 
      *
      */

     public function localidades()
     {
         return $this->belongsTo('App\Model\RadioLocalidad','radio_localidad');
     }

    /**
     * Segmentar radio a lados completos
     * 
     */
    public function segmentar($deseadas,$max,$min,$indivisible)
    {
        //
        $aglo=$this->aglomerado->codigo();
        $segmenta = new Segmentador();
        $segmenta->segmentar_a_lado_completo($aglo,$dpto,$frac,$radio,$deseadas,$max,$min,$indivisible);
        return $segmenta->ver_segmentacion($radio);
    }

}
