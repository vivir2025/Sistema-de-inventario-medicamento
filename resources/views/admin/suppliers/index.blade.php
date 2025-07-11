
@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
    <h3 class="page-title">Proveedores</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item active">Proveedores</li>
    </ul>
</div>
<div class="col-sm-5 col">
    <a href="{{route('suppliers.create')}}" class="btn btn-primary float-right mt-2">Agregar Nuevo</a>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
    
        <!-- Proveedores -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="supplier-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Correo electrónico</th>
                                <th>Dirección</th>
                                <th>Identificacion</th>
                                <th class="action-btn">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- @foreach ($suppliers as $supplier)
                            <tr>
                                
                                <td>{{$supplier->name}}</td>
                                <td>{{$supplier->phone}}</td>
                                <td>{{$supplier->email}}</td>
                                <td>{{$supplier->address}}</td>
                                <td>{{$supplier->company}}</td>
                                <td>
                                    <div class="actions">
                                        <a class="btn btn-sm bg-success-light" href="{{route('edit-supplier',$supplier)}}">
                                            <i class="fe fe-pencil"></i> Editar
                                        </a>
                                        <a data-id="{{$supplier->id}}" href="javascript:void(0);" class="btn btn-sm bg-danger-light deletebtn" data-toggle="modal">
                                            <i class="fe fe-trash"></i> Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach							 --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /Proveedores-->
        
    </div>
</div>

@endsection	

@push('page-js')
<script>
    $(document).ready(function() {
        var table = $('#supplier-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{route('suppliers.index')}}",
            columns: [
               
                {data: 'name', name: 'name'},
                {data: 'phone', name: 'phone'},
                {data: 'email', name: 'email'},
                {data: 'address', name: 'address'},
                {data: 'company',name: 'company'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
        
    });
</script> 
@endpush