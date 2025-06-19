
@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    <link rel="stylesheet" href="{{asset('assets/plugins/chart.js/Chart.min.css')}}">
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">¡Bienvenido {{auth()->user()->name}}!</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item active">Panel</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-primary border-primary">
                        <i class="fe fe-money"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{AppSettings::get('app_currency', '$')}} {{$today_sales}}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    <h6 class="text-muted">Ventas en Efectivo Hoy</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-primary w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-success">
                        <i class="fe fe-credit-card"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{$total_categories}}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    <h6 class="text-muted">Categorías de Productos</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-success w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-danger border-danger">
                        <i class="fe fe-folder"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{$total_expired_products}}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    <h6 class="text-muted">Productos Vencidos</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-danger w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-warning border-warning">
                        <i class="fe fe-users"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{\DB::table('users')->count()}}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    <h6 class="text-muted">Usuarios del Sistema</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-warning w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


    <div class="col-md-12 col-lg-6">
                    
        <!-- Pie Chart -->
        <div class="card card-chart">
            <div class="card-header">
                <h4 class="card-title text-center">Recursos</h4>
            </div>
            <div class="card-body">
                <div style="">
                    {!! $pieChart->render() !!}
                </div>
            </div>
        </div>
        <!-- /Pie Chart -->
        
    </div>	
    
    
</div>

@endsection

@push('page-js')
<script>
   $(document).ready(function() {
    var table = $('#sales-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{route('sales.index')}}",
        columns: [
            { 
                data: 'product_id', 
                name: 'product_id',
                render: function(data, type, row) {
                    return row.product ? row.product.name : 'N/A';
                }
            },
            { data: 'quantity', name: 'quantity' },
            { data: 'total_price', name: 'total_price' },
            { 
                data: 'created_at', 
                name: 'created_at',
                render: function(data) {
                    return new Date(data).toLocaleString();
                }
            },
        ]
    });
});
</script> 
<script src="{{asset('assets/plugins/chart.js/Chart.bundle.min.js')}}"></script>
@endpush