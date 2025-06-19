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
        <th>Empresa P.</th>
        <th>Email P.</th>
        <th>Teléfono P.</th>
        <th>Medicamento</th>
        <th>Categoría</th>
        <th>Precio</th>
        <th>Cantidad</th>
        <th>Vencimiento</th>
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
        
        <!-- Columna Categoría -->
        <td>{{$purchase->category->name}}</td>
        
        <!-- Columna Precio -->
        <td>{{AppSettings::get('app_currency', '$')}}{{number_format($purchase->cost_price, 2)}}</td>
        
        <!-- Columna Cantidad -->
        <td>{{$purchase->quantity}}</td>
        
        <!-- Columna Fecha Vencimiento -->
        <td>{{date_format(date_create($purchase->expiry_date),"d M, Y")}}</td>
        
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
                    title: 'Reporte de Compras',
                    exportOptions: {
                        columns: "thead th:not(.action-btn)"
                    }
                },
                {
                    extend: 'csv',
                    title: 'Reporte de Compras',
                    exportOptions: {
                        columns: "thead th:not(.action-btn)"
                    }
                },
                {
                    extend: 'print',
                    title: 'Reporte de Compras',
                    exportOptions: {
                        columns: "thead th:not(.action-btn)"
                    }
                }
            ]
            }
        ],
        order: [[6, 'desc']] // Ordenar por fecha de compra descendente
    });
});
</script>
@endpush