@extends('layouts.app')

@section('title', '{{ $departamento->parent->nombre ?? ""}} Departamento: {{ $departamento->nombre }}')

@section('content')
<div class=container >
     @include('deptoinfo')
</div>
@stop
