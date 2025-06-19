@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    <style>
        .expiry-green {
            background-color: #d4edda !important; /* Verde claro */
            color: #155724;
        }
        .expiry-orange {
            background-color: #fff3cd !important; /* Naranja claro */
            color: #856404;
        }
        .expiry-red {
            background-color: #f8d7da !important; /* Rojo claro */
            color: #721c24;
        }
    </style>
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Productos</h3>
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
<div class="row">
    <div class="col-md-12">
        <!-- Productos -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="product-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Nombre del Producto</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Descuento</th>
                                <th>Fecha de Vencimiento</th>
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
            type: 'GET',
            error: function(xhr, error, code) {
                console.log('Error:', xhr.responseText);
            }
        },
        columns: [
            {
                data: 'product',
                name: 'product',
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
                data: 'price',
                name: 'price',
                orderable: true,
                searchable: true
            },
            {
                data: 'quantity',
                name: 'quantity',
                orderable: true,
                searchable: false // No buscar en cantidad calculada
            },
            {
                data: 'discount',
                name: 'discount',
                orderable: true,
                searchable: true
            },
            {
                data: 'expiry_date', 
                name: 'expiry_date',
                orderable: true,
                searchable: true,
                render: function(data, type) {
                    // Solo aplicar colores en la visualización
                    if (type === 'display' && data && data !== 'N/A') {
                        // Intentar extraer la fecha del texto formateado
                        var dateMatch = data.match(/\d{2} \w{3}, \d{4}/);
                        if (dateMatch) {
                            var dateStr = dateMatch[0];
                            var expiryDate = new Date(dateStr);
                            var today = new Date();
                            var oneMonthFromNow = new Date();
                            oneMonthFromNow.setMonth(oneMonthFromNow.getMonth() + 1);
                            
                            var colorClass = '';
                            
                            if (expiryDate < today) {
                                colorClass = 'expiry-red';
                            } else if (expiryDate <= oneMonthFromNow) {
                                colorClass = 'expiry-orange';
                            } else {
                                colorClass = 'expiry-green';
                            }
                            
                            return '<span class="' + colorClass + ' p-2 rounded">' + data + '</span>';
                        }
                    }
                    return data;
                }
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
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        pageLength: 25,
        responsive: true,
        stateSave: true,
        drawCallback: function(settings) {
            console.log('DataTable redrawn');
        }
    });
    
    // Debug para la búsqueda
    $('#product-table_filter input').on('keyup', function() {
        console.log('Search term:', this.value);
    });
});

// Código para eliminar (sin cambios)
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