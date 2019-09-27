@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @isset($data)
                <div class="alert alert-primary" role="alert">
                    <p>El usuario {{Auth::user()->name}} subió los siguientes archivos:</p>
                    <ul>
                        @foreach ($data as $index => $value)
                            <li>{{$index}} -> {{$value}}</li>
                        @endforeach
                    </ul>
                </div>
            @endisset
            <form action="/segmentador/guardar" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group row">
                    <label for="shp" class="col-sm-2 col-form-label ">SHP</label>
                    <div class="col-sm-10">
                        <input type="file" class="form-control-file" id="shp" name="shp">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="shx" class="col-sm-2 col-form-label ">SHX</label>
                    <div class="col-sm-10">
                        <input type="file" class="form-control-file" id="shx" name="shx">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="dbf" class="col-sm-2 col-form-label ">DBF</label>
                    <div class="col-sm-10">
                        <input type="file" class="form-control-file" id="dbf" name="dbf">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="prj" class="col-sm-2 col-form-label ">PRJ</label>
                    <div class="col-sm-10">
                        <input type="file" class="form-control-file" id="prj" name="prj">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="c1" class="col-sm-2 col-form-label ">C1</label>
                    <div class="col-sm-10">
                        <input type="file" class="form-control-file" id="c1" name="c1">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        </div>
    </div>
</div>
@endsection
