@extends('layouts.app')

@section('content')
<div class="container">
   <h4 class="center"> Carga de hitos geográficos.</h4>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @isset($data)
                <div class="alert alert-primary" role="alert">
                    <ul>
      @foreach ($data as $index => $value)
				@if (isset($data['file']))
                   	<p>El usuario {{Auth::user()->name}} subió los siguientes archivos:</p>
                       	@foreach ($value as $index_file => $value_file)
  	                    		<li>{{$index_file}} -> {{$value_file}}</li>
           				@endforeach
				@else 
	                    <p>Y estas otras cosas... :</p>
               	        <li>{{$index}} -> {{$value}}</li>
				@endif
        @if ($loop->last)
        	The current UNIX timestamp is {{ time() }}. {{ date('Y-m-d H:m:s') }} UTC.
        @endif
     @endforeach
                    </ul>
                </div>
            @endisset
            <form action="/segmentador/guardar_hitos" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group row">
                    <label for="hitos_shp" class="col-sm-2 col-form-label ">Base geográfica (e00/SHP)</label>
                    <div class="col-sm-10">
                        <input type="file" class="form-control-file" id="hitos_shp" name="hitos_shp">
                    </div>
		     @if (isset($epsgs))
                    <div class="col-sm-10">
                	<select name="epsg_id" class="form-control">
			     <option></option>
			        @foreach($epsgs as $id => $epsg)
					<option value="{{$id}}"> {{$epsg}} </option>
			        @endforeach
			    </select>
		    </div>
		     @endif
                </div>
                <div class="form-group row">
                    <label for="hitos_shx" class="col-sm-2 col-form-label ">SHX</label>
                    <div class="col-sm-10">
                        <input type="file" class="form-control-file" id="hitos_shx" name="hitos_shx">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="hitos_dbf" class="col-sm-2 col-form-label ">DBF</label>
                    <div class="col-sm-10">
                        <input type="file" class="form-control-file" id="hitos_dbf" name="hitos_dbf">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="hitos_prj" class="col-sm-2 col-form-label ">PRJ</label>
                    <div class="col-sm-10">
                        <input type="file" class="form-control-file" id="hitos_prj" name="hitos_prj">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        </div>
    </div>
</div>
@endsection
