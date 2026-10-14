@if($posts->hasPages())
<nav class="pagination" aria-label="Páginas de noticias">
    @if($posts->onFirstPage())<span>Anterior</span>@else<a href="{{ $posts->previousPageUrl() }}" rel="prev">← Anterior</a>@endif
    <span aria-current="page">Página {{ $posts->currentPage() }} de {{ $posts->lastPage() }}</span>
    @if($posts->hasMorePages())<a href="{{ $posts->nextPageUrl() }}" rel="next">Siguiente →</a>@else<span>Siguiente</span>@endif
</nav>
@endif
