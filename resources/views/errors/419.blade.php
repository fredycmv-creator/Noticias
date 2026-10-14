@extends('layout')
@section('title','Tu sesión ha caducado')
@section('content')<section class="editor"><p class="eyebrow">419</p><h1>Tu sesión ha caducado</h1><p>Vuelve al formulario y recarga la página antes de enviarlo.</p><a class="button" href="{{ route('news.index') }}">Volver al inicio</a></section>@endsection
