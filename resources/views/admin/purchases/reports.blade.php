@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Reportes de Compras</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item active">Generar Reporte de Compras</li>
    </ul>
</div>
<div class="col-sm-5 col">
    <a href="#generate_report" data-toggle="modal" class="btn btn-primary float-right mt-2">Generar Reporte</a>
</div>
@endpush

@section('content')
    @isset($purchases)
    <div class="row">
        <div class="col-md-12">
            <!-- Reportes de Compras -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="purchase-table" class="datatable table table-hover table-center mb-0">
                            <thead>
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Identificacion P.</th>
                                    <th>Email P.</th>
                                    <th>Teléfono P.</th>
                                    <th>Medicamento</th>
                                    <th>Lote</th>
                                    <th>Marca</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Vencimiento</th>
                                    <th>Serie</th>
                                    <th>Riesgo</th>
                                    <th>Vida Útil</th>
                                    <th>Registro Sanitario</th>
                                    <th>Presentación Comercial</th>
                                    <th>Forma Farmacéutica</th>
                                    <th>Concentración</th>
                                    <th>Unidad Medida</th>
                                    <th>Fecha Compra</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchases as $purchase)
                                    @if(!empty($purchase->supplier) && !empty($purchase->category))
                                    <tr>
                                        <!-- Columna Proveedor -->
                                        <td>{{$purchase->supplier->name}}</td>
                                        
                                        <!-- Columna Empresa -->
                                        <td>{{$purchase->supplier->company ?? 'N/A'}}</td>
                                        
                                        <!-- Columna Email -->
                                        <td>{{$purchase->supplier->email ?? 'N/A'}}</td>
                                        
                                        <!-- Columna Teléfono -->
                                        <td>{{$purchase->supplier->phone ?? 'N/A'}}</td>
                                        
                                        <!-- Columna Medicamento -->
                                        <td>
                                            <h2 class="table-avatar">
                                                @if(!empty($purchase->image))
                                                <span class="avatar avatar-sm mr-2">
                                                    <img class="avatar-img" src="{{asset('storage/purchases/'.$purchase->image)}}" alt="imagen producto">
                                                </span>
                                                @endif
                                                {{$purchase->product}}
                                            </h2>
                                        </td>
                                        
                                        <!-- Columna Lote -->
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $purchase->batch_number ?? 'Sin lote' }}
                                            </span>
                                        </td>

                                        <!-- Columna Marca -->
                                        <td>{{$purchase->marca ?? 'N/A'}}</td>

                                        <!-- Columna Categoría -->
                                        <td>{{$purchase->category->name}}</td>
                                        
                                        <!-- Columna Precio -->
                                        <td>{{AppSettings::get('app_currency', '$')}}{{number_format($purchase->cost_price, 2)}}</td>
                                        
                                        <!-- Columna Cantidad -->
                                        <td>{{$purchase->quantity}}</td>
                                        
                                        <!-- Columna Fecha Vencimiento -->
                                        <td>{{date_format(date_create($purchase->expiry_date),"d M, Y")}}</td>
                                        
                                        <!-- Nuevos campos -->
                                        <td>{{$purchase->serie ?? 'N/A'}}</td>
                                        <td>{{$purchase->riesgo ?? 'N/A'}}</td>
                                        <td>{{$purchase->vida_util ?? 'N/A'}}</td>
                                        <td>{{$purchase->registro_sanitario ?? 'N/A'}}</td>
                                        <td>{{$purchase->presentacion_comercial ?? 'N/A'}}</td>
                                        <td>{{$purchase->forma_farmaceutica ?? 'N/A'}}</td>
                                        <td>{{$purchase->concentracion ?? 'N/A'}}</td>
                                        <td>{{$purchase->unidad_medida ?? 'N/A'}}</td>
                                        
                                        <!-- Columna Fecha Compra -->
                                        <td>{{date_format(date_create($purchase->created_at),"d M, Y")}}</td>
                                    </tr>
                                    @endif
                                @endforeach                         
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /Reportes de Compras -->
        </div>
    </div>
    @endisset

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
                    <form method="post" action="{{route('purchases.report')}}">
                        @csrf
                        <div class="row form-row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Desde</label>
                                            <input type="date" name="from_date" class="form-control from_date" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Hasta</label>
                                            <input type="date" name="to_date" class="form-control to_date" required>
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
    $('#purchase-table').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'collection',
                text: 'Exportar Datos',
                buttons: [
                 
                    {
                        extend: 'excel',
                        title: 'Reporte de Compras',
                        exportOptions: {
                            columns: ":visible"
                        }
                    },
                    {
                        extend: 'csv',
                        title: 'Reporte de Compras',
                        exportOptions: {
                            columns: ":visible"
                        }
                    },
                    {
                        extend: 'print',
                        title: 'Reporte de Compras',
                        exportOptions: {
                            columns: ":visible"
                        },
                        customize: function (win) {
                            $(win.document.body).css('font-size', '8pt');
                            $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                        }
                    }
                ]
            },
            'colvis'
        ],
        order: [[18, 'desc']], // Ordenar por fecha de compra descendente
        scrollX: true,
        scrollY: 400,
        scrollCollapse: true,
        columnDefs: [
            { width: 100, targets: 0 }, // Proveedor
            { width: 80, targets: 1 },   // Empresa
            { width: 100, targets: 2 },  // Email
            { width: 80, targets: 3 },   // Teléfono
            { width: 120, targets: 4 },  // Medicamento
            { width: 60, targets: 5 },  // Lote
            { width: 80, targets: 6 },   // Marca
            { width: 80, targets: 6 },   // Categoría
            { width: 60, targets: 7 },   // Precio
            { width: 60, targets: 8 },  // Cantidad
            { width: 80, targets: 9 },   // Vencimiento
            { width: 60, targets: 10 },  // Serie
            { width: 60, targets: 11 },  // Riesgo
            { width: 60, targets: 12 }, // Vida Útil
            { width: 80, targets: 13 },  // Registro Sanitario
            { width: 100, targets: 14 }, // Presentación Comercial
            { width: 100, targets: 15 }, // Forma Farmacéutica
            { width: 80, targets: 16 },  // Concentración
            { width: 80, targets: 17 },  // Unidad Medida
            { width: 80, targets: 18 }   // Fecha Compra
        ],
        fixedColumns: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        }
    });
});
</script>
@endpush