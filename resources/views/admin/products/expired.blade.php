@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    <!-- Buttons extension CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <style>
        .expiry-red {
            background-color: #f8d7da !important;
            color: #721c24;
        }
    </style>
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Vencidos</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('products.index')}}">Productos</a></li>
        <li class="breadcrumb-item active">Vencidos</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        
        <!-- Estadísticas por municipio -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Cajibío</h5>
                        <h3 class="text-info">{{ $expiredStats['cajibio'] }}</h3>
                        <small class="text-muted">Productos vencidos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Morales</h5>
                        <h3 class="text-success">{{ $expiredStats['morales'] }}</h3>
                        <small class="text-muted">Productos vencidos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Piendamó</h5>
                        <h3 class="text-warning">{{ $expiredStats['piendamo'] }}</h3>
                        <small class="text-muted">Productos vencidos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total</h5>
                        <h3 class="text-danger">{{ $expiredStats['total'] }}</h3>
                        <small class="text-muted">Productos vencidos</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtro por municipio -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="municipality-filter">Filtrar por municipio:</label>
                    <select class="form-control" id="municipality-filter">
                        <option value="all" {{ $municipality == 'all' ? 'selected' : '' }}>Todos los municipios</option>
                        <option value="cajibio" {{ $municipality == 'cajibio' ? 'selected' : '' }}>Cajibío</option>
                        <option value="morales" {{ $municipality == 'morales' ? 'selected' : '' }}>Morales</option>
                        <option value="piendamo" {{ $municipality == 'piendamo' ? 'selected' : '' }}>Piendamó</option>
                    </select>
                </div>
            </div>
        </div>
    
        <!-- Productos Vencidos -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="expired-product" class="table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Lote</th>
                                <th>P. Act./D. Med/Insumo</th>
                                <th>Marca</th>
                                <th>Municipio</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Descuento</th>
                                <th>Vence</th>
                                <th>Serie</th>
                                <th>Riesgo</th>
                                <th>Vida Útil</th>
                                <th>Registro Sanitario</th>
                                <th>Presentación Comercial</th>
                                <th>Forma Farmacéutica</th>
                                <th>Concentración</th>
                                <th>Unidad de Medida</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /Productos Vencidos -->
        
    </div>
</div>

@endsection

@push('page-js')
<!-- Buttons extension JS -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#expired-product').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('expired')}}",
                type: 'GET',
                data: function (d) {
                    d.municipality = $('#municipality-filter').val();
                },
                error: function(xhr, error, thrown) {
                    console.log('Error details:', xhr.responseText);
                    alert('Error al cargar los datos: ' + xhr.responseText);
                }
            },
            columns: [
                {data: 'batch_number', name: 'batch_number'},
                {data: 'product', name: 'product'},
                {data: 'marca', name: 'marca'},
                {data: 'municipality', name: 'municipality'},
                {data: 'category', name: 'category'},
                {data: 'price', name: 'price'},
                {data: 'quantity', name: 'quantity'},
                {data: 'discount', name: 'discount'},
                {
                    data: 'expiry_date', 
                    name: 'expiry_date',
                    render: function(data, type, row) {
                        return '<span class="expiry-red p-2 rounded">' + data + '</span>';
                    }
                },
                {data: 'serie', name: 'serie'},
                {data: 'riesgo', name: 'riesgo'},
                {data: 'vida_util', name: 'vida_util'},
                {data: 'registro_sanitario', name: 'registro_sanitario'},
                {data: 'presentacion_comercial', name: 'presentacion_comercial'},
                {data: 'forma_farmaceutica', name: 'forma_farmaceutica'},
                {data: 'concentracion', name: 'concentracion'},
                {data: 'unidad_medida', name: 'unidad_medida'}
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
            scrollX: true,
            drawCallback: function(settings) {
                console.log('DataTable redrawn');
            }
        });

        // Filtro por municipio
        $('#municipality-filter').on('change', function() {
            table.ajax.reload();
            
            // Actualizar URL sin recargar la página
            var municipality = $(this).val();
            var url = new URL(window.location);
            if (municipality === 'all') {
                url.searchParams.delete('municipality');
            } else {
                url.searchParams.set('municipality', municipality);
            }
            window.history.pushState({}, '', url);
        });
    });
</script>
@endpush