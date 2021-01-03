<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Segmentador;
use App\MyDB;
use App\Model\Frccion;
use Illuminate\Support\Str;

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
/*
     public function departamento()
     {
         return $this->hasOneThrough(
                Departamento::class,
                Fraccion::class,
                'id','id','id','departamento_id');
        }
*/

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
        if ($localidad=$this->localidades()->first())
            return $localidad->aglomerado()->get();
        else
            return null; //new Aglomerado();
     }

    /**
     * Segmentar radio a lados completos
     * 
     */
    public function segmentar($esquema,$deseadas,$max,$min,$indivisible)
    {
        $prov= substr(trim($this->codigo), 0, 2);
        $dpto= substr(trim($this->codigo), 2, 3);
        $frac= substr(trim($this->codigo), 5, 2);
        $radio= substr(trim($this->codigo), 7, 2);

        $segmenta = new Segmentador();
        $segmenta->segmentar_a_lado_completo($esquema,$prov,$dpto,$frac,$radio,$deseadas,$max,$min,$indivisible);

        $segmenta->vista_segmentos_lados_completos($esquema);
        $segmenta->lados_completos_a_tabla_segmentacion_ffrr($esquema,$frac,$radio);
        return $this->_resultado = $segmenta->ver_segmentacion();
    }

    /**
     * Segmentar radio con metodo magico.
     * 
     */
    public function segmentarLucky($esquema,$deseadas,$max,$min,$indivisible)
    {
        $prov= substr(trim($this->codigo), 0, 2);
        $dpto= substr(trim($this->codigo), 2, 3);
        $frac= substr(trim($this->codigo), 5, 2);
        $radio= substr(trim($this->codigo), 7, 2);

        $segmenta = new Segmentador();
        $segmenta->segmentar_a_lado_completo($esquema,$prov,$dpto,$frac,$radio,$deseadas,$max,$min,$indivisible);

        $segmenta->vista_segmentos_lados_completos($esquema);
        $segmenta->lados_completos_a_tabla_segmentacion_ffrr($esquema,$frac,$radio);
        $segmenta->segmentar_excedidos_ffrr($esquema,$frac,$radio,$max,$deseadas);

//        dd($segmenta);
        return $this->_resultado = $segmenta->ver_segmentacion();
    }

     /**
      * Fix Cantidad de manzanas en cartografia..
      *
      */
     public function getCantMzasAttribute($value)
     {
          $cant_mzas = MyDB::getCantMzas($this->codigo,$this->esquema);
          if ($cant_mzas!=0){
            $cant_mzas = $cant_mzas[0]->cant_mzas;
            }else{$cant_mzas=-1;}

          return $cant_mzas;
     }

     /**
      * Fix existe una segmentacion..
      *
      */
     public function getisSegmentadoAttribute($value)
     {
        if (! isset($this->_isSegmentado)){
          if ($this->aglomerado() != null){
                    $result =
                    MyDB::isSegmentado($this->codigo,$this->esquema);

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

    public function setResultadoAttribute($value)
    {
        return $this->_resultado=$value;
    }

    public function getCodigoRad($value){
        return $radio= substr(trim($this->codigo), 7, 2);
    }

    public function getCodigoFrac($value){
        return $frac= substr(trim($this->codigo), 5, 2);
    }

    public function getEsquema($value){
          if ($this->aglomerado() != null){
                if ($this->departamento){
                    if ($this->departamento->provincia->codigo == '02') {
                    
                        $esquema = 'e'.$this->departamento->provincia->codigo.
                        Str::padLeft(((int)$this->departamento->codigo*7),2,0).$this->localidad->codigo;
                    }else{
                        $esquema = 'e'.$this->aglomerado()->first()->codigo;
                    }
                }else
                {dd($this->departamento);}
           Log::debug('Radio en esquema: '.$esquema);
           return $esquema;
        }
    }
}
