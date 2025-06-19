<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $sale_group_id }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #f9f9f9;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 40px;
            border: 1px solid #e1e1e1;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
            font-size: 16px;
            line-height: 1.6;
            background: white;
            border-radius: 8px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4a89dc;
        }
        .company-info {
            text-align: right;
        }
        .title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #2c3e50;
            letter-spacing: 1px;
        }
        .invoice-details {
            margin: 25px 0;
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 6px;
            border-left: 4px solid #4a89dc;
        }
        .invoice-details div {
            margin: 8px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            box-shadow: 0 2px 3px rgba(0,0,0,0.1);
        }
        table th {
            text-align: left;
            background: #4a89dc;
            color: white;
            padding: 12px 15px;
            font-weight: 600;
        }
        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e1e1e1;
        }
        table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        table tr:hover {
            background-color: #f1f5f9;
        }
        .total {
            font-weight: bold;
            font-size: 18px;
            color: #2c3e50;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 13px;
            color: #7f8c8d;
            padding-top: 20px;
            border-top: 1px solid #e1e1e1;
        }
        strong {
            color: #2c3e50;
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

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Ubicación</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->product->purchase->product }}</td>
                    <td>{{ $sale->ubicacion }}</td>
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