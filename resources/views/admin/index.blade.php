@extends('layout')
@section('title','Administrar noticias')
@section('content')
<div class="page-heading"><div><p class="eyebrow">PANEL EDITORIAL</p><h1>Tus noticias<span>.</span></h1><p>Prepara borradores y decide cuándo compartirlos.</p></div><a class="button" href="{{ route('admin.posts.create') }}">+ Crear noticia</a></div>
<div class="table-scroll"><table><caption>Noticias registradas en el blog</caption><thead><tr><th scope="col">Noticia</th><th scope="col">Categoría</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead><tbody>
@forelse($posts as $post)<tr><th scope="row">{{ $post->title }}</th><td>{{ $post->category->name }}</td><td><span class="status {{ $post->status }}">{{ $post->status === 'published' ? 'Publicada' : 'Borrador' }}</span></td><td class="actions"><a href="{{ route('admin.posts.edit',$post) }}" aria-label="Editar {{ $post->title }}">Editar</a><a href="{{ route('admin.posts.confirm',$post) }}" aria-label="Eliminar {{ $post->title }}">Eliminar</a></td></tr>
@empty<tr><td colspan="4">Todavía no hay noticias. Crea la primera.</td></tr>@endforelse
</tbody></table></div>@include('partials.pagination')
@endsection
