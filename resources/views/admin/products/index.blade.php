@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    <style>
        .expiry-green {
            background-color: #155724 !important;
            color: #ffffff;
        }
        .expiry-yellow {
            background-color: #ffeb3b !important;
            color: #6b6b6b;
        }
        .expiry-orange {
            background-color: #ff9800 !important;
            color: #ffffff;
        }
        .expiry-red {
            background-color: #f8d7da !important;
            color: #721c24;
        }
        .municipality-filter {
            margin-bottom: 20px;
        }
        .stats-card {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        /* Estilo para campos de información adicional */
        .info-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin: 0.125rem;
            display: inline-block;
        }
        .riesgo-alto { background-color: #dc3545; color: white; }
        .riesgo-medio { background-color: #ffc107; color: black; }
        .riesgo-bajo { background-color: #28a745; color: white; }
    </style>
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">
        Productos
        @if($municipality !== 'all')
            - {{ ucfirst($municipality) }}
        @endif
    </h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item active">Productos</li>
    </ul>
</div>
<div class="col-sm-5 col">
    <a href="{{route('products.create')}}" class="btn btn-primary float-right mt-2">Agregar Producto</a>
</div>
@endpush

@section('content')
<!-- Estadísticas por municipio -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card text-center">
            <h4>{{$stats['cajibio']}}</h4>
            <p class="mb-0">Cajibío</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card text-center" style="background: linear-gradient(45deg, #28a745, #1e7e34);">
            <h4>{{$stats['morales']}}</h4>
            <p class="mb-0">Morales</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card text-center" style="background: linear-gradient(45deg, #ffc107, #d39e00);">
            <h4>{{$stats['piendamo']}}</h4>
            <p class="mb-0">Piendamó</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card text-center" style="background: linear-gradient(45deg, #6c757d, #495057);">
            <h4>{{$stats['total']}}</h4>
            <p class="mb-0">Total</p>
        </div>
    </div>
</div>

<!-- Filtros por municipio -->
<div class="row">
    <div class="col-md-12">
        <div class="municipality-filter">
            <div class="btn-group" role="group">
                <a href="{{route('products.index')}}" 
                   class="btn {{$municipality === 'all' ? 'btn-primary' : 'btn-outline-primary'}}">
                    Todos
                </a>
                <a href="{{route('products.index', ['municipality' => 'cajibio'])}}" 
                   class="btn {{$municipality === 'cajibio' ? 'btn-info' : 'btn-outline-info'}}">
                    Cajibío
                </a>
                <a href="{{route('products.index', ['municipality' => 'morales'])}}" 
                   class="btn {{$municipality === 'morales' ? 'btn-success' : 'btn-outline-success'}}">
                    Morales
                </a>
                <a href="{{route('products.index', ['municipality' => 'piendamo'])}}" 
                   class="btn {{$municipality === 'piendamo' ? 'btn-warning' : 'btn-outline-warning'}}">
                    Piendamó
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- Productos -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="product-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Lote</th>
                                <th>P. Act./D. Med/Insumo</th>
                                <th>Marca</th>
                                <th>Municipio</th>
                                <th>Categoría</th>
                                <th>Fecha de Vencimiento</th>
                                <th>Serie</th>
                                <th>Riesgo</th>
                                <th>Vida Útil</th>
                                <th>Registro Sanitario</th>
                                <th>Presentación</th>
                                <th>Forma Farmacéutica</th>
                                <th>Concentración</th>
                                <th>Unidad de Medida</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Descuento</th>
                                
                                <th class="action-btn">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargarán vía DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /Productos -->
    </div>
</div>
@endsection

@push('page-js')
<script>
$(document).ready(function() {
    var table = $('#product-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('products.index')}}",
            data: {
                municipality: "{{$municipality}}"
            },
            type: 'GET',
            error: function(xhr, error, code) {
                console.log('Error:', xhr.responseText);
            }
        },
        columns: [
            {
                data: 'batch_number',
                name: 'batch_number',
                orderable: true,
                searchable: true,
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            {
                data: 'product',
                name: 'product',
                orderable: true,
                searchable: true
            },
            {
                data: 'marca',
                name: 'marca',
                orderable: true,
                searchable: true,
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            {
                data: 'municipality',
                name: 'municipality',
                orderable: true,
                searchable: true
            },
            {
                data: 'category',
                name: 'category',
                orderable: true,
                searchable: true
            },
             {
                data: 'expiry_date', 
                name: 'expiry_date',
                orderable: true,
                searchable: true,
                render: function(data, type) {
                    if (type === 'display' && data && data !== 'N/A') {
                        var dateMatch = data.match(/\d{2} \w{3}, \d{4}/);
                        if (dateMatch) {
                            var dateStr = dateMatch[0];
                            var expiryDate = new Date(dateStr);
                            var today = new Date();
                            
                            // Calcular fechas límite
                            var threeMonthsFromNow = new Date();
                            threeMonthsFromNow.setMonth(threeMonthsFromNow.getMonth() + 3);
                            
                            var sixMonthsFromNow = new Date();
                            sixMonthsFromNow.setMonth(sixMonthsFromNow.getMonth() + 6);
                            
                            var twelveMonthsFromNow = new Date();
                            twelveMonthsFromNow.setMonth(twelveMonthsFromNow.getMonth() + 12);
                            
                            var colorClass = '';
                            
                            // Nueva lógica de semaforización
                            if (expiryDate < today || expiryDate <= threeMonthsFromNow) {
                                // ROJO: Menos de 3 meses o caducado
                                colorClass = 'expiry-red';
                            } else if (expiryDate <= sixMonthsFromNow) {
                                // NARANJA: Menos de 6 meses
                                colorClass = 'expiry-orange';
                            } else if (expiryDate <= twelveMonthsFromNow) {
                                // AMARILLO: De 6 a 12 meses
                                colorClass = 'expiry-yellow';
                            } else {
                                // VERDE: Más de 12 meses
                                colorClass = 'expiry-green';
                            }
                            
                            return '<span class="' + colorClass + ' p-2 rounded">' + data + '</span>';
                        }
                    }
                    return data;
                }
            },
            {
                data: 'serie',
                name: 'serie',
                orderable: true,
                searchable: true,
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            {
                data: 'riesgo',
                name: 'riesgo',
                orderable: true,
                searchable: true,
                render: function(data, type, row) {
                    if (!data || data === 'N/A') return 'N/A';
                    
                    var riesgoClass = '';
                    var riesgoLower = data.toLowerCase();
                    
                    if (riesgoLower.includes('alto')) {
                        riesgoClass = 'riesgo-alto';
                    } else if (riesgoLower.includes('medio')) {
                        riesgoClass = 'riesgo-medio';
                    } else if (riesgoLower.includes('bajo')) {
                        riesgoClass = 'riesgo-bajo';
                    }
                    
                    return '<span class="info-badge ' + riesgoClass + '">' + data + '</span>';
                }
            },
            {
                data: 'vida_util',
                name: 'vida_util',
                orderable: true,
                searchable: true,
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            {
                data: 'registro_sanitario',
                name: 'registro_sanitario',
                orderable: true,
                searchable: true,
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            {
                data: 'presentacion_comercial',
                name: 'presentacion_comercial',
                orderable: true,
                searchable: true,
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            {
                data: 'forma_farmaceutica',
                name: 'forma_farmaceutica',
                orderable: true,
                searchable: true,
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            {
                data: 'concentracion',
                name: 'concentracion',
                orderable: true,
                searchable: true,
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            {
                data: 'unidad_medida',
                name: 'unidad_medida',
                orderable: true,
                searchable: true,
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            {
                data: 'price',
                name: 'price',
                orderable: true,
                searchable: true
            },
            {
                data: 'quantity',
                name: 'quantity',
                orderable: true,
                searchable: false
            },
            {
                data: 'discount',
                name: 'discount',
                orderable: true,
                searchable: true
            },
           
            {
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: false
            },
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'print'
        ],
        pageLength: 25,
        responsive: true,
        stateSave: true,
        scrollX: true, // Agregar scroll horizontal para manejar las columnas adicionales
        drawCallback: function(settings) {
            console.log('DataTable redrawn');
        }
    });
});

// Código para eliminar
$(document).on('click', '.delete-btn', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var url = "{{ route('products.destroy', ':id') }}".replace(':id', id);
    
    Swal.fire({
        title: '¿Estás seguro?',
        html: "<strong>¡No podrás revertir esta acción!</strong>",
        type: 'warning', 
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        buttonsStyling: true,
        reverseButtons: true
    }).then((result) => {
        if (result.value) { 
            $.ajax({
                url: url,
                type: 'DELETE',
                dataType: 'json',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            html: response.message,
                            type: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#product-table').DataTable().draw(false);
                    } else {
                        Swal.fire({
                            title: 'Error',
                            html: response.message,
                            type: 'error'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error',
                        html: xhr.responseJSON?.message || 'Error al procesar la solicitud',
                        type: 'error'
                    });
                }
            });
        }
    });
});
</script>
@endpush