@extends('layout')
@section('title','Acceso restringido')
@section('content')<section class="editor"><p class="eyebrow">403</p><h1>Acceso restringido</h1><p>Esta sección está reservada al administrador.</p><a class="button" href="{{ route('news.index') }}">Volver al inicio</a></section>@endsection
