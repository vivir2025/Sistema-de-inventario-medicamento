@extends('layouts.admin_master')
@section('content')
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table mr-1"></i>
        Lista de Clientes
        {{-- CAMBIO: Usar route() en lugar de URL::to() --}}
        <a href="{{ route('add.customer') }}" class="btn btn-primary btn-sm float-right">Agregar Cliente</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif
        
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo Electrónico</th>
                        <th>Empresa</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $row)
                    <tr>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->email }}</td>
                        <td>{{ $row->company ?? 'N/A' }}</td>
                        <td>{{ $row->address ?? 'N/A' }}</td>
                        <td>{{ $row->phone ?? 'N/A' }}</td>
                        <td>
                           
                            <a href="{{ route('edit.customer', $row->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                           
                            <form id="delete-form-{{$row->id}}" action="{{ route('delete.customer', $row->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('script')
<link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.colVis.min.js"></script>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        columnDefs: [
            {bSortable: false, targets: [5]}
        ],
        dom: 'lBfrtip',
        buttons: [
            {
                extend: 'copyHtml5',
                exportOptions: {
                    modifier: {
                        page: 'current'
                    },
                    columns: [0, 1, 2, 3, 4]
                }
            },
            {
                extend: 'excelHtml5',
                exportOptions: {
                    modifier: {
                        page: 'current'
                    },
                    columns: [0, 1, 2, 3, 4]
                }
            },
            {
                extend: 'pdfHtml5',
                exportOptions: {
                    modifier: {
                        page: 'current'
                    },
                    columns: [0, 1, 2, 3, 4]
                }
            },
            'colvis'
        ],
        language: {
            "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
        }
    });
});

function confirmDelete(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este cliente?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>

@endsection