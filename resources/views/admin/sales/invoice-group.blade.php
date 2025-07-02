<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $sale_group_id }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: white;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .invoice-box {
            width: 90%;
            height: 100vh;
            padding: 30px;
            box-sizing: border-box;
            font-size: 14px;
            line-height: 1.4;
            background: white;
            display: flex;
            flex-direction: column;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4a89dc;
            flex-shrink: 0;
        }
        .company-info {
            text-align: right;
        }
        .title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #2c3e50;
            letter-spacing: 1px;
        }
        .invoice-details {
            margin: 15px 0;
            background: #f8fafc;
            padding: 12px 15px;
            border-radius: 6px;
            border-left: 4px solid #4a89dc;
            flex-shrink: 0;
        }
        .invoice-details div {
            margin: 5px 0;
        }
        .municipality-section {
            margin: 15px 0;
            background:  #f8fafc;
            padding: 12px 15px;
            border-radius: 6px;
            border-left: 4px solid #4a89dc;
            flex-shrink: 0;
        }
        .municipality-section h5 {
            margin-top: 0;
            margin-bottom: 8px;
            color: #2c3e50;
            font-size: 14px;
            font-weight: 600;
        }
        .municipality-info {
            margin: 5px 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            box-shadow: 0 2px 3px rgba(0,0,0,0.1);
            flex: 1;
            min-height: 200px;
        }
        table th {
            text-align: left;
            background: #4a89dc;
            color: white;
            padding: 10px 12px;
            font-weight: 600;
            font-size: 13px;
        }
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e1e1e1;
            font-size: 13px;
            vertical-align: top;
        }
        table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        table tr:hover {
            background-color: #f1f5f9;
        }
        .total {
            font-weight: bold;
            font-size: 16px;
            color: #2c3e50;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: auto;
            text-align: center;
            font-size: 11px;
            color: #7f8c8d;
            padding-top: 15px;
            border-top: 1px solid #e1e1e1;
            flex-shrink: 0;
        }
        strong {
            color: #2c3e50;
        }
        .batch-number {
            font-size: 0.85em;
            color: #7f8c8d;
            margin-top: 2px;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <div class="header">
            <div>
                <div class="title">REPORTE DE SALIDA</div>
                <div>No. {{ str_pad($sale_group_id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div class="company-info">
                <strong>FUNDACION NACER PARA VIVIR IPS</strong><br>
                NIT: 900.857.959 - 1<br>
                Dirección:  CR 18 79 A 42 Cajibío-Cauca<br>
                Teléfono: 3137276557<br>
                Email: tecnologia@nacerparavivir.org
            </div>
        </div>

        <div class="invoice-details">
            <div><strong>Fecha:</strong> {{ $created_at->format('d/m/Y') }}</div>
            <div><strong>Cliente:</strong> {{ $customer->name }}</div>
            <div><strong>Dirección:</strong> {{ $customer->address ?? 'N/A' }}</div>
            <div><strong>Teléfono:</strong> {{ $customer->phone ?? 'N/A' }}</div>
            <div><strong>Email:</strong> {{ $customer->email ?? 'N/A' }}</div>
        </div>

        <div class="municipality-section">
            <h5>Información de Sedes</h5>
            @php
                $municipalityNames = [
                    'cajibio' => 'Cajibío',
                    'morales' => 'Morales',
                    'piendamo' => 'Piendamó'
                ];
                $municipalityInfo = [];
                
                foreach($sales as $sale) {
                    if($sale->product && $sale->product->purchase) {
                        $originMunicipality = $sale->origin_municipality ?? null;
                        $destinationMunicipality = $sale->destination_municipality ?? null;
                        $saleType = $sale->sale_type ?? 'sale';
                        
                        if ($saleType === 'sale' && $originMunicipality && $destinationMunicipality && $originMunicipality !== $destinationMunicipality) {
                            $originName = $municipalityNames[$originMunicipality] ?? ucfirst($originMunicipality);
                            $destinationName = $municipalityNames[$destinationMunicipality] ?? ucfirst($destinationMunicipality);
                            $municipalityInfo[] = "Producto de la sede {$originName} transferido a {$destinationName}";
                        } else if ($saleType === 'sale' && $originMunicipality && $destinationMunicipality && $originMunicipality === $destinationMunicipality) {
                            $municipalityName = $municipalityNames[$originMunicipality] ?? ucfirst($originMunicipality);
                            $municipalityInfo[] = "Producto de la sede {$municipalityName}";
                        } else if ($saleType === 'sale' && $originMunicipality && !$destinationMunicipality) {
                            $municipalityName = $municipalityNames[$originMunicipality] ?? ucfirst($originMunicipality);
                            $municipalityInfo[] = "Producto de la sede {$municipalityName}";
                        } else if ($saleType === 'transfer' && $originMunicipality && $destinationMunicipality) {
                            $originName = $municipalityNames[$originMunicipality] ?? ucfirst($originMunicipality);
                            $destinationName = $municipalityNames[$destinationMunicipality] ?? ucfirst($destinationMunicipality);
                            $municipalityInfo[] = "Producto de la sede {$originName} transferido a {$destinationName}";
                        } else {
                            $productMunicipality = $sale->product->municipality;
                            $municipalityName = $municipalityNames[$productMunicipality] ?? ucfirst($productMunicipality);
                            $municipalityInfo[] = "Producto de la sede {$municipalityName}";
                        }
                    }
                }
                
                $uniqueInfo = array_unique($municipalityInfo);
            @endphp
            
            @foreach($uniqueInfo as $info)
                <div class="municipality-info">{{ $info }}</div>
            @endforeach
        </div>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>
                        {{ $sale->product->purchase->product }}
                        <div class="batch-number"> {{ $sale->product->purchase->batch_number }}</div>
                    </td>
                    <td>{{ $sale->quantity }}</td>
                    <td>${{ number_format($sale->product->price, 2) }}</td>
                    <td>${{ number_format($sale->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right total">TOTAL</td>
                    <td class="total">${{ number_format($total_price, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>FUNDACION NACER PARA VIVIR IPS - NIT: 900.123.456-7</p>
            <p>Este documento es una factura de venta y soporte contable según Art. 617 del E.T.</p>
        </div>
    </div>
</body>
</html>