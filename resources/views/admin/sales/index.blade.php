@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
<style>
.product-item {
    display: flex;
    align-items: center;
    padding: 2px 0;
}
.product-item .avatar {
    margin-right: 8px;
}
.product-item .badge {
    font-size: 0.75em;
}
.products-column {
    max-width: 300px;
}
.municipality-column {
    max-width: 200px;
}
.repair-section {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 20px;
}

</style>
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Ventas</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item active">Ventas</li>
    </ul>
</div>
@can('create-sale')
<div class="col-sm-5 col">
    <a href="{{route('sales.create')}}" class="btn btn-primary float-right mt-2">Agregar Venta</a>
</div>
@endcan
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        
       
        <!--  Ventas -->
       <div class="card-body">
    <div class="table-responsive">
        <table id="sales-table" class="datatable table table-hover table-center mb-0">
            <thead>
                <tr>
                    <th class="products-column">Productos</th>
                    <th class="municipality-column">Municipio</th>
                    <th>Cliente</th>
                    <th>Total de Ítems</th>
                    <th>Precio Total</th>
                    <th>Fecha</th>
                    <th class="action-btn">Acción</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
        <!-- / ventas -->
        
    </div>
</div>
@endsection

@push('page-js')
<script>
$(document).ready(function() {
    var table = $('#sales-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('sales.index') }}",
        columns: [
            {
                data: 'products', 
                name: 'products', 
                orderable: false, 
                searchable: true
            },
            {
                data: 'municipality', 
                name: 'municipality',
                searchable: true,
                orderable: false
            },
            {
                data: 'customer', 
                name: 'customer',
                searchable: true
            },
            {
                data: 'total_quantity', 
                name: 'total_quantity',
                searchable: false
            },
            {
                data: 'total_price', 
                name: 'total_price',
                searchable: true
            },
            {
                data: 'date', 
                name: 'date',
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
            emptyTable: "No hay datos disponibles",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            searchPlaceholder: "Buscar por cliente, producto, municipio...",
            zeroRecords: "No se encontraron registros coincidentes"
        },
        searchDelay: 500,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        order: [[5, 'desc']],
        responsive: true,
        autoWidth: false,
        search: {
            smart: true,
            regex: false,
            caseInsensitive: true
        }
    });

    // Usar delegación de eventos más específica
    $(document).on('click', 'button[id="deletebtn"]', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const groupId = $(this).data('group-id');
        const customerId = $(this).data('customer-id');

        console.log('Group ID:', groupId, 'Customer ID:', customerId); // Debug

        if (!groupId || !customerId || groupId === 'null' || customerId === 'null') {
            // Versión compatible con SweetAlert2 antiguo
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error',
                    text: 'Datos incompletos para eliminar.',
                    type: 'error' // Usar 'type' en lugar de 'icon' para versiones antiguas
                });
            } else if (typeof swal !== 'undefined') {
                // Para SweetAlert1
                swal('Error', 'Datos incompletos para eliminar.', 'error');
            } else {
                alert('Error: Datos incompletos para eliminar.');
            }
            return;
        }

        // Función para mostrar confirmación compatible
        function showConfirmation() {
            if (typeof Swal !== 'undefined') {
                // SweetAlert2
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción eliminará toda la venta y revertirá el inventario. ¡No podrás deshacer esto!",
                    type: 'warning', // Usar 'type' para compatibilidad
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value || result.isConfirmed) { // Compatibilidad con diferentes versiones
                        performDelete();
                    }
                });
            } else if (typeof swal !== 'undefined') {
                // SweetAlert1
                swal({
                    title: '¿Estás seguro?',
                    text: "Esta acción eliminará toda la venta y revertirá el inventario. ¡No podrás deshacer esto!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }, function(isConfirm) {
                    if (isConfirm) {
                        performDelete();
                    }
                });
            } else {
                // Fallback nativo
                if (confirm('¿Estás seguro? Esta acción eliminará toda la venta y revertirá el inventario.')) {
                    performDelete();
                }
            }
        }

        // Función para realizar la eliminación
        function performDelete() {
            // Mostrar loading
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Por favor espera',
                    type: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    onBeforeOpen: function() {
                        Swal.showLoading();
                    }
                });
            }

            $.ajax({
                url: "{{ route('sales.destroy-group') }}",
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: "{{ csrf_token() }}",
                    sale_group_id: groupId,
                    customer_id: customerId
                },
                success: function(response) {
                    console.log('Respuesta del servidor:', response); // Debug
                    
                    if (response.success) {
                        table.ajax.reload(null, false); // Mantener paginación
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: response.message,
                                type: 'success'
                            });
                        } else if (typeof swal !== 'undefined') {
                            swal('¡Eliminado!', response.message, 'success');
                        } else {
                            alert('¡Eliminado! ' + response.message);
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Error desconocido',
                                type: 'error'
                            });
                        } else {
                            alert('Error: ' + (response.message || 'Error desconocido'));
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Error AJAX:', xhr); // Debug
                    
                    let errorMsg = 'Error desconocido.';
                    if (xhr.responseJSON?.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            const errorData = JSON.parse(xhr.responseText);
                            errorMsg = errorData.message || errorMsg;
                        } catch (e) {
                            errorMsg = 'Error del servidor (Status: ' + xhr.status + ')';
                        }
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Error',
                            text: errorMsg,
                            type: 'error'
                        });
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                }
            });
        }

        showConfirmation();
    });
});
</script>
@endpush