<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Audit Report - Station</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #e9ecef;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            padding: 40px 20px;
        }
        .report-container {
            max-width: 1280px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        .report-header {
            background: linear-gradient(135deg, #1e2a3a 0%, #0f1724 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            border-bottom: 3px solid #ffc107;
        }
        .header-title h2 {
            font-size: 1.8rem;
            letter-spacing: 1px;
        }
        .header-title p {
            color: #ced4da;
            margin-top: 5px;
        }
        .btn-pdf {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 28px;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 40px;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .btn-pdf:hover {
            background: #b02a37;
            transform: scale(1.02);
        }
        .loading-box {
            text-align: center;
            padding: 60px 20px;
            font-size: 1.2rem;
            color: #2c3e66;
        }
        .error-box {
            background: #f8d7da;
            color: #842029;
            padding: 20px;
            margin: 30px;
            border-radius: 12px;
            text-align: center;
        }
        /* ---- TABLE STYLES (exactly like your PDF) ---- */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .report-table td, .report-table th {
            border: 1px solid #000;
            padding: 8px 10px;
            vertical-align: top;
        }
        .heading-bg {
            background: #000;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }
        .section-bg {
            background: #d9d9d9;
            font-weight: bold;
        }
        .right {
            text-align: right;
        }
        .center {
            text-align: center;
        }
        .green {
            color: #008000;
            font-weight: bold;
        }
        .red {
            color: #cc0000;
            font-weight: bold;
        }
        .total-row {
            background: #f0f0f0;
            font-weight: bold;
        }
        .subnote {
            font-size: 10px;
            color: #444;
        }
        .inner-table-wrap {
            padding: 0;
        }
    </style>
</head>
<body>

<div class="report-container" id="app">
    <div class="report-header">
        <div class="header-title">
            <h2>⛽ AUDIT REPORT</h2>
            <p id="stationNameDisplay">Loading station details...</p>
        </div>
        <button class="btn-pdf" id="downloadPdfBtn" onclick="downloadAuditPDF()">
            📄 Download PDF Report
        </button>
    </div>

    <div id="reportContent" style="padding: 20px;">
        <div class="loading-box">
            ⏳ Fetching audit data from server...
        </div>
    </div>
</div>

<script>
    // Get station ID from URL (e.g., /station-audit/1)
    const stationId = window.location.pathname.split('/').pop();
    let auditData = null;
    let stationName = 'Station #' + stationId;

    // ---------- Helper: format numbers ----------
    function formatMoney(value) {
        if (value === undefined || value === null) return '0.00';
        let num = parseFloat(value);
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ---------- Render full report (same as PDF style) ----------
    function renderReport(data) {
        if (!data) return '<div class="error-box">❌ No data received from API</div>';

        // station name from station_id (fallback)
        if (data.station_name) stationName = data.station_name;
        document.getElementById('stationNameDisplay').innerHTML = `📍 ${stationName} &nbsp;|&nbsp; Report: ${new Date().toLocaleDateString()}`;

        const fuelStock = data.fuel_stock || [];
        const transit = data.stock_in_transit || [];
        const khattak = data.given_to_khattak || [];
        const shortages = data.shortages_pending || [];
        const lubeInv = data.lube_inventory || [];
        const fuelCards = data.fuel_card_pending || [];
        const creditCards = data.credit_card_pending || [];

        // Gain/loss details
        const gainLossDetails = data.gain_loss_current?.details || [];
        let lossRowsHtml = '', gainRowsHtml = '';
        gainLossDetails.forEach(item => {
            const lossLiters = item.total_loss || 0;
            const gainLiters = item.total_gain || 0;
            const rateVal = item.rate || 0;
            if (lossLiters > 0) {
                lossRowsHtml += `<tr>
                    <td colspan="5">Loss (${item.product_name}) - ${formatMoney(lossLiters)} Ltr</td>
                    <td colspan="3" class="right red">${formatMoney(lossLiters * rateVal)}</td>
                </tr>`;
            }
            if (gainLiters > 0) {
                gainRowsHtml += `<tr>
                    <td colspan="5">Gain (${item.product_name}) - ${formatMoney(gainLiters)} Ltr</td>
                    <td colspan="3" class="right green">(${formatMoney(gainLiters * rateVal)})</td>
                </tr>`;
            }
        });

        const netGainLoss = (data.gain_loss_current?.net) || 0;
        const netClass = netGainLoss > 0 ? 'red' : 'green';

        // Main HTML table (exactly matching your PDF structure)
        return `
            <table class="report-table">
                <tr class="heading-bg"><td colspan="8">AUDIT REPORT — ${stationName}</td></tr>

                <!-- WORKING CAPITAL SECTION -->
                <tr class="section-bg"><td colspan="8">Working Capital Summary</tr>
                <tr><td colspan="7">Working Capital</td><td class="right">${formatMoney(data.working_capital)}</td></tr>
                <tr><td colspan="7">Total Outflow</td><td class="right">${formatMoney(data.total_outflow)}</td></tr>
                <tr>
                    <td colspan="7"><b>Remaining / (Gain/Loss)</b><br><span class="subnote">Recovered: ${data.remaining?.recovered_percentage || 0}%</span></td>
                    <td class="right ${data.remaining?.color}">
                        ${formatMoney(data.remaining?.amount)} (${data.remaining?.status === 'profit' ? 'Profit' : 'Loss'})
                        ${data.remaining?.remaining_to_recover > 0 ? `<br><span class="subnote">Need to recover: ${formatMoney(data.remaining.remaining_to_recover)}</span>` : ''}
                    </td>
                </tr>

                <!-- FUEL STOCK -->
                <tr class="section-bg"><td colspan="8">Fuel Stock (Tanks)</tr>
                ${fuelStock.map(s => `
                    <tr>
                        <td colspan="2">${s.name}</td>
                        <td colspan="2" class="right">${formatMoney(s.total_current_level)}</td>
                        <td class="right">${formatMoney(s.rate)}</td>
                        <td colspan="3" class="right">${formatMoney(s.total_current_level * s.rate)}</td>
                    </tr>
                `).join('')}

                <!-- STOCK IN TRANSIT -->
                <tr class="section-bg"><td colspan="8">Stock in Transit</tr>
                ${transit.map(t => `
                    <tr>
                        <td colspan="2">${t.name}</td>
                        <td colspan="2" class="right">${formatMoney(t.qty)}</td>
                        <td class="right">${formatMoney(t.rate)}</td>
                        <td colspan="3" class="right">${formatMoney(t.amount)}</td>
                    </tr>
                `).join('')}

                <!-- GIVEN TO DRIVERS -->
                ${khattak.length ? `<tr class="section-bg"><td colspan="8">Given to Drivers / Vehicles</tr>
                ${khattak.map(k => `<tr><td colspan="7"><b>${k.name}</b></td><td class="right bold">${formatMoney(k.total)}</td></tr>`).join('')}` : ''}

                <!-- CASH POSITION -->
                <tr class="section-bg"><td colspan="8">Cash Position</tr>
                <tr><td colspan="7">Cash in Safe (Bank)</td><td class="right">${formatMoney(data.cash_in_safe)}</td></tr>
                <tr><td colspan="7">Cash in Hand</td><td class="right">${formatMoney(data.cash_in_hand)}</td></tr>

                <!-- GAIN/LOSS CURRENT MONTH -->
                <tr class="section-bg"><td colspan="8">Product Loss/Gain - ${data.date_ranges?.current?.[0]?.slice(0,7) || 'Current Month'}</tr>
                ${lossRowsHtml}
                ${gainRowsHtml}
                <tr class="total-row"><td colspan="7">Net Loss/Gain</td><td class="right ${netClass}">${formatMoney(netGainLoss)}</td></tr>

                <!-- LUBE INVENTORY -->
                <tr class="section-bg"><td colspan="8">Lube Inventory</tr>
                ${lubeInv.map(l => `
                    <tr>
                        <td colspan="2">${l.name}</td>
                        <td class="right">${formatMoney(l.quantity)}</td>
                        <td class="right">${formatMoney(l.avg_buying_price)}</td>
                        <td colspan="4" class="right">${formatMoney(l.total_amount)}</td>
                    </tr>
                `).join('')}

                <!-- EXPENSES -->
                <tr class="section-bg"><td colspan="8">Expenses</tr>
                <tr><td colspan="7">Current Month (${data.date_ranges?.current?.[0] || ''})</td><td class="right">${formatMoney(data.expense_current)}</td></tr>
                <tr><td colspan="7">Previous Month (${data.date_ranges?.previous?.[0] || ''})</td><td class="right">${formatMoney(data.expense_previous)}</td></tr>

                <!-- FUEL CARD PENDING -->
                ${fuelCards.length ? `<tr class="section-bg"><td colspan="8">Fuel Card (Pending)</tr>
                ${fuelCards.map(fc => `
                    <tr><td colspan="7">${fc.account_name} - Fuel Card</td><td class="right">${formatMoney(fc.total_fuel_card)}</td></tr>
                    ${fc.fuel_card_mdr ? `<tr><td colspan="7">${fc.account_name} MDR (${fc.mdr}%)</td><td class="right">${formatMoney(fc.fuel_card_mdr)}</td></tr>` : ''}
                `).join('')}` : ''}

                <!-- CREDIT CARD PENDING -->
                ${creditCards.length ? `<tr class="section-bg"><td colspan="8">Credit Card (Pending)</tr>
                ${creditCards.map(cc => `
                    <tr><td colspan="7">${cc.account_name} - Credit Card</td><td class="right">${formatMoney(cc.total_credit_card)}</td></tr>
                    ${cc.credit_card_mdr ? `<tr><td colspan="7">${cc.account_name} MDR (${cc.mdr}%)</td><td class="right">${formatMoney(cc.credit_card_mdr)}</td></tr>` : ''}
                `).join('')}` : ''}

                <!-- SHORTAGES -->
                ${shortages.length ? `<tr class="section-bg"><td colspan="8">Shortages Pending</tr>
                ${shortages.map(sh => `<tr><td colspan="7">${sh.name} - ${formatMoney(sh.total_shortage)} Ltr</td><td class="right red">${formatMoney(sh.amount)}</td></tr>`).join('')}
                <tr class="total-row"><td colspan="7">Total Shortages Amount</td><td class="right red">${formatMoney(data.total_shortages)}</td></tr>` : ''}

                <!-- TOTAL OUTFLOW FOOTER -->
                <tr class="heading-bg"><td colspan="7"><b>TOTAL OUTFLOW</b></td><td class="right"><b>${formatMoney(data.total_outflow)}</b></td></tr>
            </table>
        `;
    }

    // ----- load data from API -----
    async function loadAuditReport() {
        const contentDiv = document.getElementById('reportContent');
        try {
            const response = await fetch(`/api/currentstatus/${stationId}`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            auditData = data;
            // optional: try to fetch station name from separate endpoint? not needed, we use station_id
            if (!data.station_name && stationId) stationName = `Station ID: ${stationId}`;
            contentDiv.innerHTML = renderReport(data);
        } catch (err) {
            console.error(err);
            contentDiv.innerHTML = `<div class="error-box">⚠️ Failed to load report. Please check API connectivity or station ID.</div>`;
        }
    }

    // ----- download PDF (same API that returns PDF) -----
    window.downloadAuditPDF = function() {
        if (!stationId) return;
        window.location.href = `/api/audit-pdf/${stationId}`;
    };

    loadAuditReport();
</script>
</body>
</html>