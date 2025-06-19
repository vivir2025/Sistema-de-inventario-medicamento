
@extends('admin.layouts.app')

<x-assets.datatables />


@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Reportes de Ventas</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item active">Generar Reporte de Ventas</li>
    </ul>
</div>
<div class="col-sm-5 col">
    <a href="#generate_report" data-toggle="modal" class="btn btn-primary float-right mt-2">Generar Reporte</a>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
    
        @isset($sales)
            <!--  Reporte de Ventas -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="sales-table" class="datatable table table-hover table-center mb-0">
                            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Correo cliente</th>
                    <th>Teléfono cliente</th>
                    <th>Medicamento</th>
                    <th>Ubicación</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sales as $sale)
                    @if (!empty($sale->product->purchase))
                        <tr>
                            <!-- Columna Cliente (Nombre) -->
                            <td>
                                <strong>{{ $sale->customer->name ?? 'Sin cliente' }}</strong>
                            </td>
                            
                             <!-- Columna Email Cliente -->
                <td>
                    {{ $sale->customer->email ?? 'No disponible' }}
                </td>
                
                <!-- Columna Teléfono Cliente -->
                <td>
                    {{ $sale->customer->phone ?? 'No disponible' }}
                </td>
                              
            
                            <!-- Columna Medicamento -->
                            <td>
                                {{ $sale->product->purchase->product }}
                                @if (!empty($sale->product->purchase->image))
                                    <span class="avatar avatar-sm mr-2">
                                        <img class="avatar-img" src="{{ asset('storage/purchases/'.$sale->product->purchase->image) }}" alt="imagen">
                                    </span>
                                @endif
                            </td>
                            
                            <!-- Columna Ubicación -->
                            <td>{{ ucfirst($sale->ubicacion ?? 'Desconocida') }}</td>
                            
                            <!-- Columna Cantidad -->
                            <td>{{ $sale->quantity }}</td>
                            
                            <!-- Columna Total -->
                            <td>{{ AppSettings::get('app_currency', '$') }} {{ number_format($sale->total_price, 2) }}</td>
                            
                            <!-- Columna Fecha -->
                            <td>{{ date_format(date_create($sale->created_at), "d M, Y") }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>

                        </table>
                    </div>
                </div>
            </div>
            <!-- / Reporte de Ventas -->
        @endisset
       
        
    </div>
</div>

<!-- Modal Generar Reporte -->
<div class="modal fade" id="generate_report" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generar Reporte</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{route('sales.report')}}">
                    @csrf
                    <div class="row form-row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Desde</label>
                                        <input type="date" name="from_date" class="form-control from_date">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Hasta</label>
                                        <input type="date" name="to_date" class="form-control to_date">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block submit_report">Enviar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Modal Generar Reporte -->
@endsection

@push('page-js')
<script>
    $(document).ready(function(){
        $('#sales-table').DataTable({
            dom: 'Bfrtip',		
            buttons: [
                {
                 extend: 'collection',
            text: 'Exportar Datos',
            buttons: [
                {
                    extend: 'pdf',
                    title: 'Reporte de Compras',
                    exportOptions: {
                        columns: "thead th:not(.action-btn)"
                    },
                    customize: function (doc) {
                        // Configurar orientación horizontal
                        doc.pageOrientation = 'landscape';
                        
                        // Opcional: puedes ajustar otros aspectos del PDF aquí
                        // Por ejemplo, márgenes, estilos, etc.
                        doc.defaultStyle.fontSize = 8;
                        doc.styles.tableHeader.fontSize = 9;
                        doc.styles.title.fontSize = 12;
                    }
                },
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: "thead th:not(.action-btn)"
                        }
                    },
                    {
                        extend: 'csv',
                        exportOptions: {
                            columns: "thead th:not(.action-btn)"
                        }
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            columns: "thead th:not(.action-btn)"
                        }
                    }
                ]
                }
            ],
            language: {
                emptyTable: "No hay datos disponibles",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                lengthMenu: "Mostrar _MENU_ registros",
                loadingRecords: "Cargando...",
                processing: "Procesando...",
                search: "Buscar:",
                zeroRecords: "No se encontraron registros coincidentes"
            }
        });
    });
</script>
@endpush