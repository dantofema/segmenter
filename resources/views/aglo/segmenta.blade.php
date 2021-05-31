<div class="container">
    Información del aglomerado ({{ $aglomerado->codigo }}) 
    <b> {{ $aglomerado->nombre }} </b><br />
     @if($carto)
        La base geográfica está cargada.
     @else
        NO está cargada la base geográfica.
     @endif 
    <br />
     @if($listado)
        El listado de viviendas está cargado.
     @else
        NO está cargado el listado de viviendas.
     @endif 
    <br />

<div class="form-horizontal">
<form action="/aglo-segmenta-run/{{ $aglomerado->id }}" method="POST" enctype="multipart/form-data">
                @csrf

  <div class="form-group">
    <label class="control-label" for="radio">Seleccione el Radio a segmentar:</label>
    <div class="">
        <select name="radios" class="form-control" >
            @foreach($radios as $radio)
                <option value="{{ $radio->link }}">{{ trim($radio->nombre) }} - Viv: {{ trim($radio->vivs) }}</option>
            @endforeach
        </select>
    </div>
  </div>
    <div class="form-group" style="display: none; visibility: hidden;"> 
    <label class="form-check-label">
      <input name=checkallradios type="checkbox" class="form-check-input"
      value=allradios>
          Todos los radios
    </label>
  </div>  
  <div class="form-group">
    <label class="control-label" for="radio">Método de segmentación:</label><br />
    <label class="radio-inline"><input type="radio" name="optalgoritmo" value=listado>Manzanas independientes</label><br />
    <label class="radio-inline"><input type="radio" name="optalgoritmo" value=lados>Lados Completos</label><br />
    <label class="radio-inline" title="Primero segmenta a lados completos, luego los lados excedidos son segmentados a lado independiente."
    alt="Primero segmenta a lados completos, luego los lados excedidos son segmentados a lado independiente."><input type="radio" name="optalgoritmo"
    value=magic checked>Mixto</label><br />
  </div>
  <div class="form-group">
   <label class="control-label" for="radio">Cuestionario: </label>
   <div class="form-check form-check-inline">
    <input class="form-check-input" type="radio" name="Cuestionario"
    id="CuestionarioB" value="B" checked>
    <label class="form-check-label" for="basico">Básico</label>
   </div>
   <div class="form-check">
     <input class="form-check-input" type="radio" name="Cuestionario"
     id="CuestionarioA" value="A">
     <label class="form-check-label" for="basico">Ampliado</label>
    </div>
  </div>
  <div class="form-group">
    <label class="control-label" for="radio">Parametros:</label><br />
    <div class="">
    <label class="control-label " for="radio">Cantidad deseada de viviendas:</label>
    <input id="vivs_deaseadas" type="integer" maxlength=3 size=3
    name="vivs_deseadas" value="36"><br />
    </div>
    <div class="">
    <label class="control-label " for="radio">Cantidad máxima deseada:</label>
    <input id="vivs_max" type="integer" maxlength=3 size=3 name="vivs_max"
    value="40"><br />
    </div>
    <div class="">
    <label class="control-label " for="radio">Cantidad Mínima deseada:</label>
    <input id="vivs_min" type="integer" maxlength=3 size=3 name="vivs_min"
    value="32"><br />
    </div>
    <div class="">
    <label class="control-label " for="radio">Mantener manzana indivisible para manzanas con menos de:</label>
    <input id="mzas_indivisibles" type="integer" maxlength=3 size=3
    name="mzas_indivisibles" value="9"> viviendas
    </div>
 </div>
 <div class="mx-auto">
 <input type="submit" class="segmentar btn btn-primary" value="Segmentar">
 </div>
</form>
</div>
</div>
<div>
