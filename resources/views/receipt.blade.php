<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->id }}</title>
    <style>
        /* Thermal printer optimized styles */
        @media print {
            @page {
                margin: 0;
                size: 80mm auto; /* Standard thermal receipt width */
            }
            body {
                margin: 0;
                padding: 0;
                font-size: 12px;
                line-height: 1.2;
            }
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: 'Courier New', monospace;
            color: #000;
            font-size: 12px;
            line-height: 1.2;
            margin: 0;
            padding: 5px;
            width: 80mm; /* Fixed width for thermal paper */
            max-width: 80mm;
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #000;
        }
        
        .receipt-header h2 {
            font-size: 14px;
            margin: 5px 0;
            font-weight: bold;
        }
        
        .receipt-details {
            margin-bottom: 10px;
        }
        
        .receipt-details .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .items-table th {
            text-align: left;
            padding: 3px 0;
            border-bottom: 1px dashed #000;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 3px 0;
            border-bottom: 1px dashed #ddd;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-section {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .total-row.final {
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }
        
        .barcode {
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="receipt-header">
            <h2>FABKIN STORE</h2>
            <div>123 Fashion Street</div>
            <div>Karachi, 75500</div>
            <div>Pakistan</div>
        </div>
        
        <div class="receipt-details">
            <div class="row">
                <div>Receipt #: <strong>{{ $order->id }}</strong></div>
                <div>{{ \Carbon\Carbon::parse($order->date)->format('d/m/Y H:i') }}</div>
            </div>
            <div class="row">
                <div>Customer: Walk-in</div>
                <div>Status: {{ ucfirst($order->status) }}</div>
            </div>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>{{ $product['name'] }}</td>
                    <td class="text-right">{{ $product['qty'] }}</td>
                    <td class="text-right">{{ number_format($product['price'], 2) }}</td>
                    <td class="text-right">{{ number_format($product['total'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="total-section">
            <div class="total-row">
                <div>Subtotal:</div>
                <div>PKR {{ number_format($order->total, 2) }}</div>
            </div>
            @php
                $tax = $order->total * 0.10;
            @endphp
            <div class="total-row">
                <div>Tax (10%):</div>
                <div>PKR {{ number_format($tax, 2) }}</div>
            </div>
            <div class="total-row final">
                <div>Grand Total:</div>
                <div>PKR {{ number_format($order->total + $tax, 2) }}</div>
            </div>
        </div>
        
        <div class="barcode">
            <!-- You can add a barcode here if needed -->
            * * * * * * * * * * * * *
        </div>
        
        <div class="footer">
            <div>Thank you for your business!</div>
            <div>FabKin POS System</div>
        </div>
    </div>
</body>
</html>