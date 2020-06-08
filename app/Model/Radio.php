<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Segmentador;
use App\MyDB;

class Radio extends Model
{
    //
    protected $table='radio';

    protected $fillable = [
        'id','codigo','nombre'
    ];

    private $_isSegmentado;
    private $_resultado;

     /**
      * Fix datos..
      *
      */
     public function getCodigoAttribute($value)
     {
        return trim($value);
     }


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
      * Relación con Aglomerado, un Radio puede pertenecer a varios aglomerado? Espero que solo este en 1. 
      *
      */

     public function aglomerado()
     {  
        //TODO
//        return $this->belongsToMany('App\Model\Localidad', 'radio_localidad');
        if ($localidad=$this->localidades()->with('aglomerado')->first())
            return $localidad->aglomerado()->get();
        else
            return null; //new Aglomerado();
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
//        dd($segmenta);
        return $this->_resultado = $segmenta->ver_segmentacion();
    }

     /**
      * Fix Cantidad de manzanas en cartografia..
      *
      */
     public function getCantMzasAttribute($value)
     {
        if ($this->aglomerado() != null){
          $cant_mzas = MyDB::getCantMzas($this->codigo,'e'.$this->aglomerado()->first()->codigo);
          $cant_mzas = $cant_mzas[0]->cant_mzas;
          return $cant_mzas;
        }
        else{
          return -1;
        }
     }

     /**
      * Fix Cantidad de manzanas en cartografia..
      *
      */
     public function getisSegmentadoAttribute($value)
     {
        if (! isset($this->_isSegmentado)){
          if ($this->aglomerado() != null){
            $result = MyDB::isSegmentado($this->codigo,'e'.$this->aglomerado()->first()->codigo);
//        $cant_mzas = $cant_mzas[0]->cant_mzas;
              if ($result):
                  return $this->_isSegmentado = true;
              else:
                  return $this->_isSegmentado = false;
              endif;
           }
          else{
             return false;
          }
        }else{
            return $this->_isSegmentado;
        }
     }

    public function getResultadoAttribute($value)
    {
        return $this->_resultado;
    }


}
