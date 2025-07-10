@extends('admin.layouts.app')

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Editar Compra</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item active">Editar Compra</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body custom-edit-service">
            
            <!-- Editar Compra -->
            <form method="post" enctype="multipart/form-data" autocomplete="off" action="{{route('purchases.update',$purchase)}}">
                @csrf
                @method("PUT")
                
                <!-- Información del Lote -->
                <div class="service-fields mb-3">
                    <h5 class="mb-3"><i class="fas fa-box"></i> Información del Lote</h5>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Número de Lote</label>
                                <input class="form-control" type="text" name="batch_number" 
                                       value="{{$purchase->batch_number}}" readonly>
                                <small class="text-muted">El número de lote no se puede modificar</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Serie</label>
                                <input class="form-control" type="text" name="serie" 
                                       value="{{$purchase->serie}}" placeholder="Número de serie del lote">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Fecha de Vencimiento<span class="text-danger">*</span></label>
                                <input class="form-control" value="{{$purchase->expiry_date}}" type="date" name="expiry_date">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Producto -->
                <div class="service-fields mb-3">
                    <h5 class="mb-3"><i class="fas fa-pills"></i> Información del Medicamento</h5>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Nombre del P.Act. - D.Med. - Insumo<span class="text-danger">*</span></label>
                                <input class="form-control" type="text" value="{{$purchase->product}}" name="product" >
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Marca</label>
                                <input class="form-control" type="text" name="marca" 
                                       value="{{$purchase->marca}}" placeholder="Marca del medicamento">

                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Categoría <span class="text-danger">*</span></label>
                                <select class="select2 form-select form-control" name="category"> 
                                    @foreach ($categories as $category)
                                        <option {{($purchase->category->id == $category->id) ? 'selected': ''}} value="{{$category->id}}">{{$category->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Proveedor <span class="text-danger">*</span></label>
                                <select class="select2 form-select form-control" name="supplier"> 
                                    @foreach ($suppliers as $supplier)
                                        <option @if($purchase->supplier->id == $supplier->id) selected @endif value="{{$supplier->id}}">{{$supplier->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información Farmacéutica -->
                <div class="service-fields mb-3">
                    <h5 class="mb-3"><i class="fas fa-prescription-bottle"></i> Información Farmacéutica</h5>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Concentración</label>
                                <input class="form-control" type="text" name="concentracion" 
                                       value="{{$purchase->concentracion}}" placeholder="Ej: 500mg, 10ml, 2.5%">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Forma Farmacéutica</label>
                                <input class="form-control" type="text" name="forma_farmaceutica" 
                                       value="{{$purchase->forma_farmaceutica}}" placeholder="Ej: Tableta, Cápsula, Jarabe">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Presentación Comercial</label>
                                <input class="form-control" type="text" name="presentacion_comercial" 
                                       value="{{$purchase->presentacion_comercial}}" placeholder="Ej: Frasco x 30 tabletas">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Unidad de Medida</label>
                                <input class="form-control" type="text" name="unidad_medida" 
                                       value="{{$purchase->unidad_medida}}" placeholder="Ej: mg, ml, gr, UI">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Registro Sanitario</label>
                                <input class="form-control" type="text" name="registro_sanitario" 
                                       value="{{$purchase->registro_sanitario}}" placeholder="Número de registro sanitario">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Nivel de Riesgo</label>
                                <select class="form-control" name="riesgo">
                                    <option value="">Seleccionar Nivel</option>
                                    <option value="Alto" {{$purchase->riesgo == 'Alto' ? 'selected' : ''}}>Alto</option>
                                    <option value="Medio" {{$purchase->riesgo == 'Medio' ? 'selected' : ''}}>Medio</option>
                                    <option value="Bajo" {{$purchase->riesgo == 'Bajo' ? 'selected' : ''}}>Bajo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Vida Útil</label>
                                <input class="form-control" type="text" name="vida_util" 
                                       value="{{$purchase->vida_util}}" placeholder="Ej: 24 meses, 3 años">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Precios y Cantidades -->
                <div class="service-fields mb-3">
                    <h5 class="mb-3"><i class="fas fa-dollar-sign"></i> Precios y Cantidades</h5>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Precio de Costo<span class="text-danger">*</span></label>
                                <input class="form-control" value="{{$purchase->cost_price}}" type="text" name="cost_price">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Cantidad<span class="text-danger">*</span></label>
                                <input class="form-control" value="{{$purchase->quantity}}" type="text" name="quantity">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Imagen y Notas -->
                <div class="service-fields mb-3">
                    <h5 class="mb-3"><i class="fas fa-image"></i> Imagen y Observaciones</h5>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Imagen del Medicamento</label>
                                <input type="file" name="image" class="form-control">
                                @if($purchase->image)
                                    <small class="text-muted">Imagen actual: {{$purchase->image}}</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Notas del Lote</label>
                                <textarea class="form-control" name="notes" rows="3" 
                                          placeholder="Observaciones sobre este lote...">{{$purchase->notes}}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="submit-section">
                    <button class="btn btn-primary submit-btn" type="submit">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                    <a href="{{route('purchases.index')}}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                </div>
            </form>
            <!-- /Editar Compra -->

            </div>
        </div>
    </div>			
</div>
@endsection	

@push('page-js')
    <!-- Select2 JS -->
    <script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
@endpush