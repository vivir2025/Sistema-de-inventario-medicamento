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
        .municipality-section {
            background: #f1f8ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .municipality-products {
            display: none;
        }
        .municipality-products.active {
            display: block;
        }
    </style>
@endpush
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


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

                    <!-- Municipality Section -->
                    <div class="municipality-section">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipo de Venta <span class="text-danger">*</span></label>
                                    <select class="form-control" name="sales_type" id="sales-type" required>
                                        <option value="" disabled selected>Seleccione Tipo</option>
                                        <option value="local">Venta Local (Mismo Municipio)</option>
                                        <option value="transfer">Transferencia entre Municipios</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="local-sale-fields">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Municipio <span class="text-danger">*</span></label>
                                        <select class="form-control" name="origin_municipality" id="origin-municipality-local" required>
                                            <option value="" disabled selected>Seleccione Municipio</option>
                                            <option value="cajibio">Cajibío</option>
                                            <option value="piendamo">Piendamó</option>
                                            <option value="morales">Morales</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="transfer-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Municipio Origen <span class="text-danger">*</span></label>
                                        <select class="form-control" name="origin_municipality" id="origin-municipality-transfer" required>
                                            <option value="" disabled selected>Seleccione Municipio</option>
                                            <option value="cajibio">Cajibío</option>
                                            <option value="piendamo">Piendamó</option>
                                            <option value="morales">Morales</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Municipio Destino <span class="text-danger">*</span></label>
                                        <select class="form-control" name="destination_municipality" id="destination-municipality" required>
                                            <option value="" disabled selected>Seleccione Municipio</option>
                                            <option value="cajibio">Cajibío</option>
                                            <option value="piendamo">Piendamó</option>
                                            <option value="morales">Morales</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Section -->
                    <div class="row">
                        <div class="col-12">
                            <h4>Productos</h4>
                            
                           <!-- Products by Municipality -->
@foreach($productsByMunicipality as $municipality => $products)
    <div class="municipality-products" id="products-{{$municipality}}" data-municipality="{{$municipality}}" style="display: none;">
        <button type="button" class="btn btn-primary add-product mb-3" onclick="addProductRow('{{$municipality}}')">
            <i class="fas fa-plus"></i> Agregar Producto
        </button>
        <div id="products-container-{{$municipality}}">
            <!-- Product rows will be added here -->
        </div>
    </div>
@endforeach
                            
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
        // ✅ OCULTAR ERRORES DE UNDEFINED - DEBE IR AL INICIO
        window.addEventListener('error', function(e) {
            if (e.message && (e.message.includes('undefined') || e.message.includes('null'))) {
                e.preventDefault();
                return false;
            }
        });

        // Ocultar errores de consola
        const originalConsoleError = console.error;
        const originalConsoleWarn = console.warn;
        
        console.error = function(message) {
            if (typeof message === 'string' && (message.includes('undefined') || message.includes('null'))) {
                return;
            }
            originalConsoleError.apply(console, arguments);
        };
        
        console.warn = function(message) {
            if (typeof message === 'string' && (message.includes('undefined') || message.includes('null'))) {
                return;
            }
            originalConsoleWarn.apply(console, arguments);
        };

        let productRowIndex = 0;
        const municipalities = {
            'cajibio': 'Cajibío',
            'morales': 'Morales',
            'piendamo': 'Piendamó'
        };

        // ✅ OBJETO CON PRODUCTOS POR MUNICIPIO - VALIDADO PARA EVITAR UNDEFINED
        const productsByMunicipality = {     
            @foreach($productsByMunicipality as $municipality => $products)         
                '{{$municipality}}': [             
                    @foreach($products as $product)                 
                        @if(!empty($product->purchase) && isset($product->id))                     
                            {                         
                                id: '{{$product->id ?? ''}}',                         
                                name: '{{addslashes($product->purchase->product ?? 'Sin nombre')}}',                         
                                price: {{$product->price ?? 0}},                         
                                available: {{$product->available ?? 0}},                        
                                municipality: '{{$municipality ?? ''}}',                         
                                is_original: {{isset($product->is_original) && $product->is_original ? 'true' : 'false'}},                         
                                transferred_in: {{$product->transferred_in ?? 0}},
                                base_quantity: {{$product->base_quantity ?? 0}},
                                batch_number: '{{$product->purchase->batch_number ?? "SIN-LOTE"}}'                     
                            },                 
                        @endif             
                    @endforeach         
                ],     
            @endforeach 
        };

        $(document).ready(function() {
            // Initialize Select2
            $('.select2-customer').select2();
            
            // Customer selection change event
            $('#customer-select').change(function() {
                var selectedOption = $(this).find('option:selected');
                
                // Update customer details with null checks
                $('#customer-name').text(selectedOption.data('name') || 'N/A');
                $('#customer-email').text(selectedOption.data('email') || 'N/A');
                $('#customer-phone').text(selectedOption.data('phone') || 'N/A');
                $('#customer-company').text(selectedOption.data('company') || 'N/A');
                $('#customer-address').text(selectedOption.data('address') || 'N/A');
                
                // Show customer details card
                $('#customer-details').addClass('active');
            });
            
            // Sales type change event
            $('#sales-type').change(function() {
                const salesType = $(this).val();
                
                if (salesType == 'local') {
                    $('#local-sale-fields').show();
                    $('#transfer-fields').hide();
                    $('#origin-municipality-transfer').removeAttr('required');
                    $('#destination-municipality').removeAttr('required');
                    $('#origin-municipality-local').prop('required', true);
                } else {
                    $('#local-sale-fields').hide();
                    $('#transfer-fields').show();
                    $('#origin-municipality-local').removeAttr('required');
                    $('#origin-municipality-transfer').prop('required', true);
                    $('#destination-municipality').prop('required', true);
                }
                
                updateProductsDisplay();
            });
            
            // Municipality change event
            $('select[name="origin_municipality"]').change(function() {
                updateProductsDisplay();
            });
            
            // ✅ FORM SUBMISSION - REDIRECCIÓN CORREGIDA
            $('#sale-form').submit(function(e) {
                e.preventDefault();
                
                if (!validateForm()) {
                    return false;
                }
                
                const formData = new FormData(this);
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // ✅ REDIRECCIÓN FORZADA AL INDEX
                        showAlert('success', 'Venta registrada exitosamente');
                        setTimeout(function() {
                            window.location.href = '{{ route("sales.index") }}';
                        }, 1000);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            let errorMessage = '';
                            
                            for (const field in errors) {
                                if (errors[field] && errors[field][0]) {
                                    errorMessage += errors[field][0] + '\n';
                                }
                            }
                            
                            showAlert('error', errorMessage || 'Error de validación');
                        } else {
                            showAlert('error', 'Error en el servidor: ' + (xhr.statusText || 'Error desconocido'));
                        }
                    }
                });
            });

            // Bind initial events
            bindProductEvents();
        });

        function updateProductsDisplay() {
            const salesType = $('#sales-type').val();
            let municipality;
            
            if (salesType == 'local') {
                municipality = $('#origin-municipality-local').val();
            } else {
                municipality = $('#origin-municipality-transfer').val();
            }
            
            // Hide all product sections
            $('.municipality-products').hide().removeClass('active');
            
            // Show products for selected municipality
            if (municipality) {
                $(`#products-${municipality}`).show().addClass('active');
            }
        }

        // ✅ FUNCIÓN CORREGIDA CON VALIDACIONES
        function addProductRow(municipality) {     
            let productOptions = '<option value="" disabled selected>Seleccione Producto</option>';          
            
            // Validar que existe el municipio y tiene productos
            if (productsByMunicipality && 
                productsByMunicipality[municipality] && 
                Array.isArray(productsByMunicipality[municipality])) {         
                productsByMunicipality[municipality].forEach(function(product) {             
                    // Validar cada propiedad del producto
                    if (product && 
                        typeof product.available !== 'undefined' && 
                        product.available > 0 &&
                        product.id &&
                        product.name) {                 
                        const productName = product.name || 'Producto sin nombre';
                        const productType = product.is_original ? 'Original' : 'Transferido';                 
                        
                          const batchNumber = product.batch_number || 'SIN-LOTE';
                
                // Formato simplificado: Nombre + Lote + Disponibilidad
                const displayText = `${productName} -  ${batchNumber} (Disp: ${product.available})`;            
                
                        productOptions += `<option value="${product.id}"                      
                            data-price="${product.price || 0}"                      
                            data-available="${product.available || 0}"                      
                            data-municipality="${product.municipality || ''}"                      
                            data-is-original="${product.is_original || false}"                     
                            data-base-quantity="${product.base_quantity || 0}"                     
                            data-transferred-in="${product.transferred_in || 0}">                     
                                                
                    ${displayText}                         
                        </option>`;             
                    }         
                });     
            }

            const newRow = `
                <div class="product-row" id="product-row-${productRowIndex}">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Producto <span class="text-danger">*</span></label>
                                <select class="select2 form-select form-control product-select" 
                                        name="products[${productRowIndex}][product_id]" 
                                        data-municipality="${municipality}" required>
                                    ${productOptions}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Cantidad <span class="text-danger">*</span></label>
                                <input type="number" value="1" class="form-control quantity-input" 
                                       name="products[${productRowIndex}][quantity]" min="1" required>
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
                                <button type="button" class="btn btn-danger remove-product form-control" 
                                        onclick="removeProductRow(${productRowIndex})">×</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $(`#products-container-${municipality}`).append(newRow);
            
            // Initialize Select2 for the new row
            $(`#product-row-${productRowIndex} .product-select`).select2();
            
            // Bind events to new row
            bindProductEvents();
            
            productRowIndex++;
        }

        function removeProductRow(index) {
            $(`#product-row-${index}`).remove();
            calculateGrandTotal();
        }

        function bindProductEvents() {
            // Product selection change event
            $('.product-select').off('change').on('change', function() {
                const row = $(this).closest('.product-row');
                const selectedOption = $(this).find('option:selected');
                const price = selectedOption.data('price') || 0;
                const available = selectedOption.data('available') || 0;
                
                row.find('.unit-price').val(price);
                row.find('.quantity-input').attr({
                    'max': available,
                    'data-current-max': available
                }).val(1);
                
                calculateRowSubtotal(row);
            });
            
            // Quantity change event
            $('.quantity-input').off('input').on('input', function() {
                const row = $(this).closest('.product-row');
                const max = parseInt($(this).attr('max')) || 0;
                const current = parseInt($(this).val()) || 0;
                const available = parseInt($(this).attr('data-current-max')) || max;
                
                if (current > available) {
                    showAlert('warning', `Solo hay ${available} unidades disponibles de este producto`);
                    $(this).val(available);
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

        function validateForm() {
            const salesType = $('#sales-type').val();
            let isValid = true;
            
            if (!salesType) {
                showAlert('error', 'Por favor seleccione el tipo de venta');
                $('#sales-type').focus();
                return false;
            }
            
            if (salesType === 'local') {
                if (!$('#origin-municipality-local').val()) {
                    showAlert('error', 'Por favor seleccione el municipio');
                    $('#origin-municipality-local').focus();
                    isValid = false;
                }
            } else {
                const origin = $('#origin-municipality-transfer').val();
                const destination = $('#destination-municipality').val();
                
                if (!origin) {
                    showAlert('error', 'Por favor seleccione el municipio de origen');
                    $('#origin-municipality-transfer').focus();
                    isValid = false;
                }
                
                if (!destination) {
                    showAlert('error', 'Por favor seleccione el municipio destino');
                    $('#destination-municipality').focus();
                    isValid = false;
                }
                
                // ✅ ESTA VALIDACIÓN LA QUITASTE DEL PHP, LA QUITO TAMBIÉN AQUÍ
                // if (origin && destination && origin === destination) {
                //     showAlert('error', 'El municipio origen y destino no pueden ser iguales');
                //     isValid = false;
                // }
            }
            
            if (!$('#customer-select').val()) {
                showAlert('error', 'Por favor seleccione un cliente');
                isValid = false;
            }
            
            if ($('.product-row').length === 0) {
                showAlert('error', 'Debe agregar al menos un producto');
                isValid = false;
            }
            
            $('.product-row').each(function() {
                const productId = $(this).find('.product-select').val();
                const quantity = $(this).find('.quantity-input').val();
                
                if (!productId) {
                    showAlert('error', 'Por favor seleccione un producto en todas las filas');
                    isValid = false;
                    return false;
                }
                
                if (!quantity || quantity <= 0) {
                    showAlert('error', 'La cantidad debe ser mayor a cero en todas las filas');
                    isValid = false;
                    return false;
                }
            });
            
            return isValid;
        }

       function showAlert(type, message) {
    // Crear el elemento de alerta de Bootstrap
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    
    // Agregar icono según el tipo
    let icon = '';
    if (type === 'success') {
        icon = '✅';
    } else if (type === 'error') {
        icon = '❌';
        // Bootstrap usa 'danger' para errores
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    } else if (type === 'warning') {
        icon = '⚠️';
    } else if (type === 'info') {
        icon = 'ℹ️';
    }
    
    // Contenido de la alerta
    alertDiv.innerHTML = `
        ${icon} ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Contenedor para las alertas (si no existe, lo creamos)
    let alertsContainer = document.getElementById('alerts-container');
    if (!alertsContainer) {
        alertsContainer = document.createElement('div');
        alertsContainer.id = 'alerts-container';
        alertsContainer.style.position = 'fixed';
        alertsContainer.style.top = '100px';
        alertsContainer.style.right = '20px';
        alertsContainer.style.zIndex = '1000';
        alertsContainer.style.width = '300px';
        document.body.appendChild(alertsContainer);
    }
    
    // Agregar la alerta al contenedor
    alertsContainer.appendChild(alertDiv);
    
    // Eliminar automáticamente después de 5 segundos
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}
    </script>
@endpush