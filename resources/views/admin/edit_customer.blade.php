

@extends('layouts.admin_master')

@section('content')
<main>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-lg border-0 rounded-lg mt-5">
                    <div class="card-header">
                        <h3 class="text-center font-weight-light my-4">Editar Cliente</h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                       
                        <form method="POST" action="{{ route('update.customer', $customer->id) }}" enctype="multipart/form-data">
                            @csrf
                            {{-- CAMBIO: Usar POST en lugar de PUT porque la ruta está definida como POST --}}
                            
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small mb-1" for="name">Nombre del Cliente</label>
                                        <input class="form-control py-4" name="name" value="{{ old('name', $customer->name) }}" type="text" placeholder="Ingrese el nombre" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small mb-1" for="email">Correo Electrónico</label>
                                        <input class="form-control py-4" name="email" value="{{ old('email', $customer->email) }}" type="email" placeholder="correo@ejemplo.com" required />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small mb-1" for="company">Empresa</label>
                                        <input class="form-control py-4" name="company" value="{{ old('company', $customer->company) }}" type="text" placeholder="Nombre de la empresa" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="small mb-1" for="phone">Teléfono</label>
                                        <input class="form-control py-4" name="phone" value="{{ old('phone', $customer->phone) }}" type="text" placeholder="Número de teléfono" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="small mb-1" for="address">Dirección</label>
                                <textarea class="form-control py-4" name="address" rows="3" placeholder="Dirección completa">{{ old('address', $customer->address) }}</textarea>
                            </div>
                            
                            <div class="form-group mt-4 mb-0">
                                <button class="btn btn-primary btn-block" type="submit">Actualizar Cliente</button>
                            </div>
                            
                            <div class="form-group mt-2 mb-0">
                                {{-- CAMBIO: Usar route() en lugar de URL::to() --}}
                                <a href="{{ route('all.customers') }}" class="btn btn-secondary btn-block">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection