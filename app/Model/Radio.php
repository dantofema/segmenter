<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Segmentador;
use App\MyDB;
use App\Model\Frccion;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Auth;

class Radio extends Model
{
    //
    protected $table='radio';
    protected $primaryKey = 'id';
    protected $fillable = [
            'codigo','nombre'
    ];

    private $_isSegmentado;
    private $_resultado;
    private $_esquema;
    private $_esquemas;

    public static function getradioData(){
      $value=DB::table($table)->orderBy('id', 'asc')->get();
      return $value;
    }

    /**
      * Fix datos..
      *
      */
     public function getCodigoAttribute($value)
     {
        return trim($value);
     }


     /**
      * Relación con TipoRadio , un Radio tiene un tipo de radio.
      *
      */

     public function tipo()
     {
         return $this->belongsTo('App\Model\TipoRadio','tipo_de_radio_id','id');
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
      * Relación con Entidad, un Radio puede estar en varias entidades de varias localidades.
      *
      */

     public function entidades()
     {
        return $this->belongsToMany('App\Model\Entidad', 'radio_entidad');
     }

     /**
      * Relación con Aglomerado, 
      * un Radio puede pertenecer a varios aglomerados!
      * (? Esperaba que solo este en 1. :( )
      *
      */

     public function aglomerados()
     {
	    $aglos=[];
            foreach ($this->localidades as $localidad){
                $aglos[] = $localidad->aglomerado;
            }
            return $aglos;
     }

    /**
     * Segmentar radio a lados completos
     *
     */
    public function segmentar($esquema,$deseadas,$max,$min,$indivisible)
    {
      if (Auth::check()) {
        $AppUser= Auth::user();
        $prov= substr(trim($this->codigo), 0, 2);
        $dpto= substr(trim($this->codigo), 2, 3);
        $frac= substr(trim($this->codigo), 5, 2);
        $radio= substr(trim($this->codigo), 7, 2);

        $segmenta = new Segmentador();
        $segmenta->segmentar_a_lado_completo($esquema,$prov,$dpto,$frac,$radio,$deseadas,$max,$min,$indivisible);

        $segmenta->vista_segmentos_lados_completos($esquema);
        $segmenta->lados_completos_a_tabla_segmentacion_ffrr($esquema,$frac,$radio);
        $this->resultado = $segmenta->ver_segmentacion().'
	x '.$AppUser->name.' ('.$AppUser->email.') en '.date("Y-m-d H:i:s").
	'
	----------------------- LOG ----------------------------
	'.$this->resultado;
        $this->save();
	return $this->resultado;
      }else{
        $mensaje='No tiene permiso para segmentar o no esta logueado';
	flash($mensaje)->error()->important();
        return $mensaje;
      }
    }

    /**
     * Segmentar radio con metodo magico.
     *
     */
    public function segmentarLucky($esquema,$deseadas,$max,$min,$indivisible,$force=false)
    {
      if (Auth::check()) {
        $AppUser= Auth::user();
        $prov= substr(trim($this->codigo), 0, 2);
        $dpto= substr(trim($this->codigo), 2, 3);
        $frac= substr(trim($this->codigo), 5, 2);
        $radio= substr(trim($this->codigo), 7, 2);

        $segmenta = new Segmentador();
        $segmenta->segmentar_a_lado_completo($esquema,$prov,$dpto,$frac,$radio,$deseadas,$max,$min,$indivisible);

        $segmenta->vista_segmentos_lados_completos($esquema);
        $segmenta->lados_completos_a_tabla_segmentacion_ffrr($esquema,$frac,$radio);

        // Calculo de umbral ...
      	// Según nuevo abordaje para forzar partir excedidos -h ...
      	// Valor por encima del 5% del máximo.
        // Prpongo sin holgura (=1) y revisar deseado según número de viviendas
        // ya que el umbral sólo selecciona el segmento a partir y luego
        // se usa el desaeado. 2022-01-19 M.

        $holgura = 1.05;
        $umbral = $holgura*$max;

        $segmenta->segmentar_excedidos_ffrr($esquema,$frac,$radio,$umbral,$deseadas);
        $this->resultado = $segmenta->ver_segmentacion().'
        x '.$AppUser->name.' ('.$AppUser->email.') en '.date("Y-m-d H:i:s").
	'
	----------------------- LOG ----------------------------
	'.$this->resultado;
        $this->save();
        return $this->resultado;
      }else{
        $mensaje='No tiene permiso para segmentar o no esta logueado';
	flash($mensaje)->error()->important();
        return $mensaje;
      }
    }

     /**
      * Fix Cantidad de manzanas en cartografia..
      *
      */
     public function getCantMzasAttribute($value)
     {
          $cant_mzas = MyDB::getCantMzas($this);
          return $cant_mzas;
     }

     /**
      * Fix existe una segmentacion..
      *
      */
     public function getisSegmentadoAttribute($value)
     {
        if (! isset($this->_isSegmentado)){
              $result = MyDB::isSegmentado($this,$this->esquema);
              return $this->_isSegmentado = $result;
              if ($result>0):
                  $this->_isSegmentado = true;
              else:
                  $this->_isSegmentado = false;
              endif;
        }else{
            return $this->_isSegmentado;
        }
     }

    public function getCodigoRadAttribute($value){
        return $radio= substr(trim($this->codigo), 7, 2);
    }

    public function getCodigoFracAttribute($value){
        return $frac= substr(trim($this->codigo), 5, 2);
    }

    public function getEsquemaAttribute($value){
      if ($this->_esquema){
	      return $this->_esquema;
     	}else{
	     $this->_esquema='cualca';
	     $posibles_esquemas=$this->esquemas;
	    return $this->_esquema=$posibles_esquemas[0];
	    }
    }

    public function getEsquemasAttribute($value){
        if (! $this->_esquemas){
                 try{
           			    if(!$this->fraccion){
                         	    Log::error('Radio sin fracción? : '.collect($this)->toJson(JSON_PRETTY_PRINT));
				                     $esquemas[]='e'.$this->codigo;
          	        }elseif ($this->fraccion->departamento->provincia->codigo == '06') {
                                $esquemas[]='e'.$this->fraccion->departamento->codigo;
                      }elseif ($this->localidades()->count() > 1) {
              				   $loc_no_rural=$this->localidades()->whereHas('aglomerado', function($q) {
                                              $q->where('codigo', 'not like', '%0000%');
                                               })->get();
               				   if ($loc_no_rural->count() > 1) {
		               			   Log::warning('TODO: Implementar radio multilocalidades'.$this->localidades()->get()->toJson(
					                              JSON_PRETTY_PRINT));
                           foreach($loc_no_rural as $localidad){
                                 Log::info('Posible esquema: e'.($localidad->codigo));
                                 $esquemas[]='e'.$localidad->codigo;
                           }
              				      $esquemas[]='e'.$this->fraccion->departamento->codigo;
				                }else{
					                    Log::info('Buscando parte Urbana del Radio en el esquema de la única localidad:'.
                         						    ($loc_no_rural->first()->codigo));
                              $esquemas[]='e'.$loc_no_rural->first()->codigo;
				                }
			              }elseif($this->localidades()->count()==1){
                       $esquemas[]='e'.$this->localidades()->first()->codigo;
                      }else{
                       $esquemas[]='e'.$this->codigo;
                       Log::error('No se encontró localidad para el radio: '.$this->codigo);
			              }
                }catch (Exception $e){
                 Log::error('Algo muy raro paso: '.$e);
                };
          if ($this->aglomerados() != null){
             			foreach ($this->aglomerados() as $aglo){
	                    $esquemas[]='e'.$aglo->codigo;
            			}
          }
        Log::debug('Radio '.$this->codigo.' esperado en esquemas => '.collect($esquemas)->toJson(
                                           JSON_PRETTY_PRINT));
        return $this->_esquemas=$esquemas; //$this->_esquema;
        }
        return $this->_esquemas; //$this->_esquema;
    }

    public function getSVG()
    {
        // return SVG Radio? Listado? Segmentación?
        if (Schema::hasTable($this->esquema.'.listado_geo')){
            $height=600;
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
                 ( SELECT st_buffer(wkb_geometry,-5) geom, -1*mza::integer, 'mza' tipo
                         FROM ".$this->esquema.".manzanas
                    WHERE  prov||dpto||frac||radio='".$this->codigo."'
                 ) ";
            $mzas_labels="
                UNION (SELECT '<text x=\"'||st_x(st_centroid(wkb_geometry))||'\"
                y=\"-'||st_y(st_centroid(wkb_geometry))||'\">'||mza||'</text>'
                as svg ,20 as orden
                FROM ".$this->esquema.".manzanas
                    WHERE  prov||dpto||frac||radio='".$this->codigo."' )";
            }else{Log::debug('No se encontro grafica de manzanas. ');$mzas='';$mzas_labels='';}

            //dd($viewBox.'/n'.$this->viewBox($extent,$epsilon,$height,$width).'/n'.$x0." -".$y0." ".$x1." -".$y1);
            $svg=DB::select("
WITH shapes (geom, attribute, tipo) AS (
    ( SELECT st_buffer(CASE WHEN trim(lg.tipoviv) in ('','LSV') then lg.wkb_geometry_lado
    else lg.wkb_geometry END,1) wkb_geometry, 
    rank() over (order by segmento_id::integer) as attribute,
    lg.tipoviv tipo
    FROM ".$this->esquema.".listado_geo lg JOIN ".$this->esquema.".segmentacion
    s ON s.listado_id=id_list
    WHERE  substr(mzae,1,5)||substr(mzae,9,4)='".$this->codigo."'
    ) ".$mzas."
  ),
  paths (svg,orden) as (
     SELECT * FROM (
     (SELECT concat(
         '<path d= \"',
         ST_AsSVG(st_buffer(geom,3),0), '\" ',
         CASE WHEN attribute = 0 THEN 'stroke=\"gray\" stroke-width=\"2\"
         fill=\"gray\"'
              WHEN tipo='mza' THEN 'stroke=\"white\"
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
          ' />') as svg,
          CASE WHEN tipo='mza' then 0
               WHEN tipo='LSV' then 1
          ELSE 10 END as orden
     FROM shapes
     ORDER BY attribute asc)
     ".$mzas_labels." ) foo order by orden asc
 )
 SELECT concat(
         '<svg id=\"radio_".$this->codigo."_botonera\"xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0
	 \" height=\"80\" width=\"".$width."\">',
	 '<circle style=\"opacity: 10%;\" class=\"compass\" cx=\"".(+30)."\" cy=\"".(30)."\" r=\"28\"></circle>
         <circle style=\"opacity: 20%;\" class=\"button\" cx=\"".(+30)."\" cy=\"".(36)."\"
         r=\"7\"
         onclick=\"zoom(0.9)\"/>
        <circle style=\"opacity: 20%;\" class=\"button\" cx=\"".(30)."\" cy=\"".(+24)."\"
	r=\"7\"
	onclick=\"zoom(1.1)\"/>
	<path style=\"opacity: 10%;\" class=\"button\" onclick=\"pan(0, 25)\" d=\"M".(+30)." ".(+5)." l6 10 a20 35 0 0 0 -12 0z\" />
	<path style=\"opacity: 10%;\" class=\"button\" onclick=\"pan(25, 0)\" d=\"M".(+5)." ".(+30)." l10 -6 a35 20 0 0 0 0 12z\" />
	<path style=\"opacity: 10%;\" class=\"button\" onclick=\"pan(0,-25)\" d=\"M".(+30)." ".(55)." l6 -10 a20 35 0 0,1 -12,0z\" />
	<path style=\"opacity: 10%;\" class=\"button\" onclick=\"pan(-25, 0)\" d=\"M".(+55)." ".(+30)." l-10 -6 a35 20 0 0 1 0 12z\" />
	',
	 '</svg>',
         '<svg id=\"radio_".$this->codigo."\"xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"".$viewBox.
	 "\" height=\"".$height."\" width=\"".$width."\">',
	 ' <g id=\"matrix-group\" transform=\"matrix(1 0 0 1 0 0)\">',
	 array_to_string(array_agg(svg),''),
	 '</g></svg>'
	    )
 FROM paths;
");
            return $svg[0]->concat;
        }else{ return "Por el momento no se puede previsualizar el radio."; }

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
