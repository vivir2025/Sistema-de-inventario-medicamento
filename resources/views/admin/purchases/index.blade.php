
@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    
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
    <a href="{{route('purchases.create')}}" class="btn btn-primary float-right mt-2">Agregar Nueva</a>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
    
        <!-- Compras Recientes -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="purchase-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Nombre del Medicamento</th>
                                <th>Categoría</th>
                                <th>Proveedor</th>
                                <th>Precio de Compra</th>
                                <th>Cantidad</th>
                                <th>Fecha de Vencimiento</th>
                                <th class="action-btn">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                                                        
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /Compras Recientes -->
        
    </div>
</div>
@endsection	

@push('page-js')
<script>
    $(document).ready(function() {
        var table = $('#purchase-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{route('purchases.index')}}",
            columns: [
                {data: 'product', name: 'product'},
                {data: 'category', name: 'category'},
                {data: 'supplier', name: 'supplier'},
                {data: 'cost_price', name: 'cost_price'},
                {data: 'quantity', name: 'quantity'},
                {data: 'expiry_date', name: 'expiry_date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
        
    });
</script> 
@endpush