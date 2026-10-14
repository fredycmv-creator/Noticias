@extends('layout')
@section('title','Actualidad')
@section('content')
<section class="intro"><p class="eyebrow">UNA MIRADA A LO QUE NOS MUEVE</p><h1>Ideas, historias<br> y nuevas perspectivas<span>.</span></h1><p>Explora las novedades de tecnología, educación y comunidad.</p></section>
<form class="filters" method="get" action="{{ route('news.index') }}" role="search">
    <div><label for="q">Buscar noticias</label><input id="q" name="q" type="search" maxlength="100" value="{{ request('q') }}" placeholder="¿Qué te interesa leer?"></div>
    <div><label for="categoria">Categoría</label><select id="categoria" name="categoria"><option value="">Todas las categorías</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('categoria') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
    <button class="button">Buscar</button><a class="clear" href="{{ route('news.index') }}">Limpiar filtros</a>
</form>
@include('partials.errors')
<div class="section-label"><h2>Últimas noticias</h2><span>{{ $posts->total() }} resultados</span></div>
<section class="news-grid" aria-label="Noticias publicadas">
@forelse($posts as $post)
<article class="news-card">
    <div class="card-marker" aria-hidden="true"><span>{{ str_pad($loop->iteration + ($posts->currentPage()-1)*6,2,'0',STR_PAD_LEFT) }}</span><span>↗</span></div>
    <div class="card-content"><span class="tag">{{ $post->category->name }}</span><h3><a href="{{ route('news.show',$post->slug) }}">{{ $post->title }}</a></h3><p>{{ $post->excerpt }}</p><div class="byline"><span>{{ $post->author->name }}</span><time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('d/m/Y') }}</time></div></div>
</article>
@empty<div class="empty"><h3>No encontramos noticias</h3><p>Prueba con otra palabra o selecciona una categoría diferente.</p><a href="{{ route('news.index') }}">Ver todas las noticias</a></div>@endforelse
</section>
@include('partials.pagination')
@endsection
