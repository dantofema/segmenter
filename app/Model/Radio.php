<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Segmentador;

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
        return $this->belongsToMany('App\Model\Localidad', 'radio_localidad');
        // return $this->belongsTo('App\Model\RadioLocalidad','radio_localidad','radio_id','localidad_id');
     }

    /**
     * Segmentar radio a lados completos
     * 
     */
    public function segmentar($aglo,$deseadas,$max,$min,$indivisible)
    {
        //
//        dd($this);
//        $aglo= $this->localidades()->first()->aglomerado()->first()->codigo;    
//       dd( $prov= substr(trim($this->departamento()->first()->provincia->first()->codigo), -2, 2));
/*
        $dpto= substr(trim($this->departamento()->first()->codigo), -3, 3);
        $frac= substr(trim($this->fraccion()->first()->codigo), -2, 2);
        $radio= substr(trim($this->codigo), -2, 2);
*/
        $prov=substr(trim($this->codigo), 0, 2);
        $dpto= substr(trim($this->codigo), 2, 3);
        $frac= substr(trim($this->codigo), 5, 2);
        $radio= substr(trim($this->codigo), 7, 2);

        $segmenta = new Segmentador();
        $segmenta->segmentar_a_lado_completo($aglo,$prov,$dpto,$frac,$radio,$deseadas,$max,$min,$indivisible);
        dd($segmenta);
        return $segmenta->ver_segmentacion();
    }

}
