
@extends('admin.layouts.app')

@push('page-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .customer-details {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 15px;
            display: none;
        }
        .customer-details.active {
            display: block;
        }
        .product-row {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }
        .remove-product {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 5px 10px;
            cursor: pointer;
        }
        .add-product {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 8px 15px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .total-section {
            background: #e9ecef;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Registrar Venta</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item active">Registrar Venta</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body custom-edit-service">
                <!-- Create Sale -->
                <form method="POST" action="{{route('sales.store')}}" id="sale-form">
                    @csrf
                    
                    <!-- Customer Selection -->
                    <div class="row form-row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>Cliente <span class="text-danger">*</span></label>
                                <select class="select2-customer form-select form-control" name="customer_id" id="customer-select" required>
                                    <option value="" disabled selected>Seleccione Cliente</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{$customer->id}}" 
                                            data-name="{{$customer->name}}"
                                            data-email="{{$customer->email}}"
                                            data-phone="{{$customer->phone}}"
                                            data-company="{{$customer->company}}"
                                            data-address="{{$customer->address}}">
                                            {{$customer->name}} ({{$customer->company}})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                                <!-- Ubicación -->
        <div class="form-group">
            <label>Ubicación <span class="text-danger">*</span></label>
            <select class="form-control" name="ubicacion" required>
                <option value="" disabled selected>Seleccione Ubicación</option>
                <option value="cajibio">Cajibío</option>
                <option value="piendamo">Piendamó</option>
                <option value="morales">Morales</option>
                <option value="administrativo">Administrativo</option>
            </select>
        </div>
        
                            
                            <!-- Customer Details Card -->
                            <div class="customer-details" id="customer-details">
                                <h5>Información del Cliente</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nombre:</strong> <span id="customer-name"></span></p>
                                        <p><strong>Correo:</strong> <span id="customer-email"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Teléfono:</strong> <span id="customer-phone"></span></p>
                                        <p><strong>Empresa:</strong> <span id="customer-company"></span></p>
                                    </div>
                                </div>
                                <p><strong>Dirección:</strong> <span id="customer-address"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Products Section -->
                    <div class="row">
                        <div class="col-12">
                            <h4>Productos</h4>
                            <button type="button" class="add-product" onclick="addProductRow()">+ Agregar Producto</button>
                            
                            <div id="products-container">
                                <!-- Initial product row -->
                                <div class="product-row" id="product-row-0">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Producto <span class="text-danger">*</span></label>
                                                <select class="select2 form-select form-control product-select" name="products[0][product_id]" required>
                                                    <option value="" disabled selected>Seleccione Producto</option>
                                                    @foreach ($products as $product)
                                                        @if (!empty($product->purchase))
                                                            @if (!($product->purchase->quantity <= 0))
                                                                <option value="{{$product->id}}" data-price="{{$product->price}}" data-available="{{$product->purchase->quantity}}">
                                                                     {{$product->purchase->product}} (Disponible: {{$product->available_stock}}) 
                                                                </option>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Cantidad <span class="text-danger">*</span></label>
                                                <input type="number" value="1" class="form-control quantity-input" name="products[0][quantity]" min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Precio Unitario</label>
                                                <input type="text" class="form-control unit-price" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Subtotal</label>
                                                <input type="text" class="form-control subtotal" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="button" class="remove-product form-control" onclick="removeProductRow(0)" style="display: none;">×</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Section -->
                            <div class="total-section">
                                <div class="row">
                                    <div class="col-md-8"></div>
                                    <div class="col-md-4">
                                        <h4>Total: $<span id="grand-total">0.00</span></h4>
                                        <input type="hidden" name="total_price" id="total-price-input">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary btn-lg">Registrar Venta</button>
                    </div>
                </form>
                <!--/ Create Sale -->
            </div>
        </div>
    </div>            
</div>
@endsection    

@push('page-js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
         let productRowIndex = 1;

    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2();
        $('.select2-customer').select2();
        
        // Customer selection change event
        $('#customer-select').change(function() {
            var selectedOption = $(this).find('option:selected');
            
            // Update customer details
            $('#customer-name').text(selectedOption.data('name'));
            $('#customer-email').text(selectedOption.data('email'));
            $('#customer-phone').text(selectedOption.data('phone'));
            $('#customer-company').text(selectedOption.data('company'));
            $('#customer-address').text(selectedOption.data('address'));
            
            // Show customer details card
            $('#customer-details').addClass('active');
        });
        
        // Validación del formulario antes de enviar
        $('#sale-form').submit(function(e) {
            // Validar ubicación
            if ($('#ubicacion-select').val() === null || $('#ubicacion-select').val() === '') {
                alert('Por favor seleccione una ubicación');
                e.preventDefault();
                return false;
            }
            
            // Validar al menos un producto
            if ($('.product-row').length === 0) {
                alert('Debe agregar al menos un producto');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
        
        // Bind events to initial row
        bindProductEvents();
    });

        function addProductRow() {
    const productOptions = `@foreach ($products as $product)
        @if (!empty($product->purchase))
            @if (!($product->purchase->quantity <= 0))
                <option value="{{$product->id}}" data-price="{{$product->price}}" data-available="{{$product->purchase->quantity}}">
                    {{$product->purchase->product}} (Disponible: {{$product->available_stock}})
                </option>
            @endif
        @endif
    @endforeach`;

    const newRow = `
        <div class="product-row" id="product-row-${productRowIndex}">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Producto <span class="text-danger">*</span></label>
                        <select class="select2 form-select form-control product-select" name="products[${productRowIndex}][product_id]" required>
                            <option value="" disabled selected>Seleccione Producto</option>
                            ${productOptions}
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Cantidad <span class="text-danger">*</span></label>
                        <input type="number" value="1" class="form-control quantity-input" name="products[${productRowIndex}][quantity]" min="1" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Precio Unitario</label>
                        <input type="text" class="form-control unit-price" readonly>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Subtotal</label>
                        <input type="text" class="form-control subtotal" readonly>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="remove-product form-control" onclick="removeProductRow(${productRowIndex})">×</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('#products-container').append(newRow);
    
    // Initialize Select2 for new row
    $(`#product-row-${productRowIndex} .select2`).select2();
    
    // Show remove button for all rows if more than one
    if ($('.product-row').length > 1) {
        $('.remove-product').show();
    }
    
    // Bind events to new row
    bindProductEvents();
    
    productRowIndex++;
}

        function removeProductRow(index) {
            $(`#product-row-${index}`).remove();
            
            // Hide remove buttons if only one row left
            if ($('.product-row').length <= 1) {
                $('.remove-product').hide();
            }
            
            // Recalculate total
            calculateGrandTotal();
        }

        function bindProductEvents() {
            // Product selection change event
            $('.product-select').off('change').on('change', function() {
                const row = $(this).closest('.product-row');
                const selectedOption = $(this).find('option:selected');
                const price = selectedOption.data('price');
                const available = selectedOption.data('available');
                
                row.find('.unit-price').val(price);
                row.find('.quantity-input').attr('max', available);
                
                calculateRowSubtotal(row);
            });
            
            // Quantity change event
            $('.quantity-input').off('input').on('input', function() {
                const row = $(this).closest('.product-row');
                const max = parseInt($(this).attr('max'));
                const current = parseInt($(this).val());
                
                if (current > max) {
                    alert(`Solo hay ${max} unidades disponibles de este producto`);
                    $(this).val(max);
                }
                
                calculateRowSubtotal(row);
            });
        }

        function calculateRowSubtotal(row) {
            const quantity = parseInt(row.find('.quantity-input').val()) || 0;
            const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
            const subtotal = quantity * unitPrice;
            
            row.find('.subtotal').val(subtotal.toFixed(2));
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let grandTotal = 0;
            
            $('.subtotal').each(function() {
                const subtotal = parseFloat($(this).val()) || 0;
                grandTotal += subtotal;
            });
            
            $('#grand-total').text(grandTotal.toFixed(2));
            $('#total-price-input').val(grandTotal.toFixed(2));
        }
    </script>
@endpush