@extends('layouts.default')

@section('title', 'Provincia {{ $provincia->nombre }}')

@section('content')
<div class=container >
     @include('provinfo')
</div>
@stop
