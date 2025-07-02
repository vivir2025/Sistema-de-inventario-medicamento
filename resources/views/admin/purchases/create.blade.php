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
                    
                    <!-- Información del Lote -->
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Número de Lote</label>
                                    <input class="form-control" type="text" name="batch_number" 
                                           placeholder="Se generará automáticamente si se deja vacío"
                                           value="{{old('batch_number')}}">
                                    <small class="text-muted">Ejemplo: LOTE-2025-0001</small>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Fecha de Vencimiento<span class="text-danger">*</span></label>
                                    <input class="form-control" type="date" name="expiry_date" 
                                           value="{{old('expiry_date')}}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Producto -->
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Nombre del Medicamento<span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="product" 
                                           value="{{old('product')}}" required>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Categoría <span class="text-danger">*</span></label>
                                    <select class="select2 form-select form-control" name="category" required> 
                                        <option value="">Seleccionar Categoría</option>
                                        @foreach ($categories as $category)
                                            <option value="{{$category->id}}" 
                                                    {{old('category') == $category->id ? 'selected' : ''}}>
                                                {{$category->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Proveedor <span class="text-danger">*</span></label>
                                    <select class="select2 form-select form-control" name="supplier" required> 
                                        <option value="">Seleccionar Proveedor</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{$supplier->id}}"
                                                    {{old('supplier') == $supplier->id ? 'selected' : ''}}>
                                                {{$supplier->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Precios y Cantidades -->
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Precio de Costo<span class="text-danger">*</span></label>
                                    <input class="form-control" type="number" step="0.01" name="cost_price" 
                                           value="{{old('cost_price')}}" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Cantidad<span class="text-danger">*</span></label>
                                    <input class="form-control" type="number" name="quantity" 
                                           value="{{old('quantity')}}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Imagen y Notas -->
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Imagen del Medicamento</label>
                                    <input type="file" name="image" class="form-control" 
                                           accept="image/jpeg,image/png,image/jpg,image/gif">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Notas del Lote</label>
                                    <textarea class="form-control" name="notes" rows="3" 
                                              placeholder="Observaciones sobre este lote...">{{old('notes')}}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alerta de Productos Similares -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Importante:</strong> Si ya existe este medicamento con diferente fecha de vencimiento, 
                        se creará un nuevo lote con un número único.
                    </div>
                    
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn" type="submit">
                            <i class="fas fa-save"></i> Guardar Lote
                        </button>
                        <a href="{{route('purchases.index')}}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
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
    
    <script>
        // Generar sugerencia de número de lote basado en el producto
        document.querySelector('input[name="product"]').addEventListener('blur', function() {
            const batchInput = document.querySelector('input[name="batch_number"]');
            if (!batchInput.value) {
                const productName = this.value.toUpperCase().replace(/\s+/g, '');
                const date = new Date();
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                
                if (productName) {
                    batchInput.placeholder = `Sugerencia: ${productName.substring(0, 3)}-${year}${month}${day}`;
                }
            }
        });
    </script>
@endpush