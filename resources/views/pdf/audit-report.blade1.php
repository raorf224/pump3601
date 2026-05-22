<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            border: 1px solid #000;
            padding: 5px;
        }

        .heading {
            background: #000;
            color: #fff;
            font-weight: bold;
        }

        .section {
            background: #d9d9d9;
            font-weight: bold;
        }

        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>

<body>

@php
    $stations = $data['stations'];
    $transit = $data['transit'];
    $lube = $data['lube'];

    $givenToKhattak = $data['giventokhattak'];
    $in_bank = $data['in_bank'];
    $in_hand = $data['in_hand'];

    $fuelcard = $data['fuelcard'];
    $creditcard = $data['creditcard'];

    $gainloss = $data['gain_loss']['summary'];
    $expense = $data['expense'];

    // =========================
    // WORKING CAPITAL LOGIC
    // =========================
    $workingCapital = $data['Workingcapital'][0]['working_capital'] ?? 0;

    $totalOutflow = ($expense['current_month'] ?? 0) + ($expense['previous_month'] ?? 0);

    $remaining = $workingCapital - $totalOutflow;
	    $productRates = [];
	foreach ($stations as $station) {
        $productRates[$station['name']] = $station['rate'];
    }
@endphp

<table>

<!-- HEADER -->
<tr class="heading">
    <td colspan="6">Audit Report</td>
    <td colspan="2" class="center">{{ now()->format('d M Y') }}</td>
</tr>

<!-- ===================================== -->
<!-- REMAINING -->
<!-- ===================================== -->

<!-- ===================================== -->
<!-- GAIN / LOSS -->
<!-- ===================================== -->

@php
    $gainLoss = $totalOutflow - $workingCapital;

    $gainLossColor = $totalOutflow > $workingCapital
        ? 'green'
        : 'red';
@endphp

<tr>
    <td colspan="7"><b>Gain / Loss</b></td>

    <td class="right"
        style="color:{{ $gainLossColor }}; font-weight:bold;">

        {{ number_format(abs($gainLoss), 2) }}

    </td>
</tr>

<!-- ===================================== -->
<!-- WORKING CAPITAL -->
<!-- ===================================== -->

<tr>
    <td colspan="7"><b>Working Capital</b></td>
    <td class="right">
        {{ number_format($workingCapital, 2) }}
    </td>
</tr>

<!-- ===================================== -->
<!-- FUEL STOCK -->
<!-- ===================================== -->

<tr class="section">
    <td colspan="8">Fuel Stock</td>
</tr>

@foreach($stations as $station)
<tr>
    <td colspan="2">{{ $station['name'] }}</td>
    <td colspan="2" class="right">{{ number_format($station['total_current_level'], 2) }}</td>
    <td class="right">{{ number_format($station['rate'], 2) }}</td>
    <td colspan="3" class="right">
        {{ number_format($station['total_current_level'] * $station['rate'], 2) }}
    </td>
</tr>
@endforeach

@foreach($transit as $item)
<tr>
    <td colspan="2">Transit - {{ $item['name'] }}</td>
    <td colspan="2" class="right">{{ number_format($item['qty'], 2) }}</td>
    <td class="right">{{ number_format($item['rate'], 2) }}</td>
    <td colspan="3" class="right">
        {{ number_format($item['ammount'], 2) }}
    </td>
</tr>
@endforeach

<!-- CASH INSIDE FUEL STOCK -->
@foreach($givenToKhattak as $item)
<tr>
    <td colspan="7"><b>Given to {{$item['name'] }}</b></td>
    <td class="right">{{ number_format($item['total'] ?? 0, 2) }}</td>
</tr>
	@endforeach

<tr>
    <td colspan="7"><b>Cash in Bank</b></td>
    <td class="right">{{ number_format($in_bank[0]['amount'] ?? 0, 2) }}</td>
</tr>

<tr>
    <td colspan="7"><b>Cash in Hand & Safe</b></td>
    <td class="right">{{ number_format($in_hand[0]['cash_return'] ?? 0, 2) }}</td>
</tr>

<!-- ===================================== -->
<!-- FUEL CARD -->
<!-- ===================================== -->

<tr class="section">
    <td colspan="8">Fuel Card</td>
</tr>

@foreach($fuelcard as $row)

<tr>
    <td colspan="7">{{ $row['account_name'] }} - Fuel Card</td>
    <td class="right">{{ number_format($row['total_fuel_card'], 2) }}</td>
</tr>

<tr>
    <td colspan="7">{{ $row['account_name'] }} MDR ({{ $row['mdr'] }}%)</td>
    <td class="right">{{ number_format($row['fuel_card_mdr'], 2) }}</td>
</tr>

@endforeach

<!-- ===================================== -->
<!-- CREDIT CARD -->
<!-- ===================================== -->

<tr class="section">
    <td colspan="8">Credit Card</td>
</tr>

@foreach($creditcard as $row)

<tr>
    <td colspan="7">{{ $row['account_name'] }} - Credit Card</td>
    <td class="right">{{ number_format($row['total_credit_card'], 2) }}</td>
</tr>

<tr>
    <td colspan="7">{{ $row['account_name'] }} MDR ({{ $row['mdr'] }}%)</td>
    <td class="right">{{ number_format($row['credit_card_mdr'], 2) }}</td>
</tr>

@endforeach

<!-- ===================================== -->
<!-- GAIN / LOSS -->
<!-- ===================================== -->

<tr class="section">
    <td colspan="8">Gain / Loss</td>
</tr>

@foreach($gainloss as $g)

@php
    $rate = $productRates[$g['product']] ?? 0;

    $lossAmount = $g['total_loss'] * $rate;
    $gainAmount = $g['total_gain'] * $rate;
@endphp

@if($g['total_loss'] > 0)
<tr>
    <td colspan="5">
        Loss ({{ $g['product'] }})
        - {{ number_format($g['total_loss'], 2) }} Ltr
        × {{ number_format($rate, 2) }}
    </td>

    <td colspan="3" class="right">
        {{ number_format($lossAmount, 2) }}
    </td>
</tr>
@endif

@if($g['total_gain'] > 0)
<tr>
    <td colspan="5">
        Gain ({{ $g['product'] }})
        - {{ number_format($g['total_gain'], 2) }} Ltr
        × {{ number_format($rate, 2) }}
    </td>

    <td colspan="3" class="right">
        ({{ number_format($gainAmount, 2) }})
    </td>
</tr>
@endif



@endforeach

<!-- ===================================== -->
<!-- LUBE -->
<!-- ===================================== -->

<tr class="section">
    <td colspan="8">Lube</td>
</tr>

@foreach($lube as $item)
<tr>
    <td colspan="2">{{ $item['name'] }}</td>
    <td class="right">{{ number_format($item['qty'], 2) }}</td>
    <td class="right">{{ number_format($item['unit_price'], 2) }}</td>
    <td colspan="4" class="right">{{ number_format($item['line_amount'], 2) }}</td>
</tr>
@endforeach

<!-- ===================================== -->
<!-- EXPENSE -->
<!-- ===================================== -->

<tr class="section">
    <td colspan="8">Expense</td>
</tr>

<tr>
    <td colspan="7">Current Month</td>
    <td class="right">{{ number_format($expense['current_month'], 2) }}</td>
</tr>

<tr>
    <td colspan="7">Previous Month</td>
    <td class="right">{{ number_format($expense['previous_month'], 2) }}</td>
</tr>

<!-- FOOTER TOTAL OUTFLOW -->
<tr class="heading">
    <td colspan="7">Total Outflow</td>
    <td class="right">
        {{ number_format($totalOutflow, 2) }}
    </td>
</tr>

</table>

</body>
</html>