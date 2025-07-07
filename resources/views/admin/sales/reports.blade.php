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
                                    <th>Marca</th>
                                    <th>Lote</th>
                                    <th>Serie</th>
                                    <th>Riesgo</th>
                                    <th>Vida Útil</th>
                                    <th>Registro Sanitario</th>
                                    <th>Presentación Comercial</th>
                                    <th>Forma Farmacéutica</th>
                                    <th>Concentración</th>
                                    <th>Unidad Medida</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Sede/Transferencia</th>
                                    <th>Cantidad</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sales as $sale)
                                    @if (!empty($sale->product->purchase))
                                        @php
                                            $municipalityNames = [
                                                'cajibio' => 'Cajibío',
                                                'morales' => 'Morales',
                                                'piendamo' => 'Piendamó'
                                            ];
                                            
                                            $originMunicipality = $sale->origin_municipality ?? null;
                                            $destinationMunicipality = $sale->destination_municipality ?? null;
                                            $saleType = $sale->sale_type ?? 'sale';
                                            $municipalityText = '';
                                            
                                            if ($saleType === 'sale' && $originMunicipality && $destinationMunicipality && $originMunicipality !== $destinationMunicipality) {
                                                $originName = $municipalityNames[$originMunicipality] ?? ucfirst($originMunicipality);
                                                $destinationName = $municipalityNames[$destinationMunicipality] ?? ucfirst($destinationMunicipality);
                                                $municipalityText = "De {$originName} a {$destinationName}";
                                            } else if ($saleType === 'sale' && $originMunicipality && $destinationMunicipality && $originMunicipality === $destinationMunicipality) {
                                                $municipalityName = $municipalityNames[$originMunicipality] ?? ucfirst($originMunicipality);
                                                $municipalityText = "Sede {$municipalityName}";
                                            } else if ($saleType === 'sale' && $originMunicipality && !$destinationMunicipality) {
                                                $municipalityName = $municipalityNames[$originMunicipality] ?? ucfirst($originMunicipality);
                                                $municipalityText = "Sede {$municipalityName}";
                                            } else if ($saleType === 'transfer' && $originMunicipality && $destinationMunicipality) {
                                                $originName = $municipalityNames[$originMunicipality] ?? ucfirst($originMunicipality);
                                                $destinationName = $municipalityNames[$destinationMunicipality] ?? ucfirst($destinationMunicipality);
                                                $municipalityText = "Transferencia: {$originName} → {$destinationName}";
                                            } else {
                                                $productMunicipality = $sale->product->municipality ?? null;
                                                if ($productMunicipality) {
                                                    $municipalityName = $municipalityNames[$productMunicipality] ?? ucfirst($productMunicipality);
                                                    $municipalityText = "Sede {$municipalityName}";
                                                } else {
                                                    $municipalityText = 'No especificado';
                                                }
                                            }
                                        @endphp
                                        
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
                                            <!-- Columna Marca -->
                                            <td>
                                                {{ $sale->product->purchase->marca ?? 'N/A' }}
                                            </td>

                                            <!-- Columna Lote -->
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $sale->product->purchase->batch_number ?? 'Sin lote' }}
                                                </span>
                                            </td>
                                            
                                            <!-- Nuevos campos de purchases -->
                                            <td>{{ $sale->product->purchase->serie ?? 'N/A' }}</td>
                                            <td>{{ $sale->product->purchase->riesgo ?? 'N/A' }}</td>
                                            <td>{{ $sale->product->purchase->vida_util ?? 'N/A' }}</td>
                                            <td>{{ $sale->product->purchase->registro_sanitario ?? 'N/A' }}</td>
                                            <td>{{ $sale->product->purchase->presentacion_comercial ?? 'N/A' }}</td>
                                            <td>{{ $sale->product->purchase->forma_farmaceutica ?? 'N/A' }}</td>
                                            <td>{{ $sale->product->purchase->concentracion ?? 'N/A' }}</td>
                                            <td>{{ $sale->product->purchase->unidad_medida ?? 'N/A' }}</td>
                                            
                                            <!-- Columna Fecha Vencimiento -->
                                            <td>{{ date_format(date_create($sale->product->purchase->expiry_date),"d M, Y") }}</td>
                                            
                                            <!-- Columna Sede/Transferencia -->
                                            <td>
                                                <small class="text-muted">
                                                    {{ $municipalityText }}
                                                </small>
                                            </td>
                                            
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
                            extend: 'excel',
                            title: 'Reporte de Ventas',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'csv',
                            title: 'Reporte de Ventas',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'print',
                            title: 'Reporte de Ventas',
                            exportOptions: {
                                columns: ':visible'
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
            scrollX: true,
            scrollY: 400,
            scrollCollapse: true,
            columnDefs: [
                { width: 100, targets: 0 }, // Cliente
                { width: 120, targets: 1 }, // Email
                { width: 100, targets: 2 }, // Teléfono
                { width: 150, targets: 3 }, // Medicamento
                { width: 80, targets: 4 },  // Lote
                { width: 80, targets: 5 },  // Serie
                { width: 80, targets: 6 },  // Riesgo
                { width: 80, targets: 7 }, // Vida Útil
                { width: 100, targets: 8 }, // Registro Sanitario
                { width: 120, targets: 9 }, // Presentación Comercial
                { width: 120, targets: 10 }, // Forma Farmacéutica
                { width: 100, targets: 11 }, // Concentración
                { width: 100, targets: 12 }, // Unidad Medida
                { width: 100, targets: 13 }, // Fecha Vencimiento
                { width: 150, targets: 14 }, // Sede/Transferencia
                { width: 80, targets: 15 },  // Cantidad
                { width: 80, targets: 16 },  // Total
                { width: 80, targets: 17 }   // Fecha
            ],
            fixedColumns: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            }
        });
    });
</script>
@endpush