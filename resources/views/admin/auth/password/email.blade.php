
@extends('admin.layouts.plain')

@section('content')
<h1>¿Olvidaste tu contraseña?</h1>
<p class="account-subtitle">Ingresa tu correo electrónico para recibir un enlace de restablecimiento</p>
<!-- Formulario -->
<form action="{{route('password.request')}}" method="post">
    @csrf
    <div class="form-group">
        <input class="form-control" name="email" type="text" placeholder="Correo Electrónico">
    </div>
    <div class="form-group mb-0">
        <button class="btn btn-primary btn-block" type="submit">Enviar</button>
    </div>
</form>
<!-- /Formulario -->

<div class="text-center dont-have">¿Recuerdas tu contraseña? <a href="{{route('login')}}">Inicia sesión</a></div>
@endsection