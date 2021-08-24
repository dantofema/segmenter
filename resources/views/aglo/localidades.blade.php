<div class="container">
    {{$provincias[]=null}}
    @foreach($aglomerado->localidades as $localidad)
      	@foreach($localidad->departamentos as $departamento)
	    @php ($provincias[$departamento->provincia->codigo]=$departamento->provincia)
	@endforeach
    @endforeach
    @foreach($provincias as $provincia)
		@if ($provincia)
                  <li class="btn  btn-outline-secondary" style="margin-bottom: 2px" >
                     <a href="{{ url("/prov/{$provincia->id}") }}" >({{ $provincia->codigo }})
                     <b> {{ $provincia->nombre }} </b></a>
                 </li>
               @endif
    @endforeach
    <h3>Aglomerado ({{ $aglomerado->codigo }}) 
    <b> {{ $aglomerado->nombre }} </b></h3>
	 {{$aglomerado->localidades->count()}} localidades.
            En Departamento:
	    @foreach($aglomerado->localidades as $localidad)
            	@foreach($localidad->departamentos as $departamento)
		 <a href="{{ url('/depto/'.$departamento->id) }}">
({{ $departamento->codigo }}) {{ $departamento->nombre }} </a>
		@endforeach
            @endforeach

<div class="form-horizontal">
<form action="/grafo/{{ $aglomerado->id }}" method="GET" enctype="multipart/form-data">
                @csrf

  <div class="form-group">
    <label class="control-label" for="localidad">Seleccione una Localidad:</label>
    <div class="">
    <ul class="nav row justify-content-around">
            @foreach($localidades as $localidad)
    <li class="btn  btn-outline-primary" style="margin-bottom: 5px" >
    <a href="{{ url('/localidad/'.$localidad->id) }}">
	{{ trim($localidad->codigo) }}: {{ trim($localidad->nombre) }}</br >

    </a>(
	    @foreach($localidad->departamentos()->orderBy('codigo')->get() as $departamento)
    		<a href="{{ url('/depto/'.$departamento->id) }}">
		{{ trim($departamento->nombre) }} 
		</a>
    @if (!$loop->last)
        |
    @endif
            @endforeach
)
    </li>
            @endforeach
    </ul>
    </div>
  </div>
</form>
</div>

</div>
