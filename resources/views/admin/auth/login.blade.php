@extends('admin.layouts.plain')

@section('content')
<h1>Iniciar Sesión</h1>
<p class="account-subtitle">Acceso a nuestro panel</p>

@if (session('login_error'))
    <x-alerts.danger :error="session('login_error')" />
@endif

<!-- Formulario -->
<form action="{{ route('login') }}" method="post">
    @csrf
    <div class="form-group">
        <input class="form-control" name="email" type="text" placeholder="Correo Electrónico">
    </div>
    <div class="form-group">
        <input class="form-control" name="password" type="password" placeholder="Contraseña">
    </div>
    <div class="form-group">
        <button class="btn btn-primary btn-block" type="submit">Iniciar Sesión</button>
    </div>
</form>
<!-- /Formulario -->

<div class="form-group text-center mt-3">
    <a href="http://45.167.125.238/sistemadeinventario" class="btn btn-outline-secondary btn-block">
        Sistema de Inventario
    </a>
</div>

<div class="text-center dont-have">
    ¿No tienes una cuenta? <a href="{{ route('register') }}">Regístrate</a>
</div>
@endsection
