@extends('layout')
@section('title','Acceso editorial')
@section('content')
<section class="login-shell"><div><p class="eyebrow">ESPACIO DE REDACCIÓN</p><h1>Las buenas historias<br>empiezan aquí<span>.</span></h1><p>Ingresa para crear, revisar y publicar las noticias de Pulso.</p></div><div class="form-panel"><h2>Acceso editorial</h2>@include('partials.errors')
<form method="post" action="{{ route('login.store') }}">@csrf
<div class="field"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" autocomplete="username" required maxlength="255" value="{{ old('email') }}" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>@error('email')<p class="error" id="email-error">{{ $message }}</p>@enderror</div>
<div class="field"><label for="password">Contraseña</label><input id="password" name="password" type="password" autocomplete="current-password" required maxlength="255" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>@error('password')<p class="error" id="password-error">{{ $message }}</p>@enderror</div>
<button class="button">Ingresar</button><p class="hint">Acceso reservado al administrador del blog.</p>
</form></div></section>
@endsection
