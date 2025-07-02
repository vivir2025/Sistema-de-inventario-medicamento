@extends('admin.layouts.app')

@push('page-css')
    
@endpush    

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Agregar Producto</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item active">Agregar Producto</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body custom-edit-service">
                <!-- Add Product -->
                <form method="post" enctype="multipart/form-data" id="update_service" action="{{route('products.store')}}">
                    @csrf
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Producto <span class="text-danger">*</span></label>
                                    <select class="select2 form-select form-control" name="product"> 
                                         @foreach ($purchases as $purchase)
            <option value="{{$purchase->id}}">{{$purchase->product}} - {{$purchase->batch_number}}</option>
        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NUEVO CAMPO DE MUNICIPIO -->
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Municipio <span class="text-danger">*</span></label>
                                    <select class="form-select form-control" name="municipality" required>
                                        <option value="">Seleccione un municipio</option>
                                        <option value="cajibio">Cajibío</option>
                                        <option value="morales">Morales</option>
                                        <option value="piendamo">Piendamó</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Precio de Venta<span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="price" value="{{old('price')}}">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Descuento (%)<span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="discount" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Descripciones <span class="text-danger">*</span></label>
                                    <textarea class="form-control service-desc" name="description">{{old('description')}}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn" type="submit" name="form_submit" value="submit">Guardar</button>
                    </div>
                </form>
                <!-- /Add Product -->
            </div>
        </div>
    </div>			
</div>
@endsection

@push('page-js')
    
@endpush