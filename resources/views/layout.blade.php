<!doctype html>
<html lang="es" data-theme="{{ $blogTheme ?? 'light' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Blog académico de noticias sobre tecnología, educación y comunidad.">
    <title>@yield('title', 'Pulso Noticias') · Pulso</title>
    <link rel="stylesheet" href="{{ asset('css/blog.css') }}">
    <link rel="stylesheet" href="{{ asset('css/themes.css') }}">
</head>
<body>
<a class="skip" href="#contenido">Saltar al contenido</a>
<div class="topline"><div class="wrap">EDICIÓN ACADÉMICA <span>TECNOLOGÍA · EDUCACIÓN · COMUNIDAD</span></div></div>
<header class="site-header wrap">
    <a class="brand" href="{{ route('news.index') }}" aria-label="Pulso Noticias, inicio">pulso<span>NOTICIAS</span></a>
    <nav aria-label="Navegación principal">
        <a href="{{ route('news.index') }}">Actualidad</a>
        @auth
            @if(auth()->user()->is_admin)<a href="{{ route('admin.posts.index') }}">Administrar</a>@endif
            <form action="{{ route('logout') }}" method="post">@csrf<button class="text-button">Cerrar sesión</button></form>
        @else <a class="nav-admin" href="{{ route('login') }}">Acceso editorial <span aria-hidden="true">↗</span></a> @endauth
    </nav>
</header>
<div class="wrap appearance-bar">
    <form class="appearance-form" method="get" action="{{ url()->current() }}" aria-label="Preferencias de color">
        @foreach(request()->except('tema') as $key => $value)
            @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
        @endforeach
        <label for="theme">Paleta de colores</label>
        <select id="theme" name="tema" aria-describedby="theme-help">
            @foreach(($blogThemes ?? config('themes')) as $value => $label)
                <option value="{{ $value }}" @selected(($blogTheme ?? 'light') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="button" type="submit">Aplicar</button>
    </form>
    <p id="theme-help" class="appearance-help">Elige la combinación que distingas mejor. Se mantiene mientras navegas en esta sesión.</p>
</div>

<main id="contenido" class="wrap" tabindex="-1">
    @if(session('success'))<div class="success" role="status">{{ session('success') }}</div>@endif
    @yield('content')
</main>
<footer class="wrap site-footer"><strong>pulso.</strong><p>Información clara. Ideas que conectan.</p><small>Prototipo académico PA02 · Noticias y datos de demostración ficticios · 2026</small></footer>
</body>
</html>
