@extends('layouts.default')

@section('title', '{{ $departamento->parent->nombre ?? ""}} Departamento: {{ $departamento->nombre }}')

@section('content')
     @include('deptoinfo')
@stop
