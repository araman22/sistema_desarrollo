@extends('errors.layout')

@php
    // Los Response::deny de las Policies traen un motivo propio; el genérico
    // de Laravel no aporta nada, así que se reemplaza por el texto en español.
    $mensaje = $exception->getMessage();

    if ($mensaje === '' || $mensaje === 'This action is unauthorized.') {
        $mensaje = 'No tenés permiso para acceder a esta sección.';
    }
@endphp

@section('code', '403')
@section('title', 'Acceso denegado')
@section('message', $mensaje)
