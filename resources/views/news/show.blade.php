@extends('layout')
@section('title',$post->title)
@section('content')
<article class="story"><a class="back" href="{{ route('news.index') }}">← Volver a noticias</a><p class="eyebrow">{{ $post->category->name }}</p><h1>{{ $post->title }}</h1><p class="lead">{{ $post->excerpt }}</p><div class="byline"><span>Por {{ $post->author->name }}</span><time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('d/m/Y') }}</time></div><div class="story-body">{{ $post->body }}</div><p class="demo-note">Este contenido es ficticio y forma parte de una demostración académica.</p></article>
@endsection
