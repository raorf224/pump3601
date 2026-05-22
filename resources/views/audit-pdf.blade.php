<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Report - {{ $data['station_name'] ?? 'Station' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        td, th {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }
        .heading {
            background: #000;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }
        .section {
            background: #d9d9d9;
            font-weight: bold;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .green { color: green; font-weight: bold; }
        .red { color: red; font-weight: bold; }
        .bold { font-weight: bold; }
        .total-row {
            background: #f0f0f0;
            font-weight: bold;
        }
    </style>
</head>
<body>

@php
    $workingCapital = $data['working_capital'] ?? 0;
    $totalOutflow = $data['total_outflow'] ?? 0;
    $remaining = $data['remaining'] ?? ['amount' => 0, 'status' => 'loss', 'color' => 'red'];
    $fuelStock = $data['fuel_stock'] ?? [];
    $transit = $data['stock_in_transit'] ?? [];
    $givenToKhattak = $data['given_to_khattak'] ?? [];
    $cashInSafe = $data['cash_in_safe'] ?? 0;
    $cashInHand = $data['cash_in_hand'] ?? 0;
    $currentNet = $data['gain_loss_current']['net'] ?? 0;
    $gainLossDetails = $data['gain_loss_current']['details'] ?? [];
    $lubeInventory = $data['lube_inventory'] ?? [];
    $expenseCurrent = $data['expense_current'] ?? 0;
    $expensePrevious = $data['expense_previous'] ?? 0;
    $fuelCardPending = $data['fuel_card_pending'] ?? [];
    $creditCardPending = $data['credit_card_pending'] ?? [];
    $shortagesPending = $data['shortages_pending'] ?? [];
    $totalShortages = $data['total_shortages'] ?? 0;
    $stationName = $data['station_name'] ?? 'Station #' . ($data['station_id'] ?? '');
    $dateRanges = $data['date_ranges'] ?? [];
    $currentMonth = $dateRanges['current'][0] ?? now()->startOfMonth()->format('Y-m-d');
@endphp

<h2 style="text-align: center;">AUDIT REPORT</h2>
<p style="text-align: center;">{{ $stationName }} | {{ now()->format('d M Y') }}</p>

<!-- WORKING CAPITAL -->
<table>
    <tr class="section"><td colspan="8">Working Capital Summary</td></tr>
    <tr><td colspan="7">Working Capital</td><td class="right">{{ number_format($workingCapital, 2) }}</td></tr>
    <tr><td colspan="7">Total Outflow</td><td class="right">{{ number_format($totalOutflow, 2) }}</td></tr>
    <tr>
        <td colspan="7"><b>Remaining / (Gain/Loss)</b><br><small>Recovered: {{ $remaining['recovered_percentage'] ?? 0 }}%</small></td>
        <td class="right {{ $remaining['color'] }}">
            {{ number_format($remaining['amount'], 2) }} ({{ $remaining['status'] == 'profit' ? 'Profit' : 'Loss' }})
            @if(($remaining['remaining_to_recover'] ?? 0) > 0)
                <br><small>Need to recover: {{ number_format($remaining['remaining_to_recover'], 2) }}</small>
            @endif
        </td>
    </tr>
</table>

<!-- FUEL STOCK -->
@if(count($fuelStock) > 0)
<table>
    <tr class="section"><td colspan="8">Fuel Stock (Tanks)</td></tr>
    @foreach($fuelStock as $stock)
    <tr>
        <td colspan="2">{{ $stock['name'] }}</td>
        <td colspan="2" class="right">{{ number_format($stock['total_current_level'], 2) }}</td>
        <td class="right">{{ number_format($stock['rate'], 2) }}</td>
        <td colspan="3" class="right">{{ number_format($stock['total_current_level'] * $stock['rate'], 2) }}</td>
    </tr>
    @endforeach
</table>
@endif

<!-- STOCK IN TRANSIT -->
@if(count($transit) > 0)
<table>
    <tr class="section"><td colspan="8">Stock in Transit</td></tr>
    @foreach($transit as $item)
    <tr>
        <td colspan="2">{{ $item['name'] }}</td>
        <td colspan="2" class="right">{{ number_format($item['qty'], 2) }}</td>
        <td class="right">{{ number_format($item['rate'], 2) }}</td>
        <td colspan="3" class="right">{{ number_format($item['amount'], 2) }}</td>
    </tr>
    @endforeach
</table>
@endif

<!-- CASH POSITION -->
<table>
    <tr class="section"><td colspan="8">Cash Position</td></tr>
    <tr><td colspan="7">Cash in Safe (Bank)</td><td class="right">{{ number_format($cashInSafe, 2) }}</td></tr>
    <tr><td colspan="7">Cash in Hand</td><td class="right">{{ number_format($cashInHand, 2) }}</td></tr>
</table>

<!-- GAIN/LOSS -->
<table>
    <tr class="section"><td colspan="8">Product Loss/Gain - {{ date('F', strtotime($currentMonth)) }}</td></tr>
    @foreach($gainLossDetails as $gl)
        @if(($gl['total_loss'] ?? 0) > 0)
        <tr>
            <td colspan="5">Loss ({{ $gl['product_name'] }}) - {{ number_format($gl['total_loss'], 2) }} Ltr</td>
            <td colspan="3" class="right red">{{ number_format($gl['loss_amount'], 2) }}</td>
        </tr>
        @endif
        @if(($gl['total_gain'] ?? 0) > 0)
        <tr>
            <td colspan="5">Gain ({{ $gl['product_name'] }}) - {{ number_format($gl['total_gain'], 2) }} Ltr</td>
            <td colspan="3" class="right green">{{ number_format($gl['gain_amount'], 2) }}</td>
        </tr>
        @endif
    @endforeach
    <tr class="total-row">
        <td colspan="7">Net Loss/Gain</td>
        <td class="right {{ $currentNet > 0 ? 'red' : 'green' }}">{{ number_format($currentNet, 2) }}</td>
    </tr>
</table>

<!-- LUBE INVENTORY -->
@if(count($lubeInventory) > 0)
<table>
    <tr class="section"><td colspan="8">Lube Inventory</td></tr>
    @foreach($lubeInventory as $lube)
    <tr>
        <td colspan="2">{{ $lube['name'] }}</td>
        <td class="right">{{ number_format($lube['quantity'], 2) }}</td>
        <td class="right">{{ number_format($lube['avg_buying_price'], 2) }}</td>
        <td colspan="4" class="right">{{ number_format($lube['total_amount'], 2) }}</td>
    </tr>
    @endforeach
</table>
@endif

<!-- EXPENSES -->
<table>
    <tr class="section"><td colspan="8">Expenses</td></tr>
    <tr><td colspan="7">Current Month</td><td class="right">{{ number_format($expenseCurrent, 2) }}</td></tr>
    <tr><td colspan="7">Previous Month</td><td class="right">{{ number_format($expensePrevious, 2) }}</td></tr>
</table>

<!-- FUEL CARD -->
@if(count($fuelCardPending) > 0)
<table>
    <tr class="section"><td colspan="8">Fuel Card (Pending)</td></tr>
    @foreach($fuelCardPending as $fc)
    <tr><td colspan="7">{{ $fc['account_name'] }} - Fuel Card</td><td class="right">{{ number_format($fc['total_fuel_card'], 2) }}</td></tr>
    @if(($fc['fuel_card_mdr'] ?? 0) > 0)
    <tr><td colspan="7">{{ $fc['account_name'] }} MDR ({{ $fc['mdr'] }}%)</td><td class="right">{{ number_format($fc['fuel_card_mdr'], 2) }}</td></tr>
    @endif
    @endforeach
</table>
@endif

<!-- CREDIT CARD -->
@if(count($creditCardPending) > 0)
<table>
    <tr class="section"><td colspan="8">Credit Card (Pending)</td></tr>
    @foreach($creditCardPending as $cc)
    <tr><td colspan="7">{{ $cc['account_name'] }} - Credit Card</td><td class="right">{{ number_format($cc['total_credit_card'], 2) }}</td></tr>
    @if(($cc['credit_card_mdr'] ?? 0) > 0)
    <tr><td colspan="7">{{ $cc['account_name'] }} MDR ({{ $cc['mdr'] }}%)</td><td class="right">{{ number_format($cc['credit_card_mdr'], 2) }}</td></tr>
    @endif
    @endforeach
</table>
@endif

<!-- SHORTAGES -->
@if(count($shortagesPending) > 0)
<table>
    <tr class="section"><td colspan="8">Shortages Pending</td></tr>
    @foreach($shortagesPending as $shortage)
    <tr>
        <td colspan="7">{{ $shortage['name'] }} - {{ number_format($shortage['total_shortage'], 2) }} Ltr</td>
        <td class="right red">{{ number_format($shortage['amount'], 2) }}</td>
    </tr>
    @endforeach
    <tr class="total-row">
        <td colspan="7">Total Shortages Amount</td>
        <td class="right red">{{ number_format($totalShortages, 2) }}</td>
    </tr>
</table>
@endif

<!-- TOTAL OUTFLOW FOOTER -->
<table>
    <tr class="heading">
        <td colspan="7"><b>TOTAL OUTFLOW</b></td>
        <td class="right"><b>{{ number_format($totalOutflow, 2) }}</b></td>
    </tr>
</table>

</body>
</html>