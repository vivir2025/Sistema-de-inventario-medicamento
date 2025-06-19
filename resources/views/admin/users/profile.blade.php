
@extends('admin.layouts.app')

@push('page-header')
<div class="col">
    <h3 class="page-title">Perfil</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item active">Perfil</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-auto profile-image">
                    <a href="#">
                        <img class="rounded-circle" alt="Imagen de Usuario" src="{{!empty(auth()->user()->avatar) ? asset('storage/users/'.auth()->user()->avatar): asset('assets/img/avatar.png')}}">
                    </a>
                </div>
                <div class="col ml-md-n2 profile-user-info">
                    <h4 class="user-name mb-0">{{auth()->user()->name}}</h4>
                    <h6 class="text-muted">{{auth()->user()->email}}</h6>
                </div>
            </div>
        </div>
        <div class="profile-menu">
            <ul class="nav nav-tabs nav-tabs-solid">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#per_details_tab">Acerca de</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#password_tab">Contraseña</a>
                </li>
            </ul>
        </div>
        <div class="tab-content profile-tab-cont">

            <!-- Personal Details Tab -->
            <div class="tab-pane fade show active" id="per_details_tab">

                <!-- Personal Details -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title d-flex justify-content-between">
                                    <span>Detalles Personales</span>
                                    <a class="edit-link" data-toggle="modal" href="#edit_personal_details"><i class="fa fa-edit mr-1"></i>Editar</a>
                                </h5>
                                <div class="row">
                                    <p class="col-sm-2 text-muted text-sm-right mb-0 mb-sm-3">Nombre</p>
                                    <p class="col-sm-10">{{auth()->user()->name}}</p>
                                </div>

                                <div class="row">
                                    <p class="col-sm-2 text-muted text-sm-right mb-0 mb-sm-3">Correo Electrónico</p>
                                    <p class="col-sm-10">{{auth()->user()->email}}</p>
                                </div>

                                <div class="row">
                                    <p class="col-sm-2 text-muted text-sm-right mv-0 mb-sm-3">Rol de Usuario</p>
                                    <p class="col-sm-10">
                                        @foreach (auth()->user()->getRoleNames() as $role)
                                        {{$role}}
                                        @endforeach
                                    </p>
                                </div>

                            </div>
                        </div>

                        <!-- Edit Details Modal -->
                        <div class="modal fade" id="edit_personal_details" aria-hidden="true" role="dialog">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detalles Personales</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" enctype="multipart/form-data" action="{{route('profile.update',auth()->user())}}">
                                            @csrf
                                            <div class="row form-row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label>Nombre Completo</label>
                                                        <input class="form-control" name="name" type="text" value="{{auth()->user()->name}}" placeholder="Nombre Completo">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label>Correo Electrónico</label>
                                                        <input class="form-control" name="email" type="text" value="{{auth()->user()->email}}" placeholder="Correo Electrónico">
                                                    </div>
                                                </div>
                                                @can('edit-role')
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label>Rol</label>
                                                        <select class="form-control select edit_role" name="role">
                                                            @foreach ($roles as $role)
                                                                <option value="{{$role->name}}">{{$role->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                @endcan
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label>Avatar de Usuario</label>
                                                        <input type="file" value="{{auth()->user()->avatar}}" class="form-control" name="avatar">
                                                    </div>
                                                </div>

                                            </div>
                                            <button type="submit" class="btn btn-primary btn-block">Guardar Cambios</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Edit Details Modal -->

                    </div>
                </div>
                <!-- /Personal Details -->

            </div>
            <!-- /Personal Details Tab -->

            <!-- Change Password Tab -->
            <div id="password_tab" class="tab-pane fade">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Cambiar Contraseña</h5>
                        <div class="row">
                            <div class="col-md-10 col-lg-12">
                                <form method="POST" action="{{route('update-password',auth()->user())}}">
                                    @csrf
                                    @method("PUT")
                                    <div class="form-group">
                                        <label>Contraseña Actual</label>
                                        <input type="password" name="current_password" class="form-control" placeholder="Ingresa tu contraseña actual">
                                    </div>
                                    <div class="form-group">
                                        <label>Nueva Contraseña</label>
                                        <input type="password" name="password" class="form-control" placeholder="Ingresa tu nueva contraseña">
                                    </div>
                                    <div class="form-group">
                                        <label>Confirmar Contraseña</label>
                                        <input type="password" name="password_confirmation" class="form-control" placeholder="Repite tu nueva contraseña">
                                    </div>
                                    <button class="btn btn-primary" type="submit">Guardar Cambios</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Change Password Tab -->

        </div>
    </div>
</div>
@endsection