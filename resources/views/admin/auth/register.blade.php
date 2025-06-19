
@extends('admin.layouts.plain')

@section('content')
<h1>Registro</h1>
<p class="account-subtitle">Acceso a nuestro panel</p>

<!-- Formulario -->
<form action="{{route('register')}}" method="POST">
    @csrf
    <div class="form-group">
        <input class="form-control" name="name" type="text" value="{{old('name')}}" placeholder="Nombre Completo">
    </div>
    <div class="form-group">
        <input class="form-control" name="email" type="text" value="{{old('email')}}" placeholder="Correo Electrónico">
    </div>
    <div class="form-group">
        <input class="form-control" name="password" type="password" placeholder="Contraseña">
    </div>
    <div class="form-group">
        <input class="form-control" name="password_confirmation" type="password" placeholder="Confirmar Contraseña">
    </div>
    <div class="form-group mb-0">
        <button class="btn btn-primary btn-block" type="submit">Registrarse</button>
    </div>
</form>
<!-- /Formulario -->
                                
<div class="text-center dont-have">¿Ya tienes una cuenta? <a href="{{route('login')}}">Iniciar sesión</a></div>
@endsection