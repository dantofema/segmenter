@extends('errors::minimal')

@section('title', __('Servicio no disponible'))
@section('code', '503')
@section('message', __($exception->getMessage() ?: 'Servicio no disponible'))
