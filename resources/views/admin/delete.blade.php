@extends('layout')
@section('title','Confirmar eliminación')
@section('content')
<section class="editor"><p class="eyebrow">CONFIRMACIÓN</p><h1>Eliminar noticia<span>.</span></h1><p>Vas a eliminar <strong>{{ $post->title }}</strong>. Esta acción no se puede deshacer.</p><form method="post" action="{{ route('admin.posts.destroy',$post) }}">@csrf @method('DELETE')<div class="form-actions"><button class="button danger">Sí, eliminar noticia</button><a href="{{ route('admin.posts.index') }}">Cancelar y conservar</a></div></form></section>
@endsection
