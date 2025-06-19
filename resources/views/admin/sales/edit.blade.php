
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
            <strong>Ubicación:</strong> {{ $ubicaciones[$sale->ubicacion] ?? $sale->ubicacion }}
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
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>Producto <span class="text-danger">*</span></label>
                                        <select class="select2 form-select form-control edit_product" name="product" required> 
                                            @foreach ($products as $product)
                                                @if (!empty($product->purchase))
                                                    @php
                                                        $availableQty = $product->purchase->quantity;
                                                        $isCurrentProduct = ($product->id == $sale->product_id);
                                                        // Si es el producto actual, sumar la cantidad que ya está vendida
                                                        if($isCurrentProduct) {
                                                            $availableQty += $sale->quantity;
                                                        }
                                                    @endphp
                                                    @if ($availableQty > 0)
                                                        <option {{ $isCurrentProduct ? 'selected': '' }} 
                                                                value="{{$product->id}}" 
                                                                data-available="{{$availableQty}}"
                                                                data-price="{{$product->price}}">
                                                            {{$product->purchase->product}} (Disponible: {{$availableQty}})
                                                        </option>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </select>
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
        <label>Ubicación <span class="text-danger">*</span></label>
        <select class="form-control" name="ubicacion" required>
            @foreach ($ubicaciones as $key => $value)
                <option value="{{ $key }}" {{ $sale->ubicacion == $key ? 'selected' : '' }}>
                    {{ $value }}
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

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="deleteSaleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar esta venta? Esta acción no se puede deshacer.</p>
                <p><strong>Nota:</strong> Esto solo eliminará el ítem actual de la venta, no todo el grupo.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="deleteSaleForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                   @if($groupSales->count() > 1)
                        <button type="button" class="btn btn-danger" onclick="confirmDeleteSale()">
                            <i class="fas fa-trash"></i> Eliminar Solo Esta Venta
                        </button>
                   @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection    

@push('page-js')
<script>
$(document).ready(function() {


      function validateForm() {
        // Validar ubicación
        if (!$('select[name="ubicacion"]').val()) {
            alert('Por favor seleccione una ubicación');
            return false;
        }
        return true;
    }
    // Calcular precio total cuando cambia cantidad o producto


    function calculateTotal() {
        var quantity = $('.edit_quantity').val();
        var selectedOption = $('.edit_product option:selected');
        var price = selectedOption.data('price');
        
        if (quantity && price) {
            var total = quantity * price;
            $('#unit_price').val('{{ settings("app_currency","$") }}' + parseFloat(price).toFixed(2));
            $('#total_price').val('{{ settings("app_currency","$") }}' + total.toFixed(2));
        }
    }
    
    // Validar cantidad contra stock disponible
    function validateQuantity() {
        var quantity = parseInt($('.edit_quantity').val());
        var selectedOption = $('.edit_product option:selected');
        var available = parseInt(selectedOption.data('available'));
        
        if (quantity > available) {
            $('.edit_quantity').val(available);
            alert('La cantidad no puede exceder el stock disponible (' + available + ')');
        }
    }
    
    // Eventos
    $('.edit_product').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var available = selectedOption.data('available');
        $('.edit_quantity').attr('max', available);
        calculateTotal();
    });
    
    $('.edit_quantity').on('input', function() {
        validateQuantity();
        calculateTotal();
    });
    
    // Inicializar cálculos
    calculateTotal();
});

// Confirmación nativa
function confirmDeleteSale() {
    if (confirm('¿Estás seguro de que quieres eliminar esta venta?\n\nEsta acción eliminará solo este producto de la compra y restaurará el inventario.')) {
        deleteSaleAjax();
    }
}

// Eliminar usando AJAX
function deleteSaleAjax() {
    // Mostrar loading simple
    var loadingBtn = $('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Eliminando...</div>');
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
            // Redirigir a la lista de ventas
            window.location.href = '{{ route("sales.index") }}';
        },
        error: function(xhr, status, error) {
            $('#loading-overlay').remove();
            console.error('Error:', error);
            alert('Error al eliminar la venta: ' + (xhr.responseJSON?.message || 'Error desconocido'));
        }
    });
}

// Alternativa con SweetAlert2
function confirmDeleteSaleSweet() {
    swal({
        title: '¿Eliminar esta venta?',
        text: 'Esta acción eliminará solo este producto de la compra y restaurará el inventario.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.value) {
            deleteSaleAjax();
        }
    });
}
</script>
@endpush