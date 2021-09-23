@extends('layouts.app')

@section('title', 'Aglomerado {{ $aglomerado->nombre }}')

@section('content')
     @include('aglo.radio')
@stop
