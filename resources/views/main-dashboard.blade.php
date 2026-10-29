{{-- Dashboard data comes from the protected API, which enforces station access. --}}
@extends('partials.layouts.master')

@section('title', 'Main Dashboard | Pump 360')
@section('title-sub', 'Dashboard')
@section('pagetitle', 'Main Dashboard')

@section('css')
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
  @verbatim
    <style>
      @scope (#mainDashboard) {
        :scope {
          color-scheme: light;
          --bg: #eef1f5;
          --card: #fff;
          --ink: #101828;
          --muted: #667085;
          --line: #e6e9ef;
          --head: #f6f8fb;
          --track: #eaeef3;
          --good: #12a05c;
          --bad: #e0453f;
          --shadow:
            0 1px 2px rgba(16, 24, 40, 0.05), 0 8px 24px rgba(16, 24, 40, 0.05);
        }

        * {
          box-sizing: border-box;
          margin: 0;
        }

        :scope {
          font-family: Manrope, system-ui, sans-serif;
          background: var(--bg);
          color: var(--ink);
          font-size: 14px;
          display: flex;
          min-height: 100vh;
          width: 100%;
        }

        aside {
          width: 200px;
          background: #0f1c27;
          color: #b9c6d1;
          padding: 22px 12px;
          position: sticky;
          top: 0;
          height: 100vh;
          flex-shrink: 0;
        }

        aside h1 {
          font-size: 18px;
          color: #fff;
          padding: 0 10px 22px;
          font-weight: 800;
        }

        aside h1 span {
          color: #f59e0b;
        }

        aside a {
          display: block;
          padding: 10px 12px;
          border-radius: 10px;
          color: inherit;
          text-decoration: none;
          font-weight: 600;
          margin-bottom: 2px;
        }

        aside a.on,
        aside a:hover {
          background: #1b2d3b;
          color: #fff;
        }

        main {
          flex: 1;
          min-width: 0;
          position: relative;
        }

        .topbar {
          position: sticky;
          top: 0;
          z-index: 5;
          background: var(--bg);
          padding: 14px 26px;
          border-bottom: 1px solid var(--line);
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 12px;
          flex-wrap: wrap;
        }

        .topbar h2 {
          font-size: 20px;
          font-weight: 800;
        }

        .topbar p {
          color: var(--muted);
          font-size: 12px;
        }

        .controls {
          display: flex;
          gap: 8px;
          flex-wrap: wrap;
          align-items: center;
        }

        .controls select,
        .controls input,
        .btn {
          font: inherit;
          font-weight: 600;
          padding: 8px 11px;
          border: 1px solid var(--line);
          border-radius: 10px;
          background: var(--card);
          color: var(--ink);
        }

        .btn {
          cursor: pointer;
        }

        .btn.primary {
          background: var(--ink);
          color: var(--bg);
          border-color: var(--ink);
        }

        .range-chip {
          font-size: 12px;
          font-weight: 700;
          color: var(--muted);
          background: var(--head);
          border: 1px solid var(--line);
          border-radius: 99px;
          padding: 7px 12px;
          white-space: nowrap;
        }

        .content {
          padding: 22px 26px 40px;
        }

        .grid {
          display: grid;
          gap: 16px;
          margin-bottom: 16px;
        }

        .kpis {
          grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        }

        .insights {
          grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        }

        .products {
          grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .g2 {
          grid-template-columns: 2fr 1fr;
        }

        .g3 {
          grid-template-columns: repeat(3, 1fr);
        }

        .g22 {
          grid-template-columns: 1fr 1fr;
        }

        .card {
          background: var(--card);
          border: 1px solid var(--line);
          border-radius: 16px;
          padding: 18px;
          min-width: 0;
          box-shadow: var(--shadow);
          position: relative;
        }

        .card h3 {
          font-size: 15px;
          font-weight: 700;
          padding-right: 150px;
        }

        .sub {
          color: var(--muted);
          font-size: 12px;
          margin: 2px 0 14px;
        }

        .clickable {
          cursor: pointer;
        }

        .clickable:hover {
          box-shadow: 0 0 0 2px var(--line);
        }

        .kpi small,
        .insight small {
          color: var(--muted);
          font-weight: 600;
        }

        .kpi b {
          display: block;
          font-size: 21px;
          font-weight: 800;
          margin-top: 6px;
        }

        .insight {
          padding: 14px 16px;
          border-left: 4px solid var(--accent);
        }

        .insight b {
          display: block;
          margin: 4px 0 2px;
          font-size: 15px;
        }

        .insight span {
          font-weight: 700;
          font-size: 13px;
          color: var(--muted);
        }

        .product-card {
          border-left: 4px solid var(--accent);
        }

        .product-card b {
          display: block;
          font-size: 22px;
          margin: 6px 0 2px;
        }

        .product-card small {
          color: var(--muted);
        }

        .chart-box {
          position: relative;
          height: 250px;
        }

        .chart-box canvas,
        .drawer-chart canvas {
          display: block;
          width: 100% !important;
          height: 100% !important;
        }

        .chart-box.tall {
          height: 290px;
        }

        .is-empty .chart-box:after {
          content: "No data for this filter";
          position: absolute;
          inset: 0;
          display: flex;
          align-items: center;
          justify-content: center;
          color: var(--muted);
          background: var(--card);
        }

        .switch {
          position: absolute;
          top: 14px;
          right: 14px;
          display: flex;
          gap: 2px;
          background: var(--head);
          border: 1px solid var(--line);
          border-radius: 9px;
          padding: 2px;
        }

        .switch button {
          font: inherit;
          font-size: 11px;
          font-weight: 700;
          border: 0;
          background: none;
          color: var(--muted);
          padding: 4px 9px;
          border-radius: 7px;
          cursor: pointer;
        }

        .switch button.on {
          background: var(--ink);
          color: var(--bg);
        }

        .tank-row {
          display: flex;
          gap: 12px;
          align-items: center;
          margin-bottom: 12px;
          cursor: pointer;
        }

        .tank-name {
          width: 130px;
          font-weight: 700;
          font-size: 12px;
        }

        .tank-name small {
          display: block;
          color: var(--muted);
          font-weight: 500;
        }

        .tank-bar {
          flex: 1;
          height: 12px;
          background: var(--track);
          border-radius: 99px;
          overflow: hidden;
        }

        .tank-bar span {
          display: block;
          height: 100%;
          border-radius: 99px;
        }

        .tank-meta {
          width: 110px;
          text-align: right;
          font-size: 12px;
          color: var(--muted);
        }

        .tabs {
          display: flex;
          gap: 6px;
          margin-bottom: 12px;
          flex-wrap: wrap;
        }

        .tabs button {
          font: inherit;
          font-weight: 600;
          padding: 7px 13px;
          border-radius: 10px;
          border: 1px solid var(--line);
          background: var(--card);
          color: var(--ink);
          cursor: pointer;
        }

        .tabs button.on {
          background: var(--ink);
          color: var(--bg);
          border-color: var(--ink);
        }

        .tabs .count {
          opacity: 0.6;
          font-weight: 500;
        }

        .table-scroll {
          overflow-x: auto;
        }

        table.dataTable {
          width: 100% !important;
          border-collapse: collapse;
          font-size: 13px;
        }

        table.dataTable th,
        table.dataTable td {
          padding: 9px 10px;
          border-bottom: 1px solid var(--line);
          text-align: left;
          white-space: nowrap;
        }

        table.dataTable thead th {
          background: var(--head);
          font-weight: 700;
        }

        td.num {
          text-align: right;
        }

        .dataTables_wrapper .dataTables_filter {
          float: right;
          margin-bottom: 10px;
          color: var(--muted);
          font-size: 13px;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
          margin-top: 10px;
          font-size: 12px;
          color: var(--muted);
        }

        .dataTables_wrapper .dataTables_paginate {
          float: right;
        }

        .dataTables_wrapper input,
        .dataTables_wrapper select {
          border: 1px solid var(--line);
          border-radius: 8px;
          padding: 4px 8px;
          background: var(--card);
          color: var(--ink);
          font: inherit;
        }

        .dataTables_wrapper .paginate_button {
          padding: 4px 9px;
          margin-left: 3px;
          border: 1px solid var(--line);
          border-radius: 8px;
          cursor: pointer;
        }

        .dataTables_wrapper .paginate_button.current {
          background: var(--ink);
          color: var(--bg);
        }

        .tag {
          display: inline-block;
          padding: 2px 9px;
          border-radius: 99px;
          font-size: 12px;
          font-weight: 700;
        }

        .tag.gain,
        .tag.closed,
        .tag.income {
          background: #dcf5e8;
          color: #0f7a47;
        }

        .tag.loss,
        .tag.expense {
          background: #fde4e2;
          color: #a82a25;
        }

        .tag.open,
        .tag.balanced {
          background: #fef0d4;
          color: #a15c07;
        }

        .neg {
          color: var(--bad);
          font-weight: 700;
        }

        .pos {
          color: var(--good);
          font-weight: 700;
        }

        /* loaders */
        #pageLoader {
          position: fixed;
          inset: 0;
          z-index: 10000;
          display: grid;
          place-items: center;
          overflow: auto;
          padding: 24px;
          background: #fff;
        }

        .loader-preview {
          display: grid;
          place-items: center;
          width: 100%;
          text-align: center;
          font-family: Inter, Manrope, system-ui, sans-serif;
          color: #172033;
        }

        .loader-mark {
          width: min(540px, 90vw);
          height: auto;
          filter: drop-shadow(0 18px 28px rgba(15, 23, 42, 0.1));
        }

        .loader-progress {
          fill: none;
          stroke: url(#pump360Gradient);
          stroke-width: 6;
          stroke-linecap: round;
          stroke-dasharray: 96 205;
          transform-origin: 150px 90px;
          animation: ring-spin 1.35s cubic-bezier(0.55, 0.05, 0.35, 0.95) infinite;
        }

        .pump-body {
          animation: pump-breathe 1.6s ease-in-out infinite;
          transform-origin: 62px 92px;
        }

        .vehicle {
          animation: vehicle-idle 1.1s ease-in-out infinite;
          transform-origin: 205px 124px;
        }

        .wheel {
          animation: wheel-turn 1.4s linear infinite;
          transform-origin: center;
          transform-box: fill-box;
        }

        .fuel-flow {
          stroke-dasharray: 5 9;
          animation: fuel-flow 0.7s linear infinite;
        }

        .fuel-level {
          animation: tank-fill 2.2s ease-in-out infinite;
          transform-origin: left center;
          transform-box: fill-box;
        }

        .fuel-spark {
          animation: spark-pulse 1.1s ease-in-out infinite;
          transform-origin: center;
          transform-box: fill-box;
        }

        .loader-title {
          margin: 8px 0 5px;
          font-size: 25px;
          font-weight: 800;
          letter-spacing: -0.35px;
        }

        .loader-title span {
          color: #f97316;
        }

        .loader-message {
          margin: 0;
          color: #667085;
          font-size: 15px;
          font-weight: 600;
        }

        .loader-dots {
          display: inline-flex;
          gap: 4px;
          margin-left: 3px;
        }

        .loader-dots i {
          width: 4px;
          height: 4px;
          border-radius: 50%;
          background: #f97316;
          animation: dot-pulse 1.1s ease-in-out infinite;
        }

        .loader-dots i:nth-child(2) {
          animation-delay: 0.15s;
        }

        .loader-dots i:nth-child(3) {
          animation-delay: 0.3s;
        }

        .loading-track {
          position: relative;
          width: min(330px, 72vw);
          height: 5px;
          margin: 18px auto 15px;
          overflow: hidden;
          border-radius: 999px;
          background: #e9edf2;
        }

        .loading-track::after {
          position: absolute;
          inset: 0;
          width: 42%;
          border-radius: inherit;
          background: linear-gradient(90deg, #f97316, #fb923c, #0e8fa6);
          box-shadow: 0 0 14px rgba(249, 115, 22, 0.28);
          animation: loading-slide 1.35s cubic-bezier(0.65, 0, 0.35, 1) infinite;
          content: "";
        }

        .price-digit {
          animation: digit-pulse 1.2s steps(2) infinite;
        }

        .fuel-drop {
          animation: drop-pulse 1.2s ease-in-out infinite;
          transform-origin: center;
          transform-box: fill-box;
        }

        .fuel-drop:nth-child(2) {
          animation-delay: 0.25s;
        }

        .fuel-drop:nth-child(3) {
          animation-delay: 0.5s;
        }

        .road-line {
          stroke-dasharray: 34 20;
          animation: road-move 0.9s linear infinite;
        }

        .status-pill {
          animation: status-pulse 1.5s ease-in-out infinite;
        }

        .management-chip {
          animation: chip-float 2.4s ease-in-out infinite;
          transform-box: fill-box;
          transform-origin: center;
        }

        .management-chip:nth-of-type(2) {
          animation-delay: 0.35s;
        }

        .management-chip:nth-of-type(3) {
          animation-delay: 0.7s;
        }

        .data-pulse {
          animation: data-pulse 1.4s ease-in-out infinite;
          transform-box: fill-box;
          transform-origin: center;
        }

        .chart-bar {
          animation: chart-grow 1.6s ease-in-out infinite alternate;
          transform-box: fill-box;
          transform-origin: bottom;
        }

        .chart-bar:nth-child(2) {
          animation-delay: 0.18s;
        }

        .chart-bar:nth-child(3) {
          animation-delay: 0.36s;
        }

        .chart-bar:nth-child(4) {
          animation-delay: 0.54s;
        }

        .tank-liquid {
          animation: tank-level 2.4s ease-in-out infinite;
        }

        @keyframes ring-spin {
          to {
            transform: rotate(360deg);
          }
        }

        @keyframes pump-breathe {
          50% {
            transform: scale(0.94);
            opacity: 0.82;
          }
        }

        @keyframes vehicle-idle {
          50% {
            transform: translateY(-2px);
          }
        }

        @keyframes wheel-turn {
          to {
            transform: rotate(360deg);
          }
        }

        @keyframes fuel-flow {
          to {
            stroke-dashoffset: -28;
          }
        }

        @keyframes tank-fill {
          0%,
          100% {
            transform: scaleX(0.22);
          }

          55%,
          75% {
            transform: scaleX(1);
          }
        }

        @keyframes spark-pulse {
          50% {
            transform: scale(1.35);
            opacity: 0.45;
          }
        }

        @keyframes dot-pulse {
          0%,
          60%,
          100% {
            transform: translateY(0);
            opacity: 0.35;
          }

          30% {
            transform: translateY(-4px);
            opacity: 1;
          }
        }

        @keyframes digit-pulse {
          50% {
            opacity: 0.55;
          }
        }

        @keyframes drop-pulse {
          50% {
            transform: translateY(5px) scale(0.7);
            opacity: 0.25;
          }
        }

        @keyframes road-move {
          to {
            stroke-dashoffset: -54;
          }
        }

        @keyframes status-pulse {
          50% {
            opacity: 0.62;
          }
        }

        @keyframes loading-slide {
          from {
            transform: translateX(-105%);
          }

          to {
            transform: translateX(245%);
          }
        }

        @keyframes chip-float {
          50% {
            transform: translateY(-4px);
          }
        }

        @keyframes data-pulse {
          50% {
            transform: scale(1.45);
            opacity: 0.45;
          }
        }

        @keyframes chart-grow {
          from {
            transform: scaleY(0.45);
          }

          to {
            transform: scaleY(1);
          }
        }

        @keyframes tank-level {
          50% {
            transform: translateY(-4px);
          }
        }

        @media (prefers-reduced-motion: reduce) {
          .loader-progress,
          .pump-body,
          .vehicle,
          .wheel,
          .fuel-flow,
          .fuel-level,
          .fuel-spark,
          .loader-dots i,
          .loading-track::after {
            animation: none;
          }
        }

        .spinner {
          width: 42px;
          height: 42px;
          border-radius: 50%;
          border: 4px solid var(--line);
          border-top-color: #f59e0b;
          animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
          to {
            transform: rotate(360deg);
          }
        }

        #dataLoader {
          position: absolute;
          inset: 0;
          background: rgba(127, 127, 127, 0.18);
          z-index: 4;
          display: none;
          justify-content: center;
          padding-top: 30vh;
        }

        #errorBanner {
          display: none;
          background: #fde4e2;
          color: #a82a25;
          border: 1px solid #f3b9b5;
          padding: 12px 16px;
          border-radius: 12px;
          margin-bottom: 16px;
          font-weight: 600;
        }

        /* detail drawer */
        #drawerOverlay {
          position: fixed;
          inset: 0;
          background: rgba(10, 16, 24, 0.45);
          opacity: 0;
          pointer-events: none;
          transition: opacity 0.2s;
          z-index: 29;
        }

        #drawerOverlay.on {
          opacity: 1;
          pointer-events: auto;
        }

        #detailDrawer {
          position: fixed;
          top: calc(var(--pe-app-header-height, 74px) * 1.65);
          right: 0;
          bottom: 0;
          height: auto;
          width: min(720px, 100vw);
          box-sizing: border-box;
          background: var(--card);
          box-shadow: -12px 0 40px rgba(0, 0, 0, 0.2);
          transform: translateX(105%);
          transition: transform 0.25s;
          z-index: 30;
          overflow-y: auto;
          -webkit-overflow-scrolling: touch;
          overscroll-behavior: contain;
          padding: 20px;
        }

        #detailDrawer.on {
          transform: none;
        }

        .drawer-head {
          display: flex;
          justify-content: space-between;
          gap: 10px;
          margin-bottom: 14px;
        }

        .drawer-head h3 {
          font-size: 18px;
        }

        .drawer-head p {
          color: var(--muted);
          font-size: 12px;
          margin-top: 2px;
        }

        #drawerClose {
          font: inherit;
          cursor: pointer;
          width: 32px;
          height: 32px;
          border-radius: 8px;
          background: var(--head);
          border: 0;
          color: var(--ink);
        }

        .chips {
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 10px;
          margin-bottom: 14px;
        }

        .chips div {
          background: var(--head);
          border-radius: 12px;
          padding: 10px 12px;
        }

        .chips small {
          color: var(--muted);
          font-weight: 600;
          font-size: 11px;
        }

        .chips b {
          display: block;
          font-size: 15px;
          margin-top: 2px;
        }

        .drawer-charts {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 12px;
          margin-bottom: 14px;
        }

        .drawer-chart {
          height: 240px;
          border: 1px solid var(--line);
          border-radius: 12px;
          padding: 10px;
          display: flex;
          flex-direction: column;
          min-width: 0;
        }

        .drawer-chart h4 {
          font-size: 13px;
          margin-bottom: 6px;
        }

        .drawer-chart div {
          position: relative;
          flex: 1;
          min-height: 0;
        }

        .drawer-table-title {
          font-weight: 700;
          font-size: 14px;
          margin: 18px 0 6px;
        }

        @media (max-width: 1100px) {

          .g2,
          .g3,
          .g22 {
            grid-template-columns: 1fr;
          }
        }

        @media (max-width: 800px) {
          aside {
            display: none;
          }

          .content {
            padding: 14px;
          }

          .topbar {
            padding: 12px 14px;
          }

          .drawer-charts {
            grid-template-columns: 1fr;
          }

          .chips {
            grid-template-columns: 1fr 1fr;
          }
        }

        @media (max-width: 1199.98px) {
          #detailDrawer {
            top: var(--pe-app-header-height, 74px);
          }
        }

        @media print {

          aside,
          .controls,
          .switch,
          #detailDrawer,
          #drawerOverlay {
            display: none !important;
          }

          .card {
            break-inside: avoid;
          }
        }
      }
    </style>
  @endverbatim
@endsection

@section('content')
  <div id="mainDashboard">
    @include('partials.pump360-loader')


    <main>
      <div class="topbar">
        <div>
          <h2>Complete Overview</h2>
          <p id="dashboardSubtitle">Loading…</p>
        </div>
        <div class="controls">
          <select id="dateRangePreset" aria-label="Date range">
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="month">This month</option>
            <option value="year">This year</option>
            <option value="custom">Custom range</option>
          </select>
          <input type="date" id="fromDateInput" aria-label="From date"><input type="date" id="toDateInput"
            aria-label="To date">
          <select id="stationFilter" aria-label="Station">
            <option value="">All stations</option>
          </select>
          <select id="productFilter" aria-label="Product">
            <option value="">All products</option>
          </select>
          <span class="range-chip" id="rangeSummary"></span>
          <button class="btn primary" id="applyFiltersButton">Apply</button>
          <button class="btn" id="exportCsvButton" title="Export the open records tab">CSV</button>
          <button class="btn" id="exportExcelButton" title="Export all records tabs">Excel</button>
          <button class="btn" id="printButton">Print</button>
        </div>
      </div>

      <div id="dataLoader">
        <div class="spinner"></div>
      </div>
      <div class="content">
        <div id="errorBanner"></div>
        <div class="grid kpis" id="kpiGrid"></div>
        <div class="grid products" id="productCards"></div>

        <div class="grid g2">
          <div class="card" data-chart="salesTrend" data-types="line,bar">
            <h3>Sales trend</h3>
            <div class="sub">Litres per day and product</div>
            <div class="chart-box tall"><canvas></canvas></div>
          </div>
          <div class="card" data-chart="productShare" data-types="doughnut,pie,bar">
            <h3>Product share</h3>
            <div class="sub">Litres sold in range</div>
            <div class="chart-box tall"><canvas></canvas></div>
          </div>
        </div>
        <div class="grid g22">
          <div class="card" data-chart="topStations" data-types="bar,line,pie">
            <h3>Highest selling stations</h3>
            <div class="sub">Top 5 by litres</div>
            <div class="chart-box"><canvas></canvas></div>
          </div>
          <div class="card" data-chart="lowStations" data-types="bar,line,pie">
            <h3>Lowest selling stations</h3>
            <div class="sub">Bottom 5 by litres, needs attention</div>
            <div class="chart-box"><canvas></canvas></div>
          </div>
        </div>
        <div class="grid g2">
          <div class="card" data-chart="topNozzles" data-types="bar,line,pie">
            <h3>Top nozzles</h3>
            <div class="sub">Top 15 nozzles by litres, coloured by product</div>
            <div class="chart-box tall"><canvas></canvas></div>
          </div>
          <div class="card" data-chart="cashDistribution" data-types="doughnut,pie,bar">
            <h3>Cash distribution</h3>
            <div class="sub">Latest cash flow of each shift</div>
            <div class="chart-box tall"><canvas></canvas></div>
          </div>
        </div>

        <h3 style="margin:8px 0 12px">Stock loss and gain, income, expense and credit</h3>
        <div class="grid insights" id="insightGrid"></div>
        <div class="grid g22">
          <div class="card" data-chart="lossByStation" data-types="bar,line,pie">
            <h3>Loss by station</h3>
            <div class="sub">Stock loss value, highest first</div>
            <div class="chart-box"><canvas></canvas></div>
          </div>
          <div class="card" data-chart="gainByStation" data-types="bar,line,pie">
            <h3>Gain by station</h3>
            <div class="sub">Stock gain value, highest first</div>
            <div class="chart-box"><canvas></canvas></div>
          </div>
        </div>
        <div class="grid g22">
          <div class="card" data-chart="productLossGain" data-types="bar,line">
            <h3>Product-wise loss and gain</h3>
            <div class="sub">Value in Rs</div>
            <div class="chart-box"><canvas></canvas></div>
          </div>
          <div class="card" data-chart="tankVariance" data-types="bar,line">
            <h3>Tank-wise loss and gain</h3>
            <div class="sub">Top 12 tanks by variance, litres</div>
            <div class="chart-box"><canvas></canvas></div>
          </div>
        </div>
        <div class="grid g3">
          <div class="card" data-chart="incomeByStation" data-types="bar,line,pie">
            <h3>Income by station</h3>
            <div class="sub">Highest first</div>
            <div class="chart-box"><canvas></canvas></div>
          </div>
          <div class="card" data-chart="expenseByStation" data-types="bar,line,pie">
            <h3>Expense by station</h3>
            <div class="sub">Highest first</div>
            <div class="chart-box"><canvas></canvas></div>
          </div>
          <div class="card" data-chart="creditByStation" data-types="bar,line,pie">
            <h3>Party credit by station</h3>
            <div class="sub">Highest first</div>
            <div class="chart-box"><canvas></canvas></div>
          </div>
        </div>

        <div class="card" style="margin-bottom:16px">
          <h3 style="padding-right:0">Tank levels</h3>
          <div class="sub">Lowest 12 tanks by fill level (latest dip), click for details</div>
          <div id="tankLevelList"></div>
        </div>

        <div class="card">
          <h3 style="padding-right:0">Records</h3>
          <div class="sub">Search, sort, paginate and export</div>
          <div class="tabs" id="recordTabs"></div>
          <div class="table-scroll">
            <table id="recordTable" class="display"></table>
          </div>
        </div>
      </div>
    </main>

    <div id="drawerOverlay"></div>
    <div id="detailDrawer" role="dialog" aria-label="Details">
      <div class="drawer-head">
        <div>
          <h3 id="drawerTitle"></h3>
          <p id="drawerSubtitle"></p>
        </div><button id="drawerClose" aria-label="Close details">✕</button>
      </div>
      <div class="chips" id="drawerChips"></div>
      <div class="drawer-charts" id="drawerCharts"></div>
      <div id="drawerTables"></div>
    </div>
  </div>
@endsection

@section('js')
  <script>
    window.DashboardConfig = {
      apiUrl: @json(url('/api/main-dashboard')),
      bearerToken: @json(session('api_token')),   // leave null when the API uses the web session (cookie) auth
      defaultRangeDays: 7
    };
  </script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  @verbatim
    <script>
      (function () {
        "use strict";

        /* ============================== 1. CONSTANTS AND STATE ============================== */
        const API_URL = window.DashboardConfig.apiUrl;
        const PRODUCT_COLORS = {
          PMG: "#f59e0b",
          HSD: "#0e8fa6",
          HASRON95: "#8b5cf6",
          LPG: "#12a05c",
        };
        const COLOR_PALETTE = [
          "#0e8fa6",
          "#f59e0b",
          "#8b5cf6",
          "#12a05c",
          "#e0453f",
          "#4a90d9",
          "#ec4899",
          "#64748b",
          "#84cc16",
          "#14b8a6",
        ];
        
        const COLOR_GAIN = "#12a05c",
          COLOR_LOSS = "#e0453f";
        const KPI_DEFINITIONS = [
          { key: "litres_sold", label: "Litres sold", kind: "litres" },
          { key: "shift_count", label: "Shifts", kind: "count" },
          { key: "cash_collected", label: "Cash collected", kind: "money" },
          { key: "active_nozzles", label: "Active nozzles", kind: "count" },
          { key: "party_credit", label: "Party credit", kind: "money" },
          { key: "stock_loss", label: "Stock loss", kind: "money" },
          { key: "stock_gain", label: "Stock gain", kind: "money" },
          { key: "income", label: "Income", kind: "money" },
          { key: "expenses", label: "Expenses", kind: "money" },
          { key: "net_income", label: "Net income", kind: "money" },
        ];
        const state = {
          apiData: null,
          viewModel: null,
          recordTables: [],
          activeRecordTabIndex: 0,
          isFirstLoad: true,
          latestRequestId: 0,
          currentRequest: null,
          chartTypeByKey: {},
          chartSpecByKey: {},
          chartInstanceByKey: {},
          drawerChartInstances: [],
        };
        const el = (id) => document.getElementById(id);

        /* ============================== 2. SMALL HELPERS ============================== */
        function escapeHtml(value) {
          return String(value ?? "").replace(
            /[&<>"']/g,
            (c) =>
              ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;",
              })[c],
          );
        }
        function formatNumber(value, decimals = 0) {
          return Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
          });
        }
        function formatMoney(value) {
          return "Rs " + formatNumber(value);
        }
        function formatCompactMoney(value) {
          const amount = Number(value || 0),
            absolute = Math.abs(amount);
          if (absolute >= 1e7) return "Rs " + formatNumber(amount / 1e7, 2) + " Cr";
          if (absolute >= 1e5) return "Rs " + formatNumber(amount / 1e5, 2) + " Lac";
          return formatMoney(amount);
        }
        function formatCompactNumber(value) {
          const amount = Number(value || 0),
            absolute = Math.abs(amount);
          if (absolute >= 1e6) return (amount / 1e6).toFixed(1) + "M";
          if (absolute >= 1e3) return (amount / 1e3).toFixed(1) + "K";
          return String(Math.round(amount));
        }
        function toIsoDate(date) {
          const d = new Date(date);
          return (
            d.getFullYear() +
            "-" +
            String(d.getMonth() + 1).padStart(2, "0") +
            "-" +
            String(d.getDate()).padStart(2, "0")
          );
        }
        function sumField(rows, field) {
          return rows.reduce((total, row) => total + Number(row[field] || 0), 0);
        }
        function groupRows(rows, keyOf) {
          const groups = new Map();
          rows.forEach((row) => {
            const key = keyOf(row);
            if (!groups.has(key)) groups.set(key, []);
            groups.get(key).push(row);
          });
          return groups;
        }
        function sortedBy(rows, field, direction = "desc") {
          return [...rows].sort((a, b) =>
            direction === "desc" ? b[field] - a[field] : a[field] - b[field],
          );
        }
        function percentOf(part, whole) {
          return whole ? Math.round((part / whole) * 100) + "%" : "0%";
        }
        function shortenText(text, maxLength = 34) {
          text = String(text || "");
          return text.length > maxLength ? text.slice(0, maxLength - 1) + "…" : text;
        }
        function colorForProduct(productName) {
          return (
            PRODUCT_COLORS[productName] ||
            COLOR_PALETTE[Math.abs(hashText(productName)) % COLOR_PALETTE.length]
          );
        }
        function hashText(text) {
          let hash = 0;
          for (const ch of String(text)) hash = (hash * 31 + ch.charCodeAt(0)) | 0;
          return hash;
        }
        function rawHtml(html) {
          return { html };
        }
        function statusBadge(status) {
          return rawHtml(
            `<span class="tag ${escapeHtml(String(status).toLowerCase())}">${escapeHtml(status)}</span>`,
          );
        }

        /* ============================== 3. API CALL AND LOADERS ============================== */
        function readFiltersFromControls() {
          return {
            from_date: el("fromDateInput").value,
            to_date: el("toDateInput").value,
            station_id: el("stationFilter").value,
            product_id: el("productFilter").value,
          };
        }
        function formatReadableDate(isoDate) {
          return new Date(isoDate + "T00:00:00").toLocaleDateString(undefined, {
            day: "2-digit",
            month: "short",
            year: "numeric",
          });
        }
        function syncDateRangeControls() {
          const fromInput = el("fromDateInput"),
            toInput = el("toDateInput"),
            today = toIsoDate(new Date());
          fromInput.max = toInput.value || today;
          toInput.min = fromInput.value;
          toInput.max = today; // from <= to <= today
          if (!fromInput.value || !toInput.value) {
            el("rangeSummary").textContent = "Select from and to date";
            return;
          }
          const dayCount =
            Math.round(
              (new Date(toInput.value) - new Date(fromInput.value)) / 86400000,
            ) + 1;
          el("rangeSummary").textContent =
            `${formatReadableDate(fromInput.value)} → ${formatReadableDate(toInput.value)} · ${dayCount} day${dayCount === 1 ? "" : "s"}`;
        }
        function validateDateRange() {
          const fromDate = el("fromDateInput").value,
            toDate = el("toDateInput").value;
          if (!fromDate || !toDate) {
            showError("Please select both from date and to date.");
            return false;
          }
          if (fromDate > toDate) {
            showError("From date cannot be after to date.");
            return false;
          }
          return true;
        }
        function setLoading(isLoading) {
          if (state.isFirstLoad) {
            el("pageLoader").style.display = isLoading ? "grid" : "none";
            return;
          }
          el("dataLoader").style.display = isLoading ? "flex" : "none";
        }
        function showError(message) {
          const banner = el("errorBanner");
          banner.style.display = message ? "block" : "none";
          banner.innerHTML = message
            ? escapeHtml(message) +
            ' <button class="btn" id="retryButton" style="margin-left:10px">Retry</button>'
            : "";
          if (message) el("retryButton").onclick = loadDashboardData;
        }
        function loadDashboardData() {
          const requestId = ++state.latestRequestId;
          if (state.currentRequest) {
            state.currentRequest.abort();
            state.currentRequest = null;
          }

          syncDateRangeControls();
          if (!validateDateRange()) {
            setLoading(false);
            state.isFirstLoad = false;
            return;
          }

          const filters = readFiltersFromControls();
          const data = Object.fromEntries(
            Object.entries(filters).filter(([, value]) => value),
          );
          const headers = {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
          };
          if (window.DashboardConfig.bearerToken)
            headers.Authorization = "Bearer " + window.DashboardConfig.bearerToken;
          setLoading(true);
          showError("");

          state.currentRequest = jQuery
            .ajax({
              url: API_URL,
              method: "GET",
              data,
              dataType: "json",
              headers,
            })
            .done((apiData) => {
              if (requestId !== state.latestRequestId) return;

              state.apiData = apiData;
              state.viewModel = buildViewModel(apiData);
              renderDashboard();
            })
            .fail((xhr, status) => {
              if (status === "abort" || requestId !== state.latestRequestId) return;

              const message =
                xhr.responseJSON && xhr.responseJSON.message
                  ? xhr.responseJSON.message
                  : `Request failed (${xhr.status || "network error"})`;
              showError(message);
            })
            .always(() => {
              if (requestId !== state.latestRequestId) return;

              state.currentRequest = null;
              setLoading(false);
              state.isFirstLoad = false;
            });
        }

        /* ============================== 4. VIEW MODEL (derived numbers) ============================== */
        function buildViewModel(api) {
          const stationPerformance = api.station_performance || [];
          const stationFinance = api.station_finance || [];
          const nozzleSales = (api.nozzle_sales || []).filter(
            (n) => Number(n.litres) > 0,
          );
          const tankVariances = api.tank_variances || [];
          const tankVarianceByTank = [
            ...groupRows(tankVariances, (v) => v.station_name + "|" + v.tank_name),
          ].map(([, rows]) => ({
            stationName: rows[0].station_name,
            tankName: rows[0].tank_name,
            productName: rows[0].product_name,
            varianceLitres: sumField(rows, "variance_litres"),
            varianceValue: sumField(rows, "variance_value"),
          }));
          const lossGainByProduct = [
            ...groupRows(tankVariances, (v) => v.product_name),
          ].map(([productName, rows]) => ({
            productName,
            loss: Math.abs(
              sumField(
                rows.filter((r) => r.variance_litres < 0),
                "variance_value",
              ),
            ),
            gain: sumField(
              rows.filter((r) => r.variance_litres > 0),
              "variance_value",
            ),
          }));
          const trendDates = [
            ...new Set((api.sales_trend || []).map((r) => r.date)),
          ].sort();
          const trendProducts = [
            ...new Set((api.sales_trend || []).map((r) => r.product_name)),
          ];
          return {
            stationPerformance,
            stationFinance,
            nozzleSales,
            tankVariances,
            tankVarianceByTank,
            lossGainByProduct,
            trendDates,
            trendProducts,
          };
        }

        /* ============================== 5. CHART ENGINE ============================== */
        function colorAt(colors, index) {
          return Array.isArray(colors) ? colors[index % colors.length] : colors;
        }
        function buildChartConfiguration(chartType, spec) {
          const isCircular = chartType === "pie" || chartType === "doughnut";
          const valueAxis = spec.horizontal && chartType === "bar" ? "x" : "y";
          const format = spec.valueFormatter || formatNumber;
          const datasets = spec.datasets.map((dataset) => {
            const circularColors = spec.labels.map((_, i) =>
              Array.isArray(dataset.colors)
                ? dataset.colors[i]
                : COLOR_PALETTE[i % COLOR_PALETTE.length],
            );
            return {
              label: dataset.label,
              data: dataset.data,
              backgroundColor: isCircular ? circularColors : dataset.colors,
              borderColor:
                chartType === "line" ? colorAt(dataset.colors, 0) : undefined,
              pointBackgroundColor: chartType === "line" ? dataset.colors : undefined,
              borderRadius: chartType === "bar" ? 5 : 0,
              borderWidth: isCircular ? 0 : chartType === "line" ? 2 : 0,
              tension: 0.35,
              fill: false,
              pointRadius: chartType === "line" ? 3 : 0,
            };
          });
          const gridColor =
            getComputedStyle(
              document.getElementById("mainDashboard"),
            ).getPropertyValue("--line") || "#e6e9ef";
          return {
            type: chartType === "doughnut" ? "doughnut" : chartType,
            data: { labels: spec.labels, datasets },
            options: {
              maintainAspectRatio: false,
              indexAxis: valueAxis === "x" ? "y" : "x",
              cutout: chartType === "doughnut" ? "62%" : undefined,
              plugins: {
                legend: {
                  display: isCircular || spec.datasets.length > 1,
                  position: "bottom",
                  labels: { usePointStyle: true },
                },
                tooltip: {
                  callbacks: {
                    label: (ctx) =>
                      `${ctx.dataset.label || ctx.label}: ${format(ctx.raw)}`,
                  },
                },
              },
              scales: isCircular
                ? {}
                : {
                  [valueAxis]: {
                    grid: { color: gridColor },
                    ticks: { callback: (v) => formatCompactNumber(v) },
                  },
                  [valueAxis === "x" ? "y" : "x"]: { grid: { display: false } },
                },
              onClick: (event, elements) => {
                if (elements.length && spec.onPointClick)
                  spec.onPointClick(elements[0].index, elements[0].datasetIndex);
              },
              onHover: (event, elements) => {
                event.native.target.style.cursor =
                  elements.length && spec.onPointClick ? "pointer" : "default";
              },
            },
          };
        }
        function refreshCharts() {
          Object.keys(state.chartInstanceByKey).forEach((chartKey) => {
            const chart = state.chartInstanceByKey[chartKey];
            if (!chart || typeof chart.resize !== "function") return;
            chart.resize();
            chart.update();
          });

          state.drawerChartInstances.forEach((chart) => {
            if (chart && typeof chart.resize === "function") {
              chart.resize();
              chart.update();
            }
          });
        }

        function drawChart(chartKey, spec) {
          const card = document.querySelector(`[data-chart="${chartKey}"]`);
          const canvas = card.querySelector("canvas");
          const allowedTypes = card.dataset.types.split(",");
          const chartType = state.chartTypeByKey[chartKey] || allowedTypes[0];
          state.chartSpecByKey[chartKey] = spec;
          card.classList.toggle("is-empty", !spec.labels.length);
          if (state.chartInstanceByKey[chartKey])
            state.chartInstanceByKey[chartKey].destroy();

          const config = buildChartConfiguration(chartType, spec);
          state.chartInstanceByKey[chartKey] = new Chart(canvas, config);
          requestAnimationFrame(refreshCharts);

          if (typeof ResizeObserver !== "undefined") {
            const observer = new ResizeObserver(() => refreshCharts());
            observer.observe(card);
          }
        }
        function installChartTypeSwitches() {
          const labels = { bar: "Bar", line: "Line", pie: "Pie", doughnut: "Donut" };
          document.querySelectorAll("[data-chart]").forEach((card) => {
            const chartKey = card.dataset.chart,
              types = card.dataset.types.split(",");
            const switchBox = document.createElement("div");
            switchBox.className = "switch";
            switchBox.innerHTML = types
              .map(
                (t, i) =>
                  `<button data-type="${t}" class="${i === 0 ? "on" : ""}">${labels[t]}</button>`,
              )
              .join("");
            switchBox.onclick = (event) => {
              const button = event.target.closest("button");
              if (!button) return;
              state.chartTypeByKey[chartKey] = button.dataset.type;
              switchBox
                .querySelectorAll("button")
                .forEach((b) => b.classList.toggle("on", b === button));
              if (state.chartSpecByKey[chartKey])
                drawChart(chartKey, state.chartSpecByKey[chartKey]);
            };
            card.appendChild(switchBox);
          });
        }

        /* ============================== 6. RENDER THE PAGE ============================== */
        window.addEventListener("resize", refreshCharts);
        document.addEventListener("DOMContentLoaded", refreshCharts);

        function renderDashboard() {
          const { apiData, viewModel } = state;
          el("dashboardSubtitle").textContent =
            `${apiData.meta.from_date} to ${apiData.meta.to_date} · generated ${new Date(apiData.meta.generated_at).toLocaleString()}`;
          populateFilterOptions(apiData.filters);
          renderKpiCards(apiData.summary);
          renderProductCards(apiData.product_summary);
          renderInsightCards(viewModel);
          renderCharts(apiData, viewModel);
          renderTankLevels(apiData.tank_levels || []);
          state.recordTables = buildRecordTables(apiData, viewModel);
          renderRecordTabs();
        }
        function populateFilterOptions(filters) {
          const fill = (selectId, rows, allLabel) => {
            const select = el(selectId),
              selected = select.value;
            select.innerHTML =
              `<option value="">${allLabel}</option>` +
              rows
                .map((r) => `<option value="${r.id}">${escapeHtml(r.name)}</option>`)
                .join("");
            select.value = selected;
          };
          fill("stationFilter", filters.stations || [], "All stations");
          fill("productFilter", filters.products || [], "All products");
        }
        function formatKpiValue(definition, value) {
          if (definition.kind === "money") return formatCompactMoney(value);
          if (definition.kind === "litres") return formatNumber(value) + " L";
          return formatNumber(value);
        }
        function renderKpiCards(summary) {
          el("kpiGrid").innerHTML = KPI_DEFINITIONS.map(
            (d) =>
              `<div class="card kpi clickable" data-kpi="${d.key}" title="${escapeHtml(d.kind === "money" ? formatMoney(summary[d.key]) : "")}"><small>${d.label}</small><b>${formatKpiValue(d, summary[d.key])}</b></div>`,
          ).join("");
        }
        function renderProductCards(productSummary) {
          const totalLitres = sumField(productSummary, "litres");
          el("productCards").innerHTML = productSummary
            .map(
              (p) =>
                `<div class="card product-card clickable" data-product="${escapeHtml(p.product_name)}" style="--accent:${colorForProduct(p.product_name)}"><strong>${escapeHtml(p.product_name)}</strong><b>${formatNumber(p.litres)} L</b><small>${formatCompactMoney(p.amount)} · ${p.nozzle_count} nozzles · ${percentOf(p.litres, totalLitres)}</small></div>`,
            )
            .join("");
        }
        function renderInsightCards(vm) {
          const best = (rows, field) =>
            rows.length ? [...rows].sort((a, b) => b[field] - a[field])[0] : null;
          const stationLoss = best(vm.stationFinance, "loss"),
            stationGain = best(vm.stationFinance, "gain");
          const productLoss = best(vm.lossGainByProduct, "loss"),
            productGain = best(vm.lossGainByProduct, "gain");
          const worstTank = vm.tankVarianceByTank.length
            ? sortedBy(vm.tankVarianceByTank, "varianceValue", "asc")[0]
            : null;
          const bestTank = vm.tankVarianceByTank.length
            ? sortedBy(vm.tankVarianceByTank, "varianceValue", "desc")[0]
            : null;
          const topIncome = best(vm.stationFinance, "income"),
            topExpense = best(vm.stationFinance, "expense"),
            topCredit = best(vm.stationFinance, "credit");
          const cards = [
            [
              "Most stock loss (station)",
              stationLoss && stationLoss.loss > 0 ? stationLoss.station_name : "-",
              stationLoss ? formatCompactMoney(stationLoss.loss) : "",
              COLOR_LOSS,
              () => stationLoss && showStationDetail(stationLoss.station_name),
            ],
            [
              "Most stock gain (station)",
              stationGain && stationGain.gain > 0 ? stationGain.station_name : "-",
              stationGain ? formatCompactMoney(stationGain.gain) : "",
              COLOR_GAIN,
              () => stationGain && showStationDetail(stationGain.station_name),
            ],
            [
              "Most loss (product)",
              productLoss ? productLoss.productName : "-",
              productLoss ? formatCompactMoney(productLoss.loss) : "",
              COLOR_LOSS,
              () => productLoss && showProductDetail(productLoss.productName),
            ],
            [
              "Most gain (product)",
              productGain ? productGain.productName : "-",
              productGain ? formatCompactMoney(productGain.gain) : "",
              COLOR_GAIN,
              () => productGain && showProductDetail(productGain.productName),
            ],
            [
              "Worst tank",
              worstTank ? `${worstTank.stationName} · ${worstTank.tankName}` : "-",
              worstTank ? formatNumber(worstTank.varianceLitres) + " L" : "",
              COLOR_LOSS,
              () =>
                worstTank &&
                showTankDetail(worstTank.stationName, worstTank.tankName),
            ],
            [
              "Best tank",
              bestTank ? `${bestTank.stationName} · ${bestTank.tankName}` : "-",
              bestTank ? "+" + formatNumber(bestTank.varianceLitres) + " L" : "",
              COLOR_GAIN,
              () =>
                bestTank && showTankDetail(bestTank.stationName, bestTank.tankName),
            ],
            [
              "Highest income",
              topIncome && topIncome.income > 0 ? topIncome.station_name : "-",
              topIncome ? formatCompactMoney(topIncome.income) : "",
              "#0e8fa6",
              () => topIncome && showStationDetail(topIncome.station_name),
            ],
            [
              "Highest expense",
              topExpense && topExpense.expense > 0 ? topExpense.station_name : "-",
              topExpense ? formatCompactMoney(topExpense.expense) : "",
              "#f59e0b",
              () => topExpense && showStationDetail(topExpense.station_name),
            ],
            [
              "Highest party credit",
              topCredit && topCredit.credit > 0 ? topCredit.station_name : "-",
              topCredit ? formatCompactMoney(topCredit.credit) : "",
              "#8b5cf6",
              () => topCredit && showStationDetail(topCredit.station_name),
            ],
          ];
          el("insightGrid").innerHTML = cards
            .map(
              (c, i) =>
                `<div class="card insight clickable" data-insight="${i}" style="--accent:${c[3]}"><small>${c[0]}</small><b>${escapeHtml(c[1])}</b><span>${c[2]}</span></div>`,
            )
            .join("");
          el("insightGrid").onclick = (event) => {
            const card = event.target.closest("[data-insight]");
            if (card) cards[+card.dataset.insight][4]();
          };
        }
        function stationBarSpec(
          rows,
          nameField,
          valueField,
          color,
          valueFormatter = formatMoney,
          limit = 10,
        ) {
          const top = sortedBy(
            rows.filter((r) => r[valueField] > 0),
            valueField,
          ).slice(0, limit);
          return {
            labels: top.map((r) => r[nameField]),
            datasets: [
              {
                label: valueField,
                data: top.map((r) => Math.round(r[valueField])),
                colors: color,
              },
            ],
            horizontal: true,
            valueFormatter,
            onPointClick: (i) => showStationDetail(top[i][nameField]),
          };
        }
        function renderCharts(api, vm) {
          // sales trend: one line per product
          drawChart("salesTrend", {
            labels: vm.trendDates,
            datasets: vm.trendProducts.map((productName) => ({
              label: productName,
              colors: colorForProduct(productName),
              data: vm.trendDates.map((date) =>
                sumField(
                  api.sales_trend.filter(
                    (r) => r.date === date && r.product_name === productName,
                  ),
                  "litres",
                ),
              ),
            })),
            onPointClick: (i) => showDayDetail(vm.trendDates[i]),
          });
          const products = api.product_summary || [];
          drawChart("productShare", {
            labels: products.map((p) => p.product_name),
            datasets: [
              {
                label: "Litres",
                data: products.map((p) => p.litres),
                colors: products.map((p) => colorForProduct(p.product_name)),
              },
            ],
            onPointClick: (i) => showProductDetail(products[i].product_name),
          });
          const stationsByLitres = sortedBy(vm.stationPerformance, "litres");
          const topStations = stationsByLitres.slice(0, 5),
            lowStations = [...stationsByLitres].reverse().slice(0, 5);
          const stationSpec = (rows, color) => ({
            labels: rows.map((r) => r.station_name),
            datasets: [
              {
                label: "Litres",
                data: rows.map((r) => Math.round(r.litres)),
                colors: color,
              },
            ],
            horizontal: true,
            onPointClick: (i) => showStationDetail(rows[i].station_name),
          });
          drawChart("topStations", stationSpec(topStations, COLOR_GAIN));
          drawChart("lowStations", stationSpec(lowStations, COLOR_LOSS));
          const topNozzles = sortedBy(vm.nozzleSales, "litres").slice(0, 15);
          drawChart("topNozzles", {
            labels: topNozzles.map((n) => shortenText(n.label, 40)),
            datasets: [
              {
                label: "Litres",
                data: topNozzles.map((n) => n.litres),
                colors: topNozzles.map((n) => colorForProduct(n.product_name)),
              },
            ],
            horizontal: true,
            onPointClick: (i) => showNozzleDetail(topNozzles[i].label),
          });
          const cashRows = [
            ["Cash", api.cash_distribution.cash],
            ["Bank", api.cash_distribution.bank],
            ["Fuel card", api.cash_distribution.fuel_card],
            ["Credit card", api.cash_distribution.credit_card],
          ];
          drawChart("cashDistribution", {
            labels: cashRows.map((r) => r[0]),
            datasets: [
              {
                label: "Amount",
                data: cashRows.map((r) => r[1]),
                colors: ["#12a05c", "#4a90d9", "#f59e0b", "#8b5cf6"],
              },
            ],
            valueFormatter: formatMoney,
            onPointClick: (i) => showCashDetail(cashRows[i][0], cashRows),
          });
          drawChart(
            "lossByStation",
            stationBarSpec(vm.stationFinance, "station_name", "loss", COLOR_LOSS),
          );
          drawChart(
            "gainByStation",
            stationBarSpec(vm.stationFinance, "station_name", "gain", COLOR_GAIN),
          );
          drawChart(
            "incomeByStation",
            stationBarSpec(vm.stationFinance, "station_name", "income", "#0e8fa6"),
          );
          drawChart(
            "expenseByStation",
            stationBarSpec(vm.stationFinance, "station_name", "expense", "#f59e0b"),
          );
          drawChart(
            "creditByStation",
            stationBarSpec(vm.stationFinance, "station_name", "credit", "#8b5cf6"),
          );
          drawChart("productLossGain", {
            labels: vm.lossGainByProduct.map((r) => r.productName),
            valueFormatter: formatMoney,
            datasets: [
              {
                label: "Loss",
                data: vm.lossGainByProduct.map((r) => Math.round(r.loss)),
                colors: COLOR_LOSS,
              },
              {
                label: "Gain",
                data: vm.lossGainByProduct.map((r) => Math.round(r.gain)),
                colors: COLOR_GAIN,
              },
            ],
            onPointClick: (i) =>
              showProductDetail(vm.lossGainByProduct[i].productName),
          });
          const extremeTanks = [...vm.tankVarianceByTank]
            .sort((a, b) => Math.abs(b.varianceLitres) - Math.abs(a.varianceLitres))
            .slice(0, 12);
          drawChart("tankVariance", {
            labels: extremeTanks.map((t) =>
              shortenText(`${t.stationName} · ${t.tankName}`, 28),
            ),
            datasets: [
              {
                label: "Variance (L)",
                data: extremeTanks.map((t) => Math.round(t.varianceLitres)),
                colors: extremeTanks.map((t) =>
                  t.varianceLitres < 0 ? COLOR_LOSS : COLOR_GAIN,
                ),
              },
            ],
            onPointClick: (i) =>
              showTankDetail(extremeTanks[i].stationName, extremeTanks[i].tankName),
          });
        }
        function renderTankLevels(tankLevels) {
          const lowest = sortedBy(tankLevels, "fill_percentage", "asc").slice(0, 12);
          el("tankLevelList").innerHTML = lowest.length
            ? lowest
              .map((t, i) => {
                const percent = Math.round(t.fill_percentage || 0),
                  color =
                    percent < 20
                      ? "var(--bad)"
                      : percent < 40
                        ? "#f59e0b"
                        : "var(--good)";
                return `<div class="tank-row" data-tank-index="${i}"><div class="tank-name">${escapeHtml(t.tank_name)}<small>${escapeHtml(t.station_name)} · ${escapeHtml(t.product_name)}</small></div><div class="tank-bar"><span style="width:${percent}%;background:${color}"></span></div><div class="tank-meta">${formatNumber(t.dip_litres)} / ${formatNumber(t.capacity)} L (${percent}%)</div></div>`;
              })
              .join("")
            : '<div class="sub">No tank data for this filter</div>';
          el("tankLevelList").onclick = (event) => {
            const row = event.target.closest("[data-tank-index]");
            if (row)
              showTankDetail(
                lowest[+row.dataset.tankIndex].station_name,
                lowest[+row.dataset.tankIndex].tank_name,
              );
          };
        }

        /* ============================== 7. RECORD TABLES AND EXPORT ============================== */
        function buildRecordTables(api, vm) {
          const records = api.records || {};
          return [
            {
              title: "Nozzle readings",
              rows: records.nozzle_readings || [],
              columns: [
                ["Station", "station"],
                ["Shift", "shift"],
                ["Dispenser", "dispenser"],
                ["Nozzle", "nozzle"],
                ["Product", "product"],
                ["Opening", "opening", "number"],
                ["Closing", "closing", "number"],
                ["Litres", "litres", "number"],
                ["Rate", "rate", "number"],
                ["Amount", "amount", "money"],
              ],
            },
            {
              title: "Shifts",
              rows: records.shifts || [],
              columns: [
                ["Shift ID", "id"],
                ["Station", "station"],
                ["Shift", "shift"],
                ["Start", "start"],
                ["End", "end"],
                ["Status", "status", "badge"],
              ],
            },
            {
              title: "Shift activity",
              rows: api.shift_activity || [],
              columns: [
                ["Shift", "label"],
                ["Litres", "litres", "number"],
                ["Amount", "amount", "money"],
                ["Readings", "reading_count"],
              ],
            },
            {
              title: "Transactions",
              rows: records.transactions || [],
              columns: [
                ["Station", "station"],
                ["Shift", "shift"],
                ["Account", "account"],
                ["Type", "type", "badge"],
                ["Method", "method"],
                ["Amount", "amount", "money"],
                ["Note", "note"],
                ["Date", "date"],
              ],
            },
            {
              title: "Party credit",
              rows: records.party_credit || [],
              columns: [
                ["Station", "station"],
                ["Shift", "shift"],
                ["Customer", "customer"],
                ["Given to", "given_to"],
                ["Vehicle", "vehicle"],
                ["Amount", "amount", "money"],
                ["Date", "date"],
              ],
            },
            {
              title: "Tank loss / gain",
              rows: (records.tank_variance || []).map((r) => ({
                ...r,
                result: r.status,
              })),
              columns: [
                ["Shift", "shift_id"],
                ["Station", "station_name"],
                ["Tank", "tank_name"],
                ["Product", "product_name"],
                ["Opening", "opening_litres", "number"],
                ["Closing", "closing_litres", "number"],
                ["Received", "oil_received_litres", "number"],
                ["Nozzle sales", "nozzle_sales_litres", "number"],
                ["Variance (L)", "variance_litres", "number"],
                ["Value", "variance_value", "money"],
                ["Result", "result", "badge"],
              ],
            },
            {
              title: "Station finance",
              rows: vm.stationFinance.map((r) => ({
                ...r,
                net: r.income - r.expense,
              })),
              columns: [
                ["Station", "station_name"],
                ["Income", "income", "money"],
                ["Expense", "expense", "money"],
                ["Net", "net", "money"],
                ["Party credit", "credit", "money"],
                ["Stock loss", "loss", "money"],
                ["Stock gain", "gain", "money"],
              ],
            },
            {
              title: "Product summary",
              rows: api.product_summary || [],
              columns: [
                ["Product", "product_name"],
                ["Nozzles", "nozzle_count"],
                ["Litres", "litres", "number"],
                ["Amount", "amount", "money"],
              ],
            },
          ].map((t) => ({
            ...t,
            columns: t.columns.map(([title, field, kind]) => ({
              title,
              field,
              kind: kind || "text",
            })),
          }));
        }
        function formatRecordCell(value, kind) {
          if (value === null || value === undefined || value === "") return "";
          if (kind === "money") return formatMoney(value);
          if (kind === "number") return formatNumber(value, 2);
          if (kind === "badge")
            return `<span class="tag ${escapeHtml(String(value).toLowerCase())}">${escapeHtml(value)}</span>`;
          return escapeHtml(value);
        }
        function renderRecordTabs() {
          el("recordTabs").innerHTML = state.recordTables
            .map(
              (t, i) =>
                `<button data-tab="${i}" class="${i === state.activeRecordTabIndex ? "on" : ""}">${escapeHtml(t.title)} <span class="count">${formatNumber(t.rows.length)}</span></button>`,
            )
            .join("");
          showRecordTable(state.activeRecordTabIndex);
        }
        function showRecordTable(tabIndex) {
          state.activeRecordTabIndex = tabIndex;
          const definition = state.recordTables[tabIndex];
          el("recordTabs")
            .querySelectorAll("button")
            .forEach((b) => b.classList.toggle("on", +b.dataset.tab === tabIndex));
          if (jQuery.fn.DataTable.isDataTable("#recordTable")) {
            jQuery("#recordTable").DataTable().destroy();
            jQuery("#recordTable").empty();
          }
          jQuery("#recordTable").DataTable({
            data: definition.rows,
            pageLength: 10,
            order: [],
            language: { emptyTable: "No records for this filter" },
            columns: definition.columns.map((c) => ({
              title: c.title,
              data: c.field,
              defaultContent: "",
              className: c.kind === "number" || c.kind === "money" ? "num" : "",
              render: (value, type) =>
                type === "display" ? formatRecordCell(value, c.kind) : value,
            })),
          });
        }
        function downloadFile(fileName, blob) {
          const link = document.createElement("a");
          link.href = URL.createObjectURL(blob);
          link.download = fileName;
          link.click();
          URL.revokeObjectURL(link.href);
        }
        function recordTableToMatrix(definition) {
          return [
            definition.columns.map((c) => c.title),
            ...definition.rows.map((row) =>
              definition.columns.map((c) => row[c.field] ?? ""),
            ),
          ];
        }
        function exportActiveTableAsCsv() {
          const definition = state.recordTables[state.activeRecordTabIndex];
          if (!definition) return;
          const csv = recordTableToMatrix(definition)
            .map((row) =>
              row.map((cell) => `"${String(cell).replace(/"/g, '""')}"`).join(","),
            )
            .join("\n");
          downloadFile(
            `${definition.title.replace(/\W+/g, "_")}_${toIsoDate(new Date())}.csv`,
            new Blob(["\ufeff" + csv], { type: "text/csv;charset=utf-8" }),
          );
        }
        function exportAllTablesAsExcel() {
          const workbook = XLSX.utils.book_new();
          state.recordTables.forEach((definition) =>
            XLSX.utils.book_append_sheet(
              workbook,
              XLSX.utils.aoa_to_sheet(recordTableToMatrix(definition)),
              definition.title.replace(/[\\/?*[\]:]/g, " ").slice(0, 31),
            ),
          );
          XLSX.writeFile(workbook, `fuel_dashboard_${toIsoDate(new Date())}.xlsx`);
        }

        /* ============================== 8. DETAIL DRAWER ============================== */
        function openDetailDrawer(detail) {
          el("drawerTitle").textContent = detail.title;
          el("drawerSubtitle").textContent = detail.subtitle || "";
          el("drawerChips").innerHTML = (detail.chips || [])
            .map(
              ([label, value]) =>
                `<div><small>${escapeHtml(label)}</small><b>${escapeHtml(value)}</b></div>`,
            )
            .join("");
          state.drawerChartInstances.forEach((chart) => chart.destroy());
          state.drawerChartInstances = [];
          const charts = detail.charts || [];
          el("drawerCharts").innerHTML = charts
            .map(
              (c, i) =>
                `<div class="drawer-chart"><h4>${escapeHtml(c.title)}</h4><div><canvas id="drawerCanvas${i}"></canvas></div></div>`,
            )
            .join("");
          charts.forEach((c, i) =>
            state.drawerChartInstances.push(
              new Chart(
                el("drawerCanvas" + i),
                buildChartConfiguration(c.type || "bar", {
                  ...c,
                  onPointClick: null,
                }),
              ),
            ),
          );
          el("drawerTables").innerHTML = (detail.tables || [])
            .map((t) => {
              const head = t.columns.map((h) => `<th>${escapeHtml(h)}</th>`).join("");
              const body = t.rows
                .map(
                  (row) =>
                    "<tr>" +
                    row
                      .map(
                        (cell) =>
                          `<td>${cell && cell.html !== undefined ? cell.html : escapeHtml(cell)}</td>`,
                      )
                      .join("") +
                    "</tr>",
                )
                .join("");
              return `<div class="drawer-table-title">${escapeHtml(t.title)}</div><div class="table-scroll"><table class="display drawer-table"><thead><tr>${head}</tr></thead><tbody>${body}</tbody></table></div>`;
            })
            .join("");
          jQuery("#drawerTables .drawer-table").DataTable({
            pageLength: 5,
            lengthChange: false,
            order: [],
          });
          el("detailDrawer").classList.add("on");
          el("drawerOverlay").classList.add("on");
          requestAnimationFrame(refreshCharts);
        }
        function closeDetailDrawer() {
          el("detailDrawer").classList.remove("on");
          el("drawerOverlay").classList.remove("on");
        }
        const chartOf = (title, labels, data, colors, type = "bar", extra = {}) => ({
          title,
          labels,
          datasets: [{ label: title, data, colors }],
          type,
          ...extra,
        });
        const nozzleRowsOf = (nozzles) =>
          nozzles.map((n) => [
            n.label,
            n.product_name,
            formatNumber(n.litres),
            formatMoney(n.amount),
          ]);
        const variancesByTank = (rows) =>
          [...groupRows(rows, (v) => v.station_name + "|" + v.tank_name)].map(
            ([, r]) => ({
              stationName: r[0].station_name,
              tankName: r[0].tank_name,
              productName: r[0].product_name,
              litres: sumField(r, "variance_litres"),
              value: sumField(r, "variance_value"),
            }),
          );
        const varianceRows = (rows) =>
          rows.map((v) => [
            v.shift_id,
            v.station_name,
            v.tank_name,
            v.product_name,
            formatNumber(v.opening_litres),
            formatNumber(v.closing_litres),
            formatNumber(v.oil_received_litres),
            formatNumber(v.nozzle_sales_litres),
            formatNumber(v.variance_litres, 1),
            formatMoney(v.variance_value),
            statusBadge(v.status),
          ]);
        const VARIANCE_COLUMNS = [
          "Shift",
          "Station",
          "Tank",
          "Product",
          "Opening",
          "Closing",
          "Received",
          "Nozzle sales",
          "Variance (L)",
          "Value",
          "Result",
        ];

        function showStationDetail(stationName) {
          const { apiData, viewModel } = state,
            records = apiData.records;
          const performance = viewModel.stationPerformance.find(
            (s) => s.station_name === stationName,
          ) || { litres: 0, amount: 0 };
          const finance = viewModel.stationFinance.find(
            (s) => s.station_name === stationName,
          ) || { income: 0, expense: 0, credit: 0, loss: 0, gain: 0 };
          const nozzles = viewModel.nozzleSales.filter(
            (n) => n.station_name === stationName,
          );
          const variances = viewModel.tankVariances.filter(
            (v) => v.station_name === stationName,
          );
          const productGroups = [...groupRows(nozzles, (n) => n.product_name)].map(
            ([name, rows]) => [name, sumField(rows, "litres")],
          );
          const topNozzles = sortedBy(nozzles, "litres").slice(0, 8),
            tanks = variancesByTank(variances);
          openDetailDrawer({
            title: stationName,
            subtitle: "Station overview for the selected date range",
            chips: [
              ["Sales", formatCompactMoney(performance.amount)],
              ["Litres", formatNumber(performance.litres) + " L"],
              ["Selling nozzles", nozzles.length],
              ["Income", formatCompactMoney(finance.income)],
              ["Expense", formatCompactMoney(finance.expense)],
              ["Net", formatCompactMoney(finance.income - finance.expense)],
              ["Party credit", formatCompactMoney(finance.credit)],
              ["Stock loss", formatCompactMoney(finance.loss)],
              ["Stock gain", formatCompactMoney(finance.gain)],
            ],
            charts: [
              chartOf(
                "Litres by product",
                productGroups.map((g) => g[0]),
                productGroups.map((g) => g[1]),
                productGroups.map((g) => colorForProduct(g[0])),
                "doughnut",
              ),
              chartOf(
                "Top nozzles (litres)",
                topNozzles.map((n) =>
                  shortenText(n.label.replace(stationName, "").trim(), 22),
                ),
                topNozzles.map((n) => n.litres),
                topNozzles.map((n) => colorForProduct(n.product_name)),
              ),
              chartOf(
                "Tank variance (L)",
                tanks.map((t) => shortenText(t.tankName, 18)),
                tanks.map((t) => Math.round(t.litres)),
                tanks.map((t) => (t.litres < 0 ? COLOR_LOSS : COLOR_GAIN)),
              ),
              chartOf(
                "Income, expense, credit (Rs)",
                ["Income", "Expense", "Credit"],
                [finance.income, finance.expense, finance.credit],
                ["#0e8fa6", "#f59e0b", "#8b5cf6"],
              ),
            ],
            tables: [
              {
                title: "Nozzle sales",
                columns: ["Nozzle", "Product", "Litres", "Amount"],
                rows: nozzleRowsOf(sortedBy(nozzles, "litres")),
              },
              {
                title: "Tanks",
                columns: ["Tank", "Product", "Capacity", "Dip (L)", "Fill"],
                rows: (apiData.tank_levels || [])
                  .filter((t) => t.station_name === stationName)
                  .map((t) => [
                    t.tank_name,
                    t.product_name,
                    formatNumber(t.capacity),
                    formatNumber(t.dip_litres),
                    Math.round(t.fill_percentage) + "%",
                  ]),
              },
              {
                title: "Tank loss / gain by shift",
                columns: VARIANCE_COLUMNS,
                rows: varianceRows(variances),
              },
              {
                title: "Shifts",
                columns: ["ID", "Shift", "Start", "End", "Status"],
                rows: records.shifts
                  .filter((s) => s.station === stationName)
                  .map((s) => [
                    s.id,
                    s.shift,
                    s.start,
                    s.end || "-",
                    statusBadge(s.status),
                  ]),
              },
              {
                title: "Transactions",
                columns: ["Account", "Type", "Method", "Amount", "Note", "Date"],
                rows: records.transactions
                  .filter((t) => t.station === stationName)
                  .map((t) => [
                    t.account || "-",
                    statusBadge(t.type),
                    t.method,
                    formatMoney(t.amount),
                    t.note || "",
                    t.date,
                  ]),
              },
              {
                title: "Party credit",
                columns: ["Customer", "Given to", "Vehicle", "Amount", "Date"],
                rows: records.party_credit
                  .filter((c) => c.station === stationName)
                  .map((c) => [
                    c.customer,
                    c.given_to,
                    c.vehicle || "-",
                    formatMoney(c.amount),
                    c.date,
                  ]),
              },
            ],
          });
        }
        function showProductDetail(productName) {
          const { apiData, viewModel } = state;
          const summary = (apiData.product_summary || []).find(
            (p) => p.product_name === productName,
          ) || { litres: 0, amount: 0, nozzle_count: 0 };
          const nozzles = viewModel.nozzleSales.filter(
            (n) => n.product_name === productName,
          );
          const byStation = [...groupRows(nozzles, (n) => n.station_name)].map(
            ([name, rows]) => ({
              name,
              litres: sumField(rows, "litres"),
              amount: sumField(rows, "amount"),
            }),
          );
          const trendRows = (apiData.sales_trend || []).filter(
            (r) => r.product_name === productName,
          );
          const variances = viewModel.tankVariances.filter(
            (v) => v.product_name === productName,
          );
          const loss = Math.abs(
            sumField(
              variances.filter((v) => v.variance_litres < 0),
              "variance_value",
            ),
          ),
            gain = sumField(
              variances.filter((v) => v.variance_litres > 0),
              "variance_value",
            );
          const topStations = sortedBy(byStation, "litres").slice(0, 10);
          openDetailDrawer({
            title: productName,
            subtitle: "Product overview for the selected date range",
            chips: [
              ["Litres", formatNumber(summary.litres) + " L"],
              ["Sales", formatCompactMoney(summary.amount)],
              ["Nozzles", summary.nozzle_count],
              ["Selling nozzles", nozzles.length],
              ["Stock loss", formatCompactMoney(loss)],
              ["Stock gain", formatCompactMoney(gain)],
            ],
            charts: [
              chartOf(
                "Litres by station (top 10)",
                topStations.map((s) => shortenText(s.name, 18)),
                topStations.map((s) => s.litres),
                colorForProduct(productName),
              ),
              chartOf(
                "Daily litres",
                trendRows.map((r) => r.date),
                trendRows.map((r) => r.litres),
                colorForProduct(productName),
                "line",
              ),
            ],
            tables: [
              {
                title: "Station-wise sales",
                columns: ["Station", "Litres", "Amount"],
                rows: sortedBy(byStation, "litres").map((s) => [
                  s.name,
                  formatNumber(s.litres),
                  formatMoney(s.amount),
                ]),
              },
              {
                title: "Top nozzles",
                columns: ["Nozzle", "Product", "Litres", "Amount"],
                rows: nozzleRowsOf(sortedBy(nozzles, "litres").slice(0, 50)),
              },
              {
                title: "Tank loss / gain by shift",
                columns: VARIANCE_COLUMNS,
                rows: varianceRows(variances),
              },
            ],
          });
        }
        function showNozzleDetail(nozzleLabel) {
          const { apiData, viewModel } = state;
          const nozzle = viewModel.nozzleSales.find((n) => n.label === nozzleLabel);
          if (!nozzle) return;
          const readings = apiData.records.nozzle_readings.filter(
            (r) => `${r.station} ${r.dispenser} ${r.nozzle}` === nozzleLabel,
          );
          const stationNozzles = sortedBy(
            viewModel.nozzleSales.filter(
              (n) => n.station_name === nozzle.station_name,
            ),
            "litres",
          ).slice(0, 10);
          openDetailDrawer({
            title: nozzleLabel,
            subtitle: `${nozzle.product_name} nozzle at ${nozzle.station_name}`,
            chips: [
              ["Litres", formatNumber(nozzle.litres) + " L"],
              ["Amount", formatCompactMoney(nozzle.amount)],
              ["Readings (shifts)", readings.length],
            ],
            charts: [
              chartOf(
                "Litres per reading",
                readings.map((r, i) => "#" + (i + 1)),
                readings.map((r) => r.litres),
                colorForProduct(nozzle.product_name),
                "line",
              ),
              chartOf(
                "Other nozzles at this station",
                stationNozzles.map((n) =>
                  shortenText(n.label.replace(nozzle.station_name, "").trim(), 20),
                ),
                stationNozzles.map((n) => n.litres),
                stationNozzles.map((n) =>
                  n.label === nozzleLabel
                    ? "#101828"
                    : colorForProduct(n.product_name),
                ),
              ),
            ],
            tables: [
              {
                title: "Readings",
                columns: ["Shift", "Opening", "Closing", "Litres", "Rate", "Amount"],
                rows: readings.map((r) => [
                  r.shift,
                  formatNumber(r.opening, 2),
                  formatNumber(r.closing, 2),
                  formatNumber(r.litres, 2),
                  formatNumber(r.rate, 2),
                  formatMoney(r.amount),
                ]),
              },
            ],
          });
        }
        function showTankDetail(stationName, tankName) {
          const { apiData, viewModel } = state;
          const level = (apiData.tank_levels || []).find(
            (t) => t.station_name === stationName && t.tank_name === tankName,
          );
          const variances = viewModel.tankVariances.filter(
            (v) => v.station_name === stationName && v.tank_name === tankName,
          );
          const totalLitres = sumField(variances, "variance_litres"),
            totalValue = sumField(variances, "variance_value");
          openDetailDrawer({
            title: `${stationName} · ${tankName}`,
            subtitle: level ? `${level.product_name} tank` : "Tank loss / gain",
            chips: [
              ["Capacity", level ? formatNumber(level.capacity) + " L" : "-"],
              ["Latest dip", level ? formatNumber(level.dip_litres) + " L" : "-"],
              ["Fill level", level ? Math.round(level.fill_percentage) + "%" : "-"],
              ["Variance", formatNumber(totalLitres, 1) + " L"],
              ["Value", formatCompactMoney(totalValue)],
              [
                "Result",
                totalLitres < 0 ? "Loss" : totalLitres > 0 ? "Gain" : "Balanced",
              ],
            ],
            charts: [
              chartOf(
                "Variance per shift (L)",
                variances.map((v) => "#" + v.shift_id),
                variances.map((v) => v.variance_litres),
                variances.map((v) =>
                  v.variance_litres < 0 ? COLOR_LOSS : COLOR_GAIN,
                ),
              ),
              chartOf(
                "Closing stock per shift (L)",
                variances.map((v) => "#" + v.shift_id),
                variances.map((v) => v.closing_litres),
                "#0e8fa6",
                "line",
              ),
            ],
            tables: [
              {
                title: "Shift-wise stock calculation",
                columns: VARIANCE_COLUMNS,
                rows: varianceRows(variances),
              },
            ],
          });
        }
        function showDayDetail(date) {
          const rows = (state.apiData.sales_trend || []).filter(
            (r) => r.date === date,
          );
          openDetailDrawer({
            title: date,
            subtitle: "Sales of the day by product",
            chips: [
              ["Litres", formatNumber(sumField(rows, "litres")) + " L"],
              ["Sales", formatCompactMoney(sumField(rows, "amount"))],
              ["Products", rows.length],
            ],
            charts: [
              chartOf(
                "Litres by product",
                rows.map((r) => r.product_name),
                rows.map((r) => r.litres),
                rows.map((r) => colorForProduct(r.product_name)),
                "doughnut",
              ),
              chartOf(
                "Sales by product (Rs)",
                rows.map((r) => r.product_name),
                rows.map((r) => r.amount),
                rows.map((r) => colorForProduct(r.product_name)),
              ),
            ],
            tables: [
              {
                title: "Product-wise",
                columns: ["Product", "Litres", "Amount"],
                rows: rows.map((r) => [
                  r.product_name,
                  formatNumber(r.litres),
                  formatMoney(r.amount),
                ]),
              },
            ],
          });
        }
        function showCashDetail(selectedLabel, cashRows) {
          const total = cashRows.reduce((t, r) => t + Number(r[1]), 0),
            selected = cashRows.find((r) => r[0] === selectedLabel);
          openDetailDrawer({
            title: selectedLabel,
            subtitle: "Cash distribution (latest cash flow of every shift)",
            chips: [
              ["Amount", formatCompactMoney(selected[1])],
              ["Share", percentOf(selected[1], total)],
              ["Total collected", formatCompactMoney(total)],
            ],
            charts: [
              chartOf(
                "All payment types",
                cashRows.map((r) => r[0]),
                cashRows.map((r) => r[1]),
                ["#12a05c", "#4a90d9", "#f59e0b", "#8b5cf6"],
                "doughnut",
              ),
            ],
            tables: [
              {
                title: "Payment types",
                columns: ["Type", "Amount", "Share"],
                rows: cashRows.map((r) => [
                  r[0],
                  formatMoney(r[1]),
                  percentOf(r[1], total),
                ]),
              },
            ],
          });
        }
        function showKpiDetail(kpiKey) {
          const { apiData, viewModel } = state,
            records = apiData.records,
            summary = apiData.summary;
          const definition = KPI_DEFINITIONS.find((d) => d.key === kpiKey);
          const chips = [
            [definition.label, formatKpiValue(definition, summary[kpiKey])],
          ];
          const financeColumn = {
            party_credit: "credit",
            stock_loss: "loss",
            stock_gain: "gain",
            income: "income",
            expenses: "expense",
          }[kpiKey];
          const detail = {
            title: definition.label,
            subtitle: "Breakdown for the selected date range",
            chips,
            charts: [],
            tables: [],
          };
          if (kpiKey === "fuel_sales" || kpiKey === "litres_sold") {
            const field = kpiKey === "fuel_sales" ? "amount" : "litres",
              products = apiData.product_summary || [],
              stations = sortedBy(viewModel.stationPerformance, field).slice(0, 10);
            detail.charts = [
              chartOf(
                "By product",
                products.map((p) => p.product_name),
                products.map((p) => p[field]),
                products.map((p) => colorForProduct(p.product_name)),
                "doughnut",
              ),
              chartOf(
                "Top 10 stations",
                stations.map((s) => shortenText(s.station_name, 16)),
                stations.map((s) => s[field]),
                COLOR_GAIN,
              ),
            ];
            detail.tables = [
              {
                title: "Products",
                columns: ["Product", "Nozzles", "Litres", "Amount"],
                rows: products.map((p) => [
                  p.product_name,
                  p.nozzle_count,
                  formatNumber(p.litres),
                  formatMoney(p.amount),
                ]),
              },
              {
                title: "Stations",
                columns: ["Station", "Litres", "Amount"],
                rows: sortedBy(viewModel.stationPerformance, field).map((s) => [
                  s.station_name,
                  formatNumber(s.litres),
                  formatMoney(s.amount),
                ]),
              },
            ];
          } else if (kpiKey === "shift_count") {
            const byStation = [...groupRows(records.shifts, (s) => s.station)].map(
              ([name, rows]) => ({ name, count: rows.length }),
            ),
              top = sortedBy(byStation, "count").slice(0, 10);
            detail.charts = [
              chartOf(
                "Shifts per station (top 10)",
                top.map((s) => shortenText(s.name, 16)),
                top.map((s) => s.count),
                "#4a90d9",
              ),
            ];
            detail.tables = [
              {
                title: "Shift activity",
                columns: ["Shift", "Litres", "Amount", "Readings"],
                rows: (apiData.shift_activity || []).map((s) => [
                  s.label,
                  formatNumber(s.litres),
                  formatMoney(s.amount),
                  s.reading_count,
                ]),
              },
            ];
          } else if (kpiKey === "cash_collected") {
            return showCashDetail("Cash", [
              ["Cash", apiData.cash_distribution.cash],
              ["Bank", apiData.cash_distribution.bank],
              ["Fuel card", apiData.cash_distribution.fuel_card],
              ["Credit card", apiData.cash_distribution.credit_card],
            ]);
          } else if (kpiKey === "active_nozzles") {
            const byProduct = [
              ...groupRows(viewModel.nozzleSales, (n) => n.product_name),
            ].map(([name, rows]) => [name, rows.length]);
            detail.chips.push(["Selling nozzles", viewModel.nozzleSales.length]);
            detail.charts = [
              chartOf(
                "Selling nozzles by product",
                byProduct.map((r) => r[0]),
                byProduct.map((r) => r[1]),
                byProduct.map((r) => colorForProduct(r[0])),
                "doughnut",
              ),
            ];
            detail.tables = [
              {
                title: "Nozzles",
                columns: ["Nozzle", "Product", "Litres", "Amount"],
                rows: nozzleRowsOf(sortedBy(viewModel.nozzleSales, "litres")),
              },
            ];
          } else if (kpiKey === "stock_loss" || kpiKey === "stock_gain") {
            const isLoss = kpiKey === "stock_loss",
              tanks = variancesByTank(viewModel.tankVariances)
                .filter((t) => (isLoss ? t.litres < 0 : t.litres > 0))
                .sort((a, b) => Math.abs(b.value) - Math.abs(a.value));
            detail.charts = [
              chartOf(
                "By station (Rs)",
                ...topSeries(viewModel.stationFinance, "station_name", financeColumn),
                isLoss ? COLOR_LOSS : COLOR_GAIN,
              ),
              chartOf(
                "Top 10 tanks (L)",
                tanks
                  .slice(0, 10)
                  .map((t) => shortenText(t.stationName + " · " + t.tankName, 22)),
                tanks.slice(0, 10).map((t) => Math.abs(Math.round(t.litres))),
                isLoss ? COLOR_LOSS : COLOR_GAIN,
              ),
            ];
            detail.tables = [
              {
                title: "Tanks",
                columns: ["Station", "Tank", "Product", "Litres", "Value"],
                rows: tanks.map((t) => [
                  t.stationName,
                  t.tankName,
                  t.productName,
                  formatNumber(t.litres, 1),
                  formatMoney(Math.abs(t.value)),
                ]),
              },
            ];
          } else if (financeColumn) {
            detail.charts = [
              chartOf(
                "By station (Rs)",
                ...topSeries(viewModel.stationFinance, "station_name", financeColumn),
                "#0e8fa6",
              ),
            ];
            detail.tables = [
              {
                title: "Stations",
                columns: ["Station", "Income", "Expense", "Party credit"],
                rows: sortedBy(viewModel.stationFinance, financeColumn).map((s) => [
                  s.station_name,
                  formatMoney(s.income),
                  formatMoney(s.expense),
                  formatMoney(s.credit),
                ]),
              },
            ];
            if (kpiKey === "party_credit")
              detail.tables.push({
                title: "Party ledger",
                columns: ["Station", "Customer", "Vehicle", "Amount", "Date"],
                rows: records.party_credit.map((c) => [
                  c.station,
                  c.customer,
                  c.vehicle || "-",
                  formatMoney(c.amount),
                  c.date,
                ]),
              });
            else
              detail.tables.push({
                title: "Transactions",
                columns: ["Station", "Account", "Type", "Amount", "Date"],
                rows: records.transactions
                  .filter((t) =>
                    kpiKey === "income" ? t.type === "income" : t.type === "expense",
                  )
                  .map((t) => [
                    t.station,
                    t.account || "-",
                    statusBadge(t.type),
                    formatMoney(t.amount),
                    t.date,
                  ]),
              });
          } else if (kpiKey === "net_income") {
            const stations = sortedBy(
              viewModel.stationFinance.map((s) => ({
                ...s,
                net: s.income - s.expense,
              })),
              "net",
              "asc",
            ).slice(0, 10);
            detail.charts = [
              chartOf(
                "Lowest net income (Rs)",
                stations.map((s) => shortenText(s.station_name, 16)),
                stations.map((s) => s.net),
                stations.map((s) => (s.net < 0 ? COLOR_LOSS : COLOR_GAIN)),
              ),
            ];
            detail.tables = [
              {
                title: "Stations",
                columns: ["Station", "Income", "Expense", "Net"],
                rows: viewModel.stationFinance.map((s) => [
                  s.station_name,
                  formatMoney(s.income),
                  formatMoney(s.expense),
                  formatMoney(s.income - s.expense),
                ]),
              },
            ];
          }
          openDetailDrawer(detail);
        }
        function topSeries(rows, nameField, valueField, limit = 10) {
          const top = sortedBy(
            rows.filter((r) => r[valueField] > 0),
            valueField,
          ).slice(0, limit);
          return [
            top.map((r) => shortenText(r[nameField], 16)),
            top.map((r) => Math.round(r[valueField])),
          ];
        }

        /* ============================== 9. EVENTS AND STARTUP ============================== */
        function applyDateRangePreset(preset) {
          const today = new Date(),
            fromInput = el("fromDateInput"),
            toInput = el("toDateInput");
          toInput.value = toIsoDate(today);
          if (preset === "7")
            fromInput.value = toIsoDate(
              new Date(today.getFullYear(), today.getMonth(), today.getDate() - 6),
            );
          if (preset === "30")
            fromInput.value = toIsoDate(
              new Date(today.getFullYear(), today.getMonth(), today.getDate() - 29),
            );
          if (preset === "month")
            fromInput.value = toIsoDate(
              new Date(today.getFullYear(), today.getMonth(), 1),
            );
          if (preset === "year")
            fromInput.value = toIsoDate(new Date(today.getFullYear(), 0, 1));
          syncDateRangeControls();
        }
        function bindEvents() {
          el("dateRangePreset").onchange = (event) => {
            if (event.target.value !== "custom") {
              applyDateRangePreset(event.target.value);
              loadDashboardData();
            }
          };
          ["fromDateInput", "toDateInput"].forEach((id) => {
            el(id).onchange = () => {
              el("dateRangePreset").value = "custom";
              syncDateRangeControls();
            };
          });
          ["stationFilter", "productFilter"].forEach((id) => {
            el(id).onchange = loadDashboardData;
          });
          el("applyFiltersButton").onclick = loadDashboardData;
          el("exportCsvButton").onclick = exportActiveTableAsCsv;
          el("exportExcelButton").onclick = exportAllTablesAsExcel;
          el("printButton").onclick = () => window.print();
          el("kpiGrid").onclick = (event) => {
            const card = event.target.closest("[data-kpi]");
            if (card) showKpiDetail(card.dataset.kpi);
          };
          el("productCards").onclick = (event) => {
            const card = event.target.closest("[data-product]");
            if (card) showProductDetail(card.dataset.product);
          };
          el("recordTabs").onclick = (event) => {
            const tab = event.target.closest("[data-tab]");
            if (tab) showRecordTable(+tab.dataset.tab);
          };
          el("drawerClose").onclick = el("drawerOverlay").onclick = closeDetailDrawer;
          document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") closeDetailDrawer();
          });
        }
        Chart.defaults.font.family = "Manrope, system-ui, sans-serif";
        installChartTypeSwitches();
        bindEvents();
        el("dateRangePreset").value = String(window.DashboardConfig.defaultRangeDays);
        applyDateRangePreset(el("dateRangePreset").value);
        loadDashboardData();
      })();
    </script>
  @endverbatim
@endsection
