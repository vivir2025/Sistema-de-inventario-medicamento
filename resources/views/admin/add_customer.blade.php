@extends('admin.layouts.app')

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Agregar Nuevo Cliente</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
        <li class="breadcrumb-item active">Agregar Cliente</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body custom-edit-service">
                <form method="POST" action="{{ url('/insert-customer') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row form-row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombre del Cliente</label>
                                <input class="form-control" name="name" type="text" placeholder="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Correo Electrónico</label>
                                <input class="form-control" name="email" type="text" placeholder="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Identificacion</label>
                                <input class="form-control" name="company" type="text" placeholder="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Dirección</label>
                                <input class="form-control" name="address" type="text" placeholder="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input class="form-control" name="phone" type="text" placeholder="" />
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Guardar</button>
                </form>
            </div>
        </div>
    </div>          
</div>
@endsection

@push('page-js')
    
@endpush