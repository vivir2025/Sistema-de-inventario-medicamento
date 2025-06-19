
@extends('admin.layouts.app')

@push('page-css')
    <!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Agregar Compra</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item active">Agregar Compra</li>
    </ul>
</div>
@endpush


@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body custom-edit-service">
                
                <!-- Agregar Medicamento -->
                <form method="post" enctype="multipart/form-data" autocomplete="off" action="{{route('purchases.store')}}">
                    @csrf
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Nombre del Medicamento<span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="product" >
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Categoría <span class="text-danger">*</span></label>
                                    <select class="select2 form-select form-control" name="category"> 
                                        @foreach ($categories as $category)
                                            <option value="{{$category->id}}">{{$category->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Proveedor <span class="text-danger">*</span></label>
                                    <select class="select2 form-select form-control" name="supplier"> 
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{$supplier->id}}">{{$supplier->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Precio de Costo<span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="cost_price">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Cantidad<span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="quantity">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Fecha de Vencimiento<span class="text-danger">*</span></label>
                                    <input class="form-control" type="date" name="expiry_date">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Imagen del Medicamento</label>
                                    <input type="file" name="image" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn" type="submit" >Guardar</button>
                    </div>
                </form>
                <!-- /Agregar Medicamento -->

            </div>
        </div>
    </div>			
</div>
@endsection

@push('page-js')
    <!-- Datetimepicker JS -->
    <script src="{{asset('assets/js/moment.min.js')}}"></script>
    <script src="{{asset('assets/js/bootstrap-datetimepicker.min.js')}}"></script>	
@endpush