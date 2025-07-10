@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
<style>
    .batch-number {
        font-weight: bold;
        font-family: 'Courier New', monospace;
    }
    .badge-expired {
        background-color: #dc3545;
    }
    .badge-near-expiry {
        background-color: #ffc107;
        color: #000;
    }
    .badge-active {
        background-color: #28a745;
    }
    .stock-info {
        font-size: 0.9em;
    }
    .stock-critical { color: #dc3545; }
    .stock-low { color: #ffc107; }
    .stock-good { color: #28a745; }
    .table-responsive {
        overflow-x: auto;
    }
    .table th, .table td {
        white-space: nowrap;
        vertical-align: middle;
    }
    .table th {
        font-size: 0.85em;
        padding: 8px 6px;
    }
    .table td {
        font-size: 0.8em;
        padding: 6px 4px;
    }
    .btn-sm {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }
    /* Estilos para la nueva semaforización */
    .text-orange {
        color: #fd7e14 !important;
    }
</style>
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Compras</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item active">Compras</li>
    </ul>
</div>
<div class="col-sm-5 col">
    <a href="{{route('purchases.create')}}" class="btn btn-primary float-right mt-2">
        <i class="fas fa-plus"></i> Agregar Nueva
    </a>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        
        <!-- Filtros Rápidos -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="filter-all">
                                Todos
                            </button>
                            <button type="button" class="btn btn-outline-success" id="filter-active">
                                Activos
                            </button>
                            <button type="button" class="btn btn-outline-warning" id="filter-near-expiry">
                                Por Vencer
                            </button>
                            <button type="button" class="btn btn-outline-danger" id="filter-expired">
                                Vencidos
                            </button>
                            <button type="button" class="btn btn-outline-danger" id="filter-alto-riesgo">
                                Alto Riesgo
                            </button>
                            <button type="button" class="btn btn-outline-warning" id="filter-medio-riesgo">
                                Medio Riesgo
                            </button>
                            <button type="button" class="btn btn-outline-success" id="filter-bajo-riesgo">
                                Bajo Riesgo
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 text-right">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Los lotes se ordenan por fecha de vencimiento
                        </small>
                    </div>
                </div>
            </div>
        </div>

        
        <!-- Compras Recientes -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="purchase-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Lote</th>
                                <th>P. Act./D. Med/Insumo</th>
                                <th>Marca</th>
                                <th>Serie</th>
                                <th>Categoría</th>
                                <th>Riesgo</th>
                                <th>Concentración</th>
                                <th>Forma Farm.</th>
                                <th>Presentación</th>
                                <th>Unidad</th>
                                <th>Vida Útil</th>
                                <th>Reg. Sanitario</th>
                                <th>Proveedor</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Vencimiento</th>
                                <th class="action-btn">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargan vía AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /Compras Recientes -->
        
    </div>
</div>

<!-- Modal para ver detalles del lote -->
<div class="modal fade" id="batchDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Lote</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="batchDetailsContent">
                <!-- Contenido se carga dinámicamente -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('page-js')
<script>
$(document).ready(function() {
    var table = $('#purchase-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('purchases.index')}}",
            data: function(d) {
                d.filter = $('.btn-group .btn.active').attr('id').replace('filter-', '');
            }
        },
        columns: [
            {data: 'batch_number', name: 'batch_number', orderable: true},
            {data: 'product', name: 'product'},
            {data: 'marca', name: 'marca'},
            {data: 'serie', name: 'serie'},
            {data: 'category', name: 'category'},
            {data: 'riesgo', name: 'riesgo'},
            {data: 'concentracion', name: 'concentracion'},
            {data: 'forma_farmaceutica', name: 'forma_farmaceutica'},
            {data: 'presentacion_comercial', name: 'presentacion_comercial'},
            {data: 'unidad_medida', name: 'unidad_medida'},
            {data: 'vida_util', name: 'vida_util'},
            {data: 'registro_sanitario', name: 'registro_sanitario'},
            {data: 'supplier', name: 'supplier'},
            {data: 'cost_price', name: 'cost_price'},
            {data: 'quantity', name: 'quantity', orderable: false},
            {data: 'expiry_date', name: 'expiry_date'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        order: [[14, 'asc']], // Ordenar por fecha de vencimiento (ahora es la columna 14)
        language: {
            processing: "Procesando...",
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
            infoFiltered: "(filtrado de un total de _MAX_ registros)",
            loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron resultados",
            emptyTable: "No hay datos disponibles en la tabla",
            paginate: {
                first: "Primero",
                previous: "Anterior",
                next: "Siguiente",
                last: "Último"
            }
        }
    });

    // Filtros rápidos
    $('.btn-group .btn').on('click', function() {
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
        table.ajax.reload();
    });

    // Ver detalles del lote (click en número de lote)
    $(document).on('click', '.batch-number', function() {
        var batchNumber = $(this).text();
        $('#batchDetailsContent').html(`
            <div class="text-center">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Cargando detalles del lote ${batchNumber}...</p>
            </div>
        `);
        $('#batchDetailsModal').modal('show');
        
        setTimeout(function() {
            $('#batchDetailsContent').html(`
                <div class="row">
                    <div class="col-md-6">
                        <h6>Información del Lote</h6>
                        <p><strong>Número:</strong> ${batchNumber}</p>
                        <p><strong>Estado:</strong> <span class="badge badge-success">Activo</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Movimientos</h6>
                        <p>Aquí irían los movimientos del lote...</p>
                    </div>
                </div>
            `);
        }, 1000);
    });

   $(document).on('click', '.btn-danger[data-id]', function(e) {
        e.preventDefault();
        console.log('Botón eliminar clickeado'); // Debug
        
        var id = $(this).data('id');
        var route = $(this).data('route');
        
        console.log('ID:', id, 'Route:', route); // Debug
        
        // Verificar que tenemos los datos necesarios
        if (!id || !route) {
            console.error('Faltan datos: ID o Route');
            Swal.fire('Error', 'Error en la configuración del botón eliminar', 'error');
            return;
        }
        
        // Versión simplificada compatible con SweetAlert2 antiguo
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción no se puede deshacer",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.value) {
                console.log('Enviando petición AJAX...'); // Debug
                
                $.ajax({
                    url: route,
                    type: 'DELETE',
                    data: {
                        '_token': $('meta[name="csrf-token"]').attr('content'),
                        'id': id
                    },
                    beforeSend: function() {
                        // Mostrar indicador simple
                        Swal.fire({
                            title: 'Eliminando...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            showConfirmButton: false
                        });
                    },
                    success: function(response) {
                        console.log('Respuesta del servidor:', response); // Debug
                        
                        if (response.success) {
                            Swal.fire(
                                'Eliminado',
                                response.message,
                                'success'
                            );
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error AJAX:', xhr.responseText); // Debug
                        
                        let errorMessage = 'No se pudo eliminar el registro';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 404) {
                            errorMessage = 'El registro no fue encontrado';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Error interno del servidor';
                        }
                        
                        Swal.fire('Error', errorMessage, 'error');
                    }
                });
            }
        });
    });

    // Activar tooltips
    $(document).on('mouseenter', '[data-toggle="tooltip"]', function() {
        $(this).tooltip('show');
    });
});

// Función global para ver detalles del lote (por si la necesitas)
function viewBatchDetails(batchNumber) {
    $('#batchDetailsContent').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Cargando detalles del lote ${batchNumber}...</p>
        </div>
    `);
    $('#batchDetailsModal').modal('show');
}
</script>
@endpush