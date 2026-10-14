@extends('layout')
@section('title','No encontramos esta noticia')
@section('content')<section class="editor"><p class="eyebrow">404</p><h1>No encontramos esta noticia</h1><p>Puede que la dirección no exista o que la noticia todavía sea un borrador.</p><a class="button" href="{{ route('news.index') }}">Volver al inicio</a></section>@endsection
