@extends('layout')
@section('title','No pudimos completar la solicitud')
@section('content')<section class="editor"><p class="eyebrow">500</p><h1>No pudimos completar la solicitud</h1><p>Inténtalo de nuevo en unos momentos.</p><a class="button" href="{{ route('news.index') }}">Volver al inicio</a></section>@endsection
