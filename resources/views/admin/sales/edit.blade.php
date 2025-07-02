@extends('admin.layouts.app')

@push('page-css')
    <style>
        .group-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .group-sales-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            background: white;
        }
        .sale-item {
            padding: 8px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sale-item:last-child {
            border-bottom: none;
        }
        .sale-item.current {
            background: #e3f2fd;
            font-weight: bold;
        }
        .sale-actions {
            display: flex;
            gap: 5px;
        }
        .product-batch {
            font-size: 0.85em;
            color: #6c757d;
        }
        .municipality-products {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
        }
        .product-option {
            padding: 8px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }
        .product-option:hover {
            background-color: #f8f9fa;
        }
        .product-option.selected {
            background-color: #e3f2fd;
        }
    </style>
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Editar Venta</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{route('sales.index')}}">Ventas</a></li>
        <li class="breadcrumb-item active">Editar Venta</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <!-- Información del Grupo -->
        <div class="group-info">
            <h5><i class="fas fa-shopping-cart"></i> Información del Grupo de Ventas</h5>
            <div class="row">
                <div class="col-md-3">
                    <strong>Cliente:</strong> {{ $sale->customer->name }}
                </div>
                <div class="col-md-3">
                    <strong>Total de Ítems:</strong> {{ $groupSales->sum('quantity') }}
                </div>
                <div class="col-md-3">
                    <strong>Total:</strong> {{ settings('app_currency','$') }} {{ number_format($groupSales->sum('total_price'), 2) }}
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Ventas en este Grupo -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Ventas en este Grupo</h5>
                    </div>
                    <div class="card-body">
                        <div class="group-sales-list">
                            @foreach($groupSales as $groupSale)
                                <div class="sale-item {{ $groupSale->id == $sale->id ? 'current' : '' }}">
                                    <div>
                                        <strong>{{ $groupSale->product->purchase->product ?? 'N/A' }}</strong><br>
                                        <small>Cant: {{ $groupSale->quantity }} | Precio: {{ settings('app_currency','$') }}{{ number_format($groupSale->total_price, 2) }}</small>
                                        @if($groupSale->product->purchase->batch_number)
                                            <br><small class="product-batch">Lote: {{ $groupSale->product->purchase->batch_number }}</small>
                                        @endif
                                    </div>
                                    <div class="sale-actions">
                                        @if($groupSale->id != $sale->id)
                                            <a href="{{ route('sales.edit', $groupSale->id) }}" class="btn btn-sm btn-outline-primary" title="Editar esta venta">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @else
                                            <span class="badge badge-primary">Actual</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Editar Venta Actual -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-edit"></i> Editar Venta Actual (ID: {{ $sale->id }})</h5>
                    </div>
                    <div class="card-body custom-edit-service">
                        <form method="POST" action="{{route('sales.update',$sale)}}">
                            @csrf
                            @method("PUT")
                            
                            <!-- Campo oculto para mantener el contexto del grupo -->
                            <input type="hidden" name="sale_group_id" value="{{ $sale->sale_group_id }}">
                            
                            <div class="row form-row">
                                <!-- Municipio de Origen -->
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Municipio de Origen <span class="text-danger">*</span></label>
                                        <select class="form-control" name="origin_municipality" id="origin_municipality" required>
                                            <option value="">Seleccionar municipio de origen</option>
                                            @foreach($municipalities as $key => $name)
                                                <option value="{{ $key }}" {{ ($sale->origin_municipality ?? old('origin_municipality')) == $key ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Municipio de Destino -->
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Municipio de Destino <span class="text-danger">*</span></label>
                                        <select class="form-control" name="destination_municipality" id="destination_municipality" required>
                                            <option value="">Seleccionar municipio de destino</option>
                                            @foreach($municipalities as $key => $name)
                                                <option value="{{ $key }}" {{ ($sale->destination_municipality ?? old('destination_municipality')) == $key ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Selector de Producto -->
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Producto <span class="text-danger">*</span></label>
                                        <input type="hidden" name="product" id="selected_product" value="{{ $sale->product_id }}">
                                        <div id="products_container">
                                            <!-- Los productos se cargarán dinámicamente aquí -->
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Cliente <span class="text-danger">*</span></label>
                                        <select class="select2 form-select form-control" name="customer_id" required>
                                            @foreach ($customers as $customer)
                                                <option {{$customer->id == $sale->customer_id ? 'selected' : ''}} value="{{$customer->id}}">
                                                    {{$customer->name}} ({{$customer->company}})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Cantidad <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control edit_quantity" 
                                               value="{{$sale->quantity ?? '1'}}" 
                                               name="quantity" 
                                               min="1" 
                                               max="100"
                                               required>
                                        <small class="text-muted">La cantidad disponible se calculará automáticamente</small>
                                    </div>
                                </div>
                                
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Precio Unitario</label>
                                        <input type="text" class="form-control" id="unit_price" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Precio Total</label>
                                        <input type="text" class="form-control" id="total_price" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                                <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver a Ventas
                                </a>
                                @if($groupSales->count() > 1)
                                    <button type="button" class="btn btn-danger" onclick="confirmDeleteSale()">
                                        <i class="fas fa-trash"></i> Eliminar Solo Esta Venta
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>            
</div>
@endsection    

@push('page-js')
<script>
$(document).ready(function() {
    // Datos de productos por municipio desde PHP
    const productsByMunicipality = @json($productsByMunicipality);
    const currentProductId = {{ $sale->product_id }};
    const currentSaleId = {{ $sale->id }};
    
    // Cargar productos cuando cambie el municipio de origen
    $('#origin_municipality').on('change', function() {
        loadProductsForMunicipality();
    });
    
    function loadProductsForMunicipality() {
        const selectedMunicipality = $('#origin_municipality').val();
        const productsContainer = $('#products_container');
        
        if (!selectedMunicipality) {
            productsContainer.html('<p class="text-muted">Selecciona un municipio de origen para ver los productos disponibles.</p>');
            return;
        }
        
        const products = productsByMunicipality[selectedMunicipality] || [];
        
        if (products.length === 0) {
            productsContainer.html('<p class="text-warning">No hay productos disponibles en este municipio.</p>');
            return;
        }
        
        let html = '<div class="municipality-products"><h6>Productos disponibles en ' + selectedMunicipality + ':</h6>';
        
        products.forEach(function(product) {
            // Ajustar disponibilidad si es el producto actual
            let availableQty = product.available;
            if (product.id == currentProductId) {
                availableQty = product.available + {{ $sale->quantity }};
            }
            
            const isSelected = product.id == currentProductId ? 'selected' : '';
            const productType = product.is_original ? 'Original' : 'Transferido';
            
            html += `<div class="product-option ${isSelected}" data-product-id="${product.id}" data-price="${product.price}" data-available="${availableQty}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${product.purchase.product}</strong>
                        <br><small class="text-muted">Tipo: ${productType} | Lote: ${product.batch_number}</small>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-success">Disponible: ${availableQty}</span>
                        <br><small>$${parseFloat(product.price).toFixed(2)}</small>
                    </div>
                </div>
            </div>`;
        });
        
        html += '</div>';
        productsContainer.html(html);
        
        // Configurar eventos de click para seleccionar producto
        $('.product-option').on('click', function() {
            $('.product-option').removeClass('selected');
            $(this).addClass('selected');
            
            const productId = $(this).data('product-id');
            const price = $(this).data('price');
            const available = $(this).data('available');
            
            $('#selected_product').val(productId);
            $('.edit_quantity').attr('max', available);
            
            calculateTotal();
        });
    }
    
    // Calcular precio total
    function calculateTotal() {
        const quantity = $('.edit_quantity').val();
        const selectedOption = $('.product-option.selected');
        const price = selectedOption.data('price');
        
        if (quantity && price) {
            const total = quantity * price;
            $('#unit_price').val('{{ settings("app_currency","$") }}' + parseFloat(price).toFixed(2));
            $('#total_price').val('{{ settings("app_currency","$") }}' + total.toFixed(2));
        }
    }
    
    // Validar cantidad contra stock disponible
    function validateQuantity() {
        const quantity = parseInt($('.edit_quantity').val());
        const selectedOption = $('.product-option.selected');
        const available = parseInt(selectedOption.data('available'));
        
        if (quantity > available) {
            $('.edit_quantity').val(available);
            alert('La cantidad no puede exceder el stock disponible (' + available + ')');
        }
    }
    
    // Eventos
    $('.edit_quantity').on('input', function() {
        validateQuantity();
        calculateTotal();
    });
    
    // Inicializar con el municipio actual si existe
    if ($('#origin_municipality').val()) {
        loadProductsForMunicipality();
    }
});

// Función para eliminar venta
function confirmDeleteSale() {
    if (confirm('¿Estás seguro de que quieres eliminar esta venta?\n\nEsta acción eliminará solo este producto de la compra y restaurará el inventario.')) {
        deleteSaleAjax();
    }
}

function deleteSaleAjax() {
    $('body').append('<div id="loading-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;color:white;font-size:18px;"><div><i class="fas fa-spinner fa-spin"></i> Eliminando...</div></div>');

    $.ajax({
        url: '{{ route("sales.destroy") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            id: {{ $sale->id }}
        },
        success: function(response) {
            $('#loading-overlay').remove();
            alert('¡Venta eliminada correctamente!');
            window.location.href = '{{ route("sales.index") }}';
        },
        error: function(xhr, status, error) {
            $('#loading-overlay').remove();
            console.error('Error:', error);
            alert('Error al eliminar la venta: ' + (xhr.responseJSON?.message || 'Error desconocido'));
        }
    });
}
</script>
@endpush