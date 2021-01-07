<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Segmentador;
use App\MyDB;
use App\Model\Frccion;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
          $cant_mzas = MyDB::getCantMzas($this);
          if ($cant_mzas!=0){
            $cant_mzas = $cant_mzas;
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
                    MyDB::isSegmentado($this);

//        $cant_mzas = $cant_mzas[0]->cant_mzas;
              if ($result):
                  $this->_isSegmentado = true;
              else:
                  $this->_isSegmentado = false;
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
        $this->save();
    }

    public function getCodigoRadAttribute($value){
        return $radio= substr(trim($this->codigo), 7, 2);
    }

    public function getCodigoFracAttribute($value){
        return $frac= substr(trim($this->codigo), 5, 2);
    }

    public function getEsquemaAttribute($value){
          if ($this->aglomerado() != null){
                if ($this->departamento){
                    if ($this->departamento->provincia->codigo == '02') {
                        return $esquema = 'e'.$this->departamento->provincia->codigo.
                        Str::padLeft(((int)$this->departamento->codigo*7),2,0).$this->localidad->codigo;
                    }else{
                        return $esquema = 'e'.$this->aglomerado->codigo;
                    }
                }else
                { 
                    return $esquema = 'e'.$this->aglomerado()->first()->codigo;
                }
           Log::debug('Radio en esquema: '.$esquema);
        }
        return 'foo';
    }

    public function getSVG()
    {
        // return SVG Radio? Listado? Segmentación?
        if (Schema::hasTable($this->esquema.'.listado_geo')){
            $height=800;
            $width=600;
            $escalar=false;
            $extent=DB::select("SELECT box2d(st_collect(wkb_geometry)) box FROM
            ".$this->esquema.".listado_geo
            WHERE  substr(mzae,1,5)||substr(mzae,9,4)='".$this->codigo."' ");
            $extent=$extent[0]->box;
            list($x0,$y0,$x1,$y1) = sscanf($extent,'BOX(%f %f,%f %f)');

             $Dx=$x1-$x0; $Dy=$y1-$y0;
            if (!$height and $width) $height=round($width*$Dy/$Dx);
            if ($height and !$width) $width=round($height*$Dx/$Dy);
            if (!$height and !$width) {$width=round($perimeter*$Dx/2/($Dx+$Dy)); $height=round($width*$Dy/$Dx);}
            $dx=$Dx/$width; $dy=$Dy/$height; $epsilon=min($dx,$dy)/15; // mínima reolución, acho y alto de lo que representa un pixel
            if ($escalar) {$viewBox="0 0 $width $height"; $stroke=2;}
          else { $viewBox=$this->viewBox($extent,$epsilon,$height,$width); $stroke=2*$epsilon;
          }


        if (Schema::hasTable($this->esquema.'.manzanas')){
            $mzas= "
                UNION
                 ( SELECT st_buffer(wkb_geometry,-5) geom, -1*mza::integer
                         FROM ".$this->esquema.".manzanas
                    WHERE  prov||dpto||frac||radio='".$this->codigo."'
                 ) ";
            }else{$mzas='';}

            //dd($viewBox.'/n'.$this->viewBox($extent,$epsilon,$height,$width).'/n'.$x0." -".$y0." ".$x1." -".$y1);
            $svg=DB::select("
WITH shapes (geom, attribute) AS (
    ( SELECT st_buffer(lg.wkb_geometry,1) wkb_geometry, segmento_id::integer
    FROM ".$this->esquema.".listado_geo lg JOIN ".$this->esquema.".segmentacion
    s ON s.listado_id=id_list
    WHERE  substr(mzae,1,5)||substr(mzae,9,4)='".$this->codigo."'
    ) ".$mzas." 
  ),
  paths (svg) as (
     SELECT concat(
         '<path d= \"',
         ST_AsSVG(st_buffer(geom,5),0), '\" ',
         CASE WHEN attribute = 0 THEN 'stroke=\"gray\" stroke-width=\"2\"
         fill=\"gray\"'
              WHEN attribute < 0 THEN 'stroke=\"white\"
              stroke-width=\"1\" fill=\"#BBBBC5\"'
              WHEN attribute < 5 THEN 'stroke=\"none\"
              stroke-width=\"".$stroke."\" fill=\"#' || attribute*20 || 'AAAA\"'
              WHEN attribute < 10 THEN 'stroke=\"none\"
         stroke-width=\"".$stroke."\" fill=\"#00' || (attribute-5)*20 || '00\"'
              WHEN attribute < 15 THEN 'stroke=\"none\"
         stroke-width=\"".$stroke."\" fill=\"#AA' || (attribute-10)*20 || '00\"'
         ELSE
            'stroke=\"black\" stroke-width=\"".$stroke."\" fill=\"#22' ||
            attribute*10 || '88\"'
         END,
          ' />')
     FROM shapes ORDER BY attribute asc
 )
 SELECT concat(
         '<svg id=\"radio_".$this->codigo."\"xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"".$viewBox.
         "\" height=\"".$height."\" width=\"".$width."\">',
         array_to_string(array_agg(svg),''),
         '</svg>')
 FROM paths;
");
            return $svg[0]->concat;
        }else{ return "No geodata"; }

    }

    private function viewBox($extent,$epsilon,$height,$width){
        list ( $x0, $y0, $x1, $y1 ) = sscanf ( $extent, 'BOX(%f %f,%f %f)' );
        $Dx = $x1 - $x0;
        $Dy = $y1 - $y0;
            $m_izq=.1*$Dx; $m_der=.1*$Dx; $m_arr=.1*$Dy; $m_aba=.1*$Dy;
        $viewBox = ($x0 - $m_izq) . " " . (- $y1 - $m_arr) . " " . ($Dx + $m_izq + $m_der) . " " . ($Dy + $m_arr + $m_aba);
        if (! $height and ! $width)
            $height = 600;
        if (! $height)
            $height = $width * $Dy / $Dx;
        if (! $width)
           $width = $height * $Dx / $Dy;
        $epsilon = min ( $Dx / $width, $Dy / $height );
        return $viewBox;
    }
}
