@extends('partials.layouts.master')

@section('title', 'Dashboard |')

@section('title-sub', 'Admin')
@section('pagetitle', 'Stations')
@section('css')
  <link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
  <style>
    /* :root {
          --bg: #eef1f5;
          --card: #fff;
          --ink: #101828;
          --mut: #667085;
          --line: #e6e9ef;
          --head: #f6f8fb;
          --track: #eaeef3;
          --glass: rgba(238, 241, 245, .85);
          --sh: 0 1px 2px rgba(16, 24, 40, .05), 0 8px 24px rgba(16, 24, 40, .05);
          --good: #12a05c;
          --bad: #e0453f;
          box-sizing: border-box;
          padding-top: env(safe-area-inset-top, 0px);
          padding-bottom: env(safe-area-inset-bottom, 0px)
        }

        @media(prefers-color-scheme:dark) {
          :root:not([data-theme="light"]) {
            --bg: #0c131a;
            --card: #16212b;
            --ink: #e8edf1;
            --mut: #92a0ad;
            --line: #243240;
            --head: #1b2834;
            --track: #243240;
            --glass: rgba(12, 19, 26, .85);
            --sh: none
          }
        } */

    #pump360-main-dashboard {
      --bg: #eef1f5;
      --card: #fff;
      --ink: #101828;
      --mut: #667085;
      --line: #e6e9ef;
      --head: #f6f8fb;
      --track: #eaeef3;
      --glass: rgba(238, 241, 245, .85);
      --sh: 0 1px 2px rgba(16, 24, 40, .05), 0 8px 24px rgba(16, 24, 40, .05);
      --good: #12a05c;
      --bad: #e0453f;
    }

    :root[data-theme="dark"] #pump360-main-dashboard {
      --bg: #0c131a;
      --card: #16212b;
      --ink: #e8edf1;
      --mut: #92a0ad;
      --line: #243240;
      --head: #1b2834;
      --track: #243240;
      --glass: rgba(12, 19, 26, .85);
      --sh: none
    }

    #pump360-main-dashboard {
      box-sizing: border-box;
      margin: 0;
    }

    #pump360-main-dashboard *,
    #pump360-main-dashboard *::before,
    #pump360-main-dashboard *::after {
      box-sizing: border-box
    }

    #pump360-main-dashboard {
      font-family: Manrope, system-ui, sans-serif;
      background: var(--bg);
      color: var(--ink);
      font-size: 14px;
      display: block;
      min-height: 100vh
    }

    #pump360-main-dashboard aside {
      width: 200px;
      background: #0f1c27;
      color: #b9c6d1;
      padding: 22px 12px;
      position: sticky;
      top: 0;
      height: 100vh;
      flex-shrink: 0
    }

    #pump360-main-dashboard aside h1 {
      font-size: 18px;
      color: #fff;
      padding: 0 10px 22px;
      font-weight: 800
    }

    #pump360-main-dashboard aside h1 span {
      color: #f59e0b
    }

    #pump360-main-dashboard aside a {
      display: block;
      padding: 10px 12px;
      border-radius: 10px;
      color: inherit;
      text-decoration: none;
      font-weight: 600;
      margin-bottom: 2px
    }

    #pump360-main-dashboard aside a:hover,
    #pump360-main-dashboard aside a.on {
      background: #1b2d3b;
      color: #fff
    }

    #pump360-main-dashboard main {
      flex: 1;
      min-width: 0
    }

    .bar0 {
      position: sticky;
      top: 0;
      z-index: 5;
      background: var(--glass);
      backdrop-filter: blur(10px);
      padding: 14px 26px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      border-bottom: 1px solid var(--line)
    }

    .bar0 h2 {
      font-size: 20px;
      font-weight: 800
    }

    .bar0 p {
      color: var(--mut);
      font-size: 12px
    }

    .fl {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center
    }

    #pump360-main-dashboard select {
      font: inherit;
      font-weight: 600;
      padding: 9px 12px;
      border: 1px solid var(--line);
      border-radius: 10px;
      background: var(--card);
      color: var(--ink)
    }

    .seg {
      display: flex;
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 10px;
      padding: 3px;
      gap: 2px
    }

    .seg button {
      font: inherit;
      font-weight: 600;
      border: 0;
      background: none;
      color: var(--mut);
      padding: 6px 12px;
      border-radius: 8px;
      cursor: pointer
    }

    .seg button.on {
      background: #101828;
      color: #fff
    }

    .body {
      padding: 22px 26px 40px
    }

    .grid {
      display: grid;
      gap: 16px;
      margin-bottom: 16px
    }

    .kpis {
      grid-template-columns: repeat(auto-fit, minmax(165px, 1fr))
    }

    .pcs {
      grid-template-columns: repeat(3, 1fr)
    }

    .g2 {
      grid-template-columns: 2fr 1fr
    }

    .g3 {
      grid-template-columns: repeat(3, 1fr)
    }

    .g22 {
      grid-template-columns: 1fr 1fr
    }

    .card {
      background: var(--card);
      border-radius: 16px;
      padding: 18px;
      min-width: 0;
      box-shadow: var(--sh);
      border: 1px solid var(--line)
    }

    .card h3 {
      font-size: 15px;
      font-weight: 700
    }

    .sub {
      color: var(--mut);
      font-size: 12px;
      margin: 2px 0 14px
    }

    .kpi small {
      color: var(--mut);
      font-weight: 600
    }

    .kpi b {
      display: block;
      font-size: 22px;
      font-weight: 800;
      margin-top: 6px
    }

    .pc {
      all: unset;
      box-sizing: border-box;
      cursor: pointer;
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 16px;
      padding: 16px 18px;
      box-shadow: var(--sh);
      position: relative;
      overflow: hidden;
      display: block
    }

    .pc span {
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 8px
    }

    .pc span:before {
      content: "";
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: var(--c)
    }

    .pc b {
      display: block;
      font-size: 24px;
      font-weight: 800;
      margin: 8px 0 2px
    }

    .pc small {
      color: var(--mut);
      font-size: 12px
    }

    .pc i {
      position: absolute;
      left: 0;
      bottom: 0;
      height: 4px;
      background: var(--c)
    }

    .pc.on {
      outline: 2px solid var(--c);
      outline-offset: -2px
    }

    .ch {
      position: relative;
      height: 330px
    }

    .ch.sm {
      height: 280px
    }

    .tank {
      display: flex;
      gap: 12px;
      align-items: center;
      margin-bottom: 14px;
      cursor: pointer;
      border-radius: 8px;
      padding: 4px;
      transition: background .15s
    }

    .tank:hover {
      background: var(--head)
    }

    .tank-view-all {
      width: 100%;
      margin-top: 8px;
      padding: 9px 12px;
      border: 1px solid var(--line);
      border-radius: 9px;
      background: var(--head);
      color: var(--ink);
      font: inherit;
      font-weight: 700;
      cursor: pointer;
    }

    .tank:last-child {
      margin-bottom: 0
    }

    .tank .nm {
      width: 84px;
      font-weight: 700
    }

    .tank .nm small {
      display: block;
      color: var(--mut);
      font-weight: 500
    }

    .bar {
      flex: 1;
      height: 12px;
      background: var(--track);
      border-radius: 99px;
      overflow: hidden
    }

    .bar span {
      display: block;
      height: 100%;
      border-radius: 99px
    }

    .tank .lv {
      width: 116px;
      text-align: right;
      font-size: 12px;
      color: var(--mut)
    }

    .tag {
      display: inline-block;
      padding: 2px 9px;
      border-radius: 99px;
      font-size: 12px;
      font-weight: 700
    }

    .t-Petrol,
    .s-Open {
      background: #fef0d4;
      color: #a15c07
    }

    .t-Diesel {
      background: #d9f0f4;
      color: #0b5b6b
    }

    .t-Hi-Octane {
      background: #ece4fd;
      color: #5b34b0
    }

    .s-Closed {
      background: #dcf5e8;
      color: #0f7a47
    }

    .neg {
      color: var(--bad);
      font-weight: 700
    }

    .ins {
      grid-template-columns: repeat(auto-fit, minmax(185px, 1fr))
    }

    .ins .card {
      padding: 14px 16px
    }

    .ins b {
      display: block;
      font-size: 15px;
      margin: 4px 0 2px
    }

    .ins span {
      font-weight: 700;
      font-size: 13px;
      color: var(--mut)
    }

    .sw {
      position: absolute;
      top: 14px;
      right: 14px;
      display: flex;
      gap: 2px;
      background: var(--head);
      border: 1px solid var(--line);
      border-radius: 9px;
      padding: 2px
    }

    .sw button {
      font: inherit;
      font-size: 11px;
      font-weight: 700;
      border: 0;
      background: none;
      color: var(--mut);
      padding: 4px 9px;
      border-radius: 7px;
      cursor: pointer
    }

    .sw button.on {
      background: #101828;
      color: #fff
    }

    .lvb {
      display: flex;
      align-items: center;
      gap: 6px;
      font-weight: 700;
      font-size: 12px;
      color: var(--good)
    }

    .lvb i {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--good);
      animation: pu 1.4s infinite
    }

    @keyframes pu {
      50% {
        opacity: .25
      }
    }

    .lvb.off {
      color: var(--mut)
    }

    .lvb.off i {
      background: var(--mut);
      animation: none
    }

    @media(prefers-reduced-motion:reduce) {
      .lvb i {
        animation: none
      }

      .feed .nw {
        animation: none
      }
    }

    .lpb {
      font: inherit;
      font-weight: 600;
      padding: 8px 12px;
      border: 1px solid var(--line);
      border-radius: 10px;
      background: var(--card);
      color: var(--ink);
      cursor: pointer
    }

    .feed {
      overflow-x: auto
    }

    .feed div {
      display: grid;
      grid-template-columns: 90px 1.4fr 70px 100px 90px 120px 80px;
      gap: 8px;
      padding: 9px 6px;
      border-bottom: 1px solid var(--line);
      cursor: pointer;
      align-items: center;
      min-width: 660px;
      font-size: 13px
    }

    .feed .nw {
      animation: fl 1.8s
    }

    @keyframes fl {
      from {
        background: #fff3c9;
        color: #101828
      }
    }

    .hm {
      border-collapse: separate;
      border-spacing: 3px;
      font-size: 12px;
      width: 100%
    }

    .hm td {
      text-align: center;
      padding: 9px 4px;
      border-radius: 6px;
      min-width: 42px;
      cursor: pointer;
      font-weight: 600
    }

    .hm th {
      font-weight: 600;
      color: var(--mut);
      padding: 4px;
      text-align: left;
      white-space: nowrap
    }

    .hm td:hover {
      outline: 2px solid var(--ink)
    }

    .kpi,
    .ins .card,
    .pc {
      cursor: pointer
    }

    .kpi:hover,
    .ins .card:hover {
      box-shadow: 0 0 0 2px var(--line)
    }
    /* cavas width width  */
    .dr {
      position: fixed;
      top: 0;
      right: 0;
      height: 100%;
      width: 70vw;
      max-width: 100vw;
      background: var(--card);
      box-shadow: -12px 0 40px rgba(0, 0, 0, .2);
      transform: translateX(105%);
      transition: transform .25s;
      z-index: 10040;
      overflow-y: auto;
      padding: 26px;
      padding-top: calc(26px + env(safe-area-inset-top, 0px))
    }

    .dr.on {
      transform: none
    }

    .ov {
      position: fixed;
      inset: 0;
      background: rgba(10, 16, 24, .45);
      opacity: 0;
      pointer-events: none;
      transition: opacity .2s;
      z-index: 10030
    }

    .ov.on {
      opacity: 1;
      pointer-events: auto
    }

    .dh {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 20px
    }

    .dh h3 {
      font-size: 18px
    }

    .dh p {
      color: var(--mut);
      font-size: 12px;
      margin-top: 2px
    }

    #dX {
      all: unset;
      cursor: pointer;
      width: 32px;
      height: 32px;
      text-align: center;
      line-height: 32px;
      border-radius: 8px;
      background: var(--head);
      flex-shrink: 0
    }

    .chips {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
      margin-bottom: 22px
    }

    .chips div {
      background: var(--head);
      border-radius: 12px;
      padding: 14px 16px;
      min-height: 72px
    }

    .chips small {
      color: var(--mut);
      font-weight: 600;
      font-size: 11px
    }

    .chips b {
      display: block;
      font-size: 16px;
      margin-top: 2px
    }

    .dbox {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
      margin-bottom: 22px
    }

    .dbox .cb {
      height: 250px;
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 16px;
      display: flex;
      flex-direction: column;
      min-width: 0
    }

    .dbox .cv {
      position: relative;
      flex: 1;
      min-height: 0
    }

    .dbox h4 {
      font-size: 14px;
      margin-bottom: 12px
    }

    .dtt {
      font-weight: 700;
      font-size: 14px;
      margin: 18px 0 6px
    }

    @media(max-width:640px) {
      .dbox {
        grid-template-columns: 1fr
      }

      .chips {
        grid-template-columns: 1fr 1fr
      }

      .dr {
        width: 100vw;
        padding: 16px
      }

      .dbox .cb {
        height: 290px
      }
    }

    #dTb table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px
    }

    #dTb th,
    #dTb td {
      padding: 8px 6px;
      border-bottom: 1px solid var(--line);
      text-align: left
    }

    #dTb th {
      color: var(--mut);
      font-weight: 700
    }

    #dTb .dtt + div {
      max-height: 330px;
      overflow: auto !important;
      border: 1px solid var(--line);
      border-radius: 10px;
    }

    #dTb th {
      position: sticky;
      top: 0;
      z-index: 1;
      background: var(--card);
    }

    .tabs {
      display: flex;
      gap: 6px;
      margin-bottom: 12px;
      flex-wrap: wrap
    }

    .tabs button {
      font: inherit;
      font-weight: 600;
      padding: 7px 13px;
      border-radius: 10px;
      border: 1px solid var(--line);
      background: var(--card);
      color: var(--ink);
      cursor: pointer
    }

    .tabs button.on {
      background: #101828;
      color: #fff;
      border-color: #101828
    }

    .pane {
      display: none;
      overflow-x: auto
    }

    .pane.on {
      display: block
    }

    #pump360-main-dashboard table.dataTable,
    #pump360-main-dashboard table.dt2 {
      width: 100% !important;
      border-collapse: collapse;
      font-size: 13px
    }

    #pump360-main-dashboard table.dataTable th,
    #pump360-main-dashboard table.dataTable td,
    #pump360-main-dashboard table.dt2 th,
    #pump360-main-dashboard table.dt2 td {
      padding: 11px 14px;
      border-bottom: 1px solid var(--line);
      text-align: left;
      white-space: nowrap
    }

    #pump360-main-dashboard table.dataTable thead th,
    #pump360-main-dashboard table.dt2 thead th {
      background: var(--head);
      font-weight: 700
    }

    #pump360-main-dashboard table.dt2 {
      min-width: 980px;
    }

    #pump360-main-dashboard table.dt2 tbody tr:hover {
      background: var(--head);
    }

    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length {
      margin-bottom: 10px;
      font-size: 13px;
      color: var(--mut)
    }

    .dataTables_wrapper .dataTables_filter {
      float: right
    }

    .dataTables_wrapper input,
    .dataTables_wrapper select {
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 4px 8px;
      background: var(--card);
      color: var(--ink);
      font: inherit
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
      margin-top: 10px;
      font-size: 12px;
      color: var(--mut)
    }

    .dataTables_wrapper .dataTables_paginate {
      float: right
    }

    .dataTables_wrapper .paginate_button {
      padding: 4px 9px;
      margin-left: 3px;
      border: 1px solid var(--line);
      border-radius: 8px;
      cursor: pointer
    }

    .dataTables_wrapper .paginate_button.current {
      background: #101828;
      color: #fff
    }

    .dataTables_wrapper .paginate_button.disabled {
      opacity: .4;
      cursor: default
    }

    .dashboard-table-tools,
    .dashboard-table-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      margin: 10px 0;
      color: var(--mut);
      font-size: 12px;
    }

    .dashboard-table-tools input,
    .dashboard-table-tools select,
    .dashboard-table-footer button {
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 7px 10px;
      background: var(--card);
      color: var(--ink);
      font: inherit;
    }

    .dashboard-table-footer button:not(:disabled) {
      cursor: pointer;
    }

    .dashboard-table-footer button:disabled {
      opacity: .45;
    }

    :focus-visible {
      outline: 2px solid #0e8fa6;
      outline-offset: 2px
    }

    @media(max-width:1100px) {

      .g2,
      .g3,
      .g22,
      .pcs {
        grid-template-columns: 1fr
      }
    }

    @media(max-width:800px) {
      #pump360-main-dashboard aside {
        display: none
      }

      .body {
        padding: 14px
      }

      .bar0 {
        padding: 12px 14px
      }
    }

    /* Keep this converted standalone dashboard inside Laravel's content area. */
    #pump360-main-dashboard {
      display: block !important;
      position: relative;
      width: 100% !important;
      max-width: none !important;
      min-width: 0;
      margin: 0 !important;
      overflow-x: hidden;
    }

    #pump360-main-dashboard>.bar0 {
      position: relative;
      top: auto;
      width: 100%;
      min-width: 0;
      margin: 0;
      display: flex !important;
    }

    #pump360-main-dashboard>.body {
      display: block !important;
      position: relative;
      width: 100% !important;
      max-width: none !important;
      min-width: 0;
      margin: 0 !important;
      float: none !important;
      clear: both;
    }

    #pump360-main-dashboard .grid {
      display: grid !important;
      width: 100%;
      min-width: 0;
      gap: 16px !important;
      margin: 0 0 16px !important;
      float: none !important;
      clear: both;
    }

    #pump360-main-dashboard .card {
      height: 100%;
      margin: 0 !important;
    }

    #pump360-main-dashboard>.body>.card {
      height: auto;
      margin: 0 0 16px !important;
    }

    #pump360-main-dashboard canvas,
    #pump360-main-dashboard .ch {
      width: 100% !important;
      max-width: 100%;
      min-width: 0;
    }

    #pump360-main-dashboard canvas {
      display: block;
    }

    @media(max-width:1350px) {
      #pump360-main-dashboard .g3 {
        grid-template-columns: 1fr 1fr
      }

      #pump360-main-dashboard .g3>.card:last-child {
        grid-column: 1 / -1
      }
    }

    #pump360-main-dashboard>.body>h3 {
      margin: 24px 0 12px !important;
    }

    @media(max-width:800px) {
      #pump360-main-dashboard .grid {
        gap: 12px !important;
        margin-bottom: 12px !important;
      }

      #pump360-main-dashboard>.body>.card {
        margin-bottom: 12px !important;
      }
    }

    #pump360-main-dashboard .dashboard-filter {
      display: grid;
      gap: 3px;
      color: var(--mut);
      font-size: 11px;
      font-weight: 700;
    }

    #pump360-main-dashboard .dashboard-filter input,
    #pump360-main-dashboard .dashboard-action {
      min-height: 38px;
      border: 1px solid var(--line);
      border-radius: 10px;
      padding: 8px 11px;
      background: var(--card);
      color: var(--ink);
      font: inherit;
      font-weight: 700;
    }

    #pump360-main-dashboard .dashboard-action {
      align-self: end;
      border-color: #101828;
      background: #101828;
      color: #fff;
      cursor: pointer;
    }

    #pump360-main-dashboard .dashboard-action.secondary {
      background: var(--card);
      color: var(--ink);
      border-color: var(--line);
    }

    #pump360-main-dashboard .choices {
      min-width: 220px;
      align-self: end;
      margin: 0;
    }

    #pump360-main-dashboard .choices__inner {
      min-height: 38px;
      border: 1px solid var(--line);
      border-radius: 10px;
      background: var(--card);
      color: var(--ink);
    }

    #pump360-main-dashboard .dashboard-loader {
      position: fixed;
      inset: 0;
      z-index: 10050;
      display: none;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      gap: 14px;
      background: rgba(238, 241, 245, .9);
      color: #101828;
      backdrop-filter: blur(3px);
    }

    #pump360-main-dashboard .dashboard-loader.active {
      display: flex;
    }

    #pump360-main-dashboard .dashboard-spinner {
      width: 48px;
      height: 48px;
      border: 5px solid #d8dee8;
      border-top-color: #0e8fa6;
      border-radius: 50%;
      animation: dashboard-spin .75s linear infinite;
    }

    #pump360-main-dashboard .dashboard-error {
      display: none;
      position: fixed;
      right: 20px;
      bottom: 20px;
      z-index: 10060;
      max-width: 460px;
      padding: 14px 18px;
      border-radius: 10px;
      background: #b42318;
      color: #fff;
      box-shadow: 0 12px 30px rgba(0, 0, 0, .2);
    }

    #pump360-main-dashboard .dashboard-empty {
      display: grid;
      min-height: 180px;
      place-items: center;
      color: var(--mut);
      text-align: center;
    }

    @keyframes dashboard-spin {
      to {
        transform: rotate(360deg);
      }
    }

    @media print {

      header,
      .app-sidebar,
      .app-menu,
      footer,
      #pump360-main-dashboard .bar0 .fl,
      #pump360-main-dashboard .dashboard-loader,
      #pump360-main-dashboard .dashboard-error {
        display: none !important;
      }

      #pump360-main-dashboard {
        background: #fff;
      }
    }
  </style>
@endsection

@section('js')
  <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
  <script src="{{ asset('assets/libs/chart.js/chart.umd.js') }}"></script>
  <script>
    (() => {
      'use strict';

      const dashboardApiUrl = @json(url('/api/main-dashboard'));
      const dashboardState = {
        response: null,
        charts: {},
        activeRecordKey: 'nozzle_readings',
        filtersInitialized: false,
        selectedProductId: '',
        stationChoices: null,
        detailCharts: [],
        recordDataTable: null,
        recordPage: 1,
        recordPageLength: 10,
        recordSearch: ''
      };
      const chartPalette = ['#f59e0b', '#0e8fa6', '#8b5cf6', '#12a05c', '#4a90d9', '#e0453f', '#ec4899', '#64748b'];

      const elements = {
        root: document.getElementById('pump360-main-dashboard'),
        loader: document.getElementById('dashboardLoader'),
        error: document.getElementById('dashboardError'),
        fromDate: document.getElementById('dashboardFromDate'),
        toDate: document.getElementById('dashboardToDate'),
        station: document.getElementById('st'),
        productSegment: document.getElementById('dashboardProductSegment'),
        subtitle: document.getElementById('sub'),
        kpis: document.getElementById('kpis'),
        productCards: document.getElementById('pcs'),
        tanks: document.getElementById('tanks'),
        insights: document.getElementById('ins'),
        tabs: document.getElementById('tabs'),
        panes: document.getElementById('panes'),
        detailOverlay: document.getElementById('dOv'),
        detailDrawer: document.getElementById('dR'),
        detailClose: document.getElementById('dX'),
        detailTitle: document.getElementById('dT'),
        detailSubtitle: document.getElementById('dS'),
        detailChips: document.getElementById('dChips'),
        detailChartBox: document.getElementById('dBox'),
        detailTable: document.getElementById('dTb')
      };

      const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
      })[character]);
      const formatNumber = value => Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });
      const formatCurrency = value => `Rs ${Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 0 })}`;
      const formatDate = value => value ? new Date(`${value}T00:00:00`).toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' }) : '';
      const formatCell = value => typeof value === 'number' ? formatNumber(value) : escapeHtml(value ?? '—');

      function setDefaultDates() {
        const today = new Date();
        const fromDate = new Date(today);
        fromDate.setDate(today.getDate() - 6);
        const localDate = date => {
          const offset = date.getTimezoneOffset();
          return new Date(date.getTime() - offset * 60000).toISOString().slice(0, 10);
        };
        elements.fromDate.value = localDate(fromDate);
        elements.toDate.value = localDate(today);
      }

      function setLoading(isLoading) {
        elements.loader.classList.toggle('active', isLoading);
        document.getElementById('applyDashboardFilters').disabled = isLoading;
      }

      function showError(message) {
        elements.error.textContent = message;
        elements.error.style.display = 'block';
        window.clearTimeout(showError.timeout);
        showError.timeout = window.setTimeout(() => elements.error.style.display = 'none', 7000);
      }

      function buildRequestUrl() {
        const parameters = new URLSearchParams({
          from_date: elements.fromDate.value,
          to_date: elements.toDate.value
        });
        if (elements.station.value) parameters.set('station_id', elements.station.value);
        if (dashboardState.selectedProductId) parameters.set('product_id', dashboardState.selectedProductId);
        return `${dashboardApiUrl}?${parameters.toString()}`;
      }

      async function fetchDashboardData() {
        if (!elements.fromDate.value || !elements.toDate.value) return showError('Please select both From and To dates.');
        if (elements.fromDate.value > elements.toDate.value) return showError('From date cannot be after To date.');

        setLoading(true);
        elements.error.style.display = 'none';
        try {
          const response = await fetch(buildRequestUrl(), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
          const payload = await response.json();
          if (!response.ok || !payload.success) throw new Error(payload.message || payload.error || 'Dashboard data could not be loaded.');
          dashboardState.response = payload;
          initializeFilterOptions(payload.filters);
          renderDashboard(payload);
        } catch (error) {
          console.error('Main dashboard request failed:', error);
          showError(error.message || 'Dashboard data could not be loaded.');
        } finally {
          setLoading(false);
        }
      }

      function initializeFilterOptions(filters) {
        if (dashboardState.filtersInitialized) return;
        elements.station.innerHTML = '<option value="">All stations</option>' + filters.stations.map(station =>
          `<option value="${station.id}">${escapeHtml(station.name)}</option>`
        ).join('');
        if (window.Choices) {
          dashboardState.stationChoices = new Choices(elements.station, {
            searchEnabled: true,
            searchPlaceholderValue: 'Search site...',
            shouldSort: false,
            itemSelectText: '',
            allowHTML: false
          });
          elements.station.addEventListener('change', fetchDashboardData);
        }
        renderProductSegments(filters.products);
        dashboardState.filtersInitialized = true;
      }

      function renderProductSegments(products) {
        elements.productSegment.innerHTML = `<button type="button" data-product-id="" class="${dashboardState.selectedProductId ? '' : 'on'}">All products</button>` +
          products.map(product => `<button type="button" data-product-id="${product.id}" class="${String(product.id) === String(dashboardState.selectedProductId) ? 'on' : ''}">${escapeHtml(product.name)}</button>`).join('');
        elements.productSegment.querySelectorAll('[data-product-id]').forEach(button => button.addEventListener('click', () => {
          dashboardState.selectedProductId = button.dataset.productId;
          elements.productSegment.querySelectorAll('button').forEach(item => item.classList.toggle('on', item === button));
          fetchDashboardData();
        }));
      }

      function renderDashboard(data) {
        elements.subtitle.textContent = `${formatDate(data.meta.from_date)} – ${formatDate(data.meta.to_date)} · live database data`;
        renderSummaryCards(data.summary);
        renderProductCards(data.product_summary, data.summary.litres_sold);
        renderTankLevels(data.tank_levels);
        renderInsightCards(data.station_finance, data.tank_variances);
        renderCharts(data);
        renderRecordTables(data.records);
        bindDashboardDetails();
      }

      function renderSummaryCards(summary) {
        const cards = [
          ['Fuel sales', formatCurrency(summary.fuel_sales)],
          ['Litres sold', `${formatNumber(summary.litres_sold)} L`],
          ['Shifts', formatNumber(summary.shift_count)],
          ['Cash collected', formatCurrency(summary.cash_collected)],
          ['Active nozzles', formatNumber(summary.active_nozzles)],
          ['Party credit (fuel)', formatCurrency(summary.party_credit)],
          ['Stock loss', formatCurrency(summary.stock_loss)],
          ['Stock gain', formatCurrency(summary.stock_gain)],
          ['Income', formatCurrency(summary.income)],
          ['Expenses', formatCurrency(summary.expenses)]
        ];
        elements.kpis.innerHTML = cards.map(([label, value]) =>
          `<div class="card kpi"><small>${escapeHtml(label)}</small><b>${escapeHtml(value)}</b></div>`
        ).join('');
      }

      function renderProductCards(products, totalLitres) {
        elements.productCards.innerHTML = products.length ? products.map((product, index) => {
          const share = totalLitres > 0 ? product.litres / totalLitres * 100 : 0;
          return `<button class="pc" type="button" data-product-id="${product.product_id}" style="--c:${chartPalette[index % chartPalette.length]}">
              <span>${escapeHtml(product.product_name)}</span><b>${formatNumber(product.litres)} L</b>
            <small>${formatCurrency(product.amount)} · ${formatNumber(product.nozzle_count)} nozzles · ${formatNumber(share)}%</small><i style="width:${Math.min(100, share)}%"></i>
            </button>`;
        }).join('') : '<div class="card dashboard-empty">No product sales found for this selection.</div>';

        elements.productCards.querySelectorAll('[data-product-id]').forEach(button => button.addEventListener('click', () => openProductDetails(Number(button.dataset.productId))));
      }

      function destroyDashboardCharts() {
        Object.values(dashboardState.charts).forEach(chart => chart.destroy());
        dashboardState.charts = {};
      }

      function createChart(key, canvasId, config) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        config.options ??= {};
        config.options.onHover = (event, items) => event.native.target.style.cursor = items.length ? 'pointer' : 'default';
        config.options.onClick = (event, items) => {
          if (items.length) openChartDetails(key, items[0].index);
        };
        dashboardState.charts[key] = new Chart(canvas, config);
      }

      function chartOptions({ horizontal = false, stacked = false, legend = false } = {}) {
        return {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: horizontal ? 'y' : 'x',
          plugins: { legend: { display: legend, position: 'bottom', labels: { usePointStyle: true } } },
          scales: {
            x: { stacked, beginAtZero: true, grid: { display: horizontal, color: '#e6e9ef' }, ticks: { autoSkip: true, maxTicksLimit: 12, maxRotation: 45 } },
            y: { stacked, beginAtZero: !horizontal, grid: { display: !horizontal, color: '#e6e9ef' }, ticks: { autoSkip: true, maxTicksLimit: 9 } }
          }
        };
      }

      function renderCharts(data) {
        destroyDashboardCharts();
        const productNames = [...new Set(data.sales_trend.map(row => row.product_name))];
        const trendDates = [...new Set(data.sales_trend.map(row => row.date))].sort();
        createChart('salesTrend', 'cTrend', {
          type: 'line',
          data: {
            labels: trendDates.map(formatDate), datasets: productNames.map((product, index) => ({
              label: product,
              data: trendDates.map(date => data.sales_trend.find(row => row.date === date && row.product_name === product)?.litres || 0),
              borderColor: chartPalette[index % chartPalette.length], backgroundColor: chartPalette[index % chartPalette.length], tension: .3
            }))
          },
          options: chartOptions({ legend: true })
        });

        createChart('productShare', 'cProd', {
          type: 'doughnut',
          data: { labels: data.product_summary.map(row => row.product_name), datasets: [{ data: data.product_summary.map(row => row.litres), backgroundColor: chartPalette, borderWidth: 0 }] },
          options: { responsive: true, maintainAspectRatio: false, cutout: '63%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } } }
        });

        const visibleNozzleSales = [...data.nozzle_sales].sort((a, b) => Number(b.litres) - Number(a.litres)).slice(0, 15);
        createChart('nozzleSales', 'cNoz', {
          type: 'bar',
          data: { labels: visibleNozzleSales.map(row => row.label), datasets: [{ label: 'Litres', data: visibleNozzleSales.map(row => row.litres), backgroundColor: visibleNozzleSales.map((row, index) => chartPalette[index % chartPalette.length]), borderRadius: 5 }] },
          options: chartOptions({ horizontal: true })
        });

        const stationPerformance = [...data.station_performance];
        const highestStations = stationPerformance.slice(0, 5);
        const lowestStations = [...stationPerformance].sort((a, b) => a.litres - b.litres).slice(0, 5);
        renderHorizontalChart('highestStations', 'cTop', highestStations, '#12a05c');
        renderHorizontalChart('lowestStations', 'cLow', lowestStations, '#e0453f');

        const visibleShiftActivity = data.shift_activity.slice(-30);
        createChart('shiftActivity', 'cHour', {
          type: 'bar',
          data: { labels: visibleShiftActivity.map(row => row.label), datasets: [{ label: 'Litres', data: visibleShiftActivity.map(row => row.litres), backgroundColor: '#4a90d9', borderRadius: 5 }] },
          options: chartOptions()
        });

        createChart('cashDistribution', 'cCash', {
          type: 'doughnut',
          data: { labels: ['Cash', 'Bank', 'Fuel card', 'Credit card'], datasets: [{ data: Object.values(data.cash_distribution), backgroundColor: ['#12a05c', '#4a90d9', '#f59e0b', '#e0453f'], borderWidth: 0 }] },
          options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } } }
        });

        const finances = data.station_finance;
        renderFinanceChart('lossByStation', 'cLossS', finances, 'loss', '#e0453f');
        renderFinanceChart('gainByStation', 'cGainS', finances, 'gain', '#12a05c');
        renderVarianceCharts(data.tank_variances);
        renderFinanceChart('incomeByStation', 'cInc', finances, 'income', '#0e8fa6');
        renderFinanceChart('expenseByStation', 'cExp', finances, 'expense', '#f59e0b');
        renderFinanceChart('creditByStation', 'cCre', finances, 'credit', '#8b5cf6');
        renderAdditionalCharts(data);
        installChartTypeControls();
      }

      function renderHorizontalChart(key, canvasId, rows, color) {
        createChart(key, canvasId, {
          type: 'bar', data: { labels: rows.map(row => row.station_name), datasets: [{ data: rows.map(row => row.litres), backgroundColor: color, borderRadius: 5 }] },
          options: chartOptions({ horizontal: true })
        });
      }

      function renderFinanceChart(key, canvasId, rows, field, color) {
        const sortedRows = [...rows].sort((a, b) => b[field] - a[field]);
        createChart(key, canvasId, {
          type: 'bar', data: { labels: sortedRows.map(row => row.station_name), datasets: [{ data: sortedRows.map(row => row[field]), backgroundColor: color, borderRadius: 5 }] },
          options: chartOptions({ horizontal: true })
        });
      }

      function renderVarianceCharts(variances) {
        const byProduct = Object.values(variances.reduce((result, row) => {
          result[row.product_name] ??= { product: row.product_name, loss: 0, gain: 0 };
          if (row.variance_value < 0) result[row.product_name].loss += Math.abs(row.variance_value);
          else result[row.product_name].gain += row.variance_value;
          return result;
        }, {}));
        createChart('productVariance', 'cProdLG', {
          type: 'bar', data: {
            labels: byProduct.map(row => row.product), datasets: [
              { label: 'Loss', data: byProduct.map(row => row.loss), backgroundColor: '#e0453f' },
              { label: 'Gain', data: byProduct.map(row => row.gain), backgroundColor: '#12a05c' }
            ]
          }, options: chartOptions({ legend: true })
        });
        const visibleVariances = [...variances].sort((a, b) => Math.abs(Number(b.variance_litres)) - Math.abs(Number(a.variance_litres))).slice(0, 30);
        createChart('tankVariance', 'cTankLG', {
          type: 'bar', data: {
            labels: visibleVariances.map(row => `${row.station_name} · ${row.tank_name}`), datasets: [{
              label: 'Variance litres', data: visibleVariances.map(row => row.variance_litres),
              backgroundColor: visibleVariances.map(row => row.variance_litres < 0 ? '#e0453f' : '#12a05c')
            }]
          }, options: chartOptions()
        });
      }

      function renderAdditionalCharts(data) {
        const stations = data.station_performance.slice(0, 12);
        const stationNames = stations.map(row => row.station_name);
        const productNames = [...new Set(data.nozzle_sales.map(row => row.product_name))];
        createChart('stationProduct', 'xStack', {
          type: 'bar', data: { labels: stationNames, datasets: productNames.map((productName, index) => ({
            label: productName,
            data: stationNames.map(stationName => data.nozzle_sales.filter(row => row.station_name === stationName && row.product_name === productName).reduce((total, row) => total + Number(row.litres || 0), 0)),
            backgroundColor: chartPalette[index % chartPalette.length], borderRadius: 4
          })) },
          options: chartOptions({ legend: true, stacked: true })
        });
        createChart('incomeExpenseNet', 'xMix', {
          type: 'bar', data: {
            labels: data.station_finance.slice(0, 12).map(row => row.station_name), datasets: [
              { label: 'Income', data: data.station_finance.slice(0, 12).map(row => row.income), backgroundColor: '#0e8fa6' },
              { label: 'Expense', data: data.station_finance.slice(0, 12).map(row => row.expense), backgroundColor: '#f59e0b' },
              { label: 'Net', data: data.station_finance.slice(0, 12).map(row => row.income - row.expense), type: 'line', borderColor: '#12a05c', backgroundColor: '#12a05c' }
            ]
          }, options: chartOptions({ legend: true })
        });
        const performanceStations = data.station_finance.slice(0, 12);
        const maxStationLitres = Math.max(...data.station_performance.map(row => Number(row.litres) || 0), 1);
        createChart('stationScorecard', 'xRad', {
          type: 'bubble', data: {
            datasets: [{
              label: 'Stations',
              data: performanceStations.map(row => {
                const performance = data.station_performance.find(item => item.station_id === row.station_id);
                return {
                  x: Number(row.income || 0),
                  y: Number(row.gain || 0) - Number(row.loss || 0),
                  r: Math.max(6, Math.min(24, Number(performance?.litres || 0) / maxStationLitres * 24)),
                  stationName: row.station_name
                };
              }),
              backgroundColor: performanceStations.map((row, index) => `${chartPalette[index % chartPalette.length]}bb`),
              borderColor: performanceStations.map((row, index) => chartPalette[index % chartPalette.length]),
              borderWidth: 1
            }]
          }, options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: context => `${context.raw.stationName}: income ${formatCurrency(context.raw.x)}, net stock ${formatNumber(context.raw.y)}` } } },
            scales: { x: { title: { display: true, text: 'Income (Rs)' }, beginAtZero: true }, y: { title: { display: true, text: 'Net stock gain / loss (Rs)' } } }
          }
        });
        createChart('expenseShare', 'xPol', {
          type: 'doughnut', data: { labels: data.station_finance.slice(0, 10).map(row => row.station_name), datasets: [{ data: data.station_finance.slice(0, 10).map(row => row.expense), backgroundColor: data.station_finance.slice(0, 10).map((row, index) => chartPalette[index % chartPalette.length]), borderWidth: 0 }] },
          options: { responsive: true, maintainAspectRatio: false, cutout: '58%', plugins: { legend: { display: true, position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } } }
        });
        createChart('nozzleValue', 'xBub', {
          type: 'bubble', data: { datasets: data.nozzle_sales.slice(0, 60).map((row, index) => ({ label: row.label, data: [{ x: row.litres, y: row.amount, r: 5 }], backgroundColor: chartPalette[index % chartPalette.length] })) },
          options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { title: { display: true, text: 'Litres' } }, y: { title: { display: true, text: 'Amount (Rs)' } } } }
        });
      }

      const chartTypeOptions = {
        salesTrend: ['line', 'bar'], productShare: ['doughnut', 'pie', 'bar'], nozzleSales: ['bar', 'line', 'pie'],
        highestStations: ['bar', 'line', 'pie'], lowestStations: ['bar', 'line', 'pie'], shiftActivity: ['bar', 'line'],
        cashDistribution: ['doughnut', 'pie', 'bar'], lossByStation: ['bar', 'line', 'pie'], gainByStation: ['bar', 'line', 'pie'],
        productVariance: ['bar', 'line'], tankVariance: ['bar', 'line'], incomeByStation: ['bar', 'line', 'pie'],
        expenseByStation: ['bar', 'line', 'pie'], creditByStation: ['bar', 'line', 'pie']
      };

      function installChartTypeControls() {
        Object.entries(chartTypeOptions).forEach(([chartKey, allowedTypes]) => {
          const chart = dashboardState.charts[chartKey];
          if (!chart) return;
          const card = chart.canvas.closest('.card');
          card.querySelector('.sw.dynamic-chart-switch')?.remove();
          const switcher = document.createElement('div');
          switcher.className = 'sw dynamic-chart-switch';
          switcher.innerHTML = allowedTypes.map(type => `<button type="button" data-chart-type="${type}" class="${type === chart.config.type ? 'on' : ''}">${type === 'doughnut' ? 'Donut' : type[0].toUpperCase() + type.slice(1)}</button>`).join('');
          switcher.addEventListener('click', event => {
            const button = event.target.closest('[data-chart-type]');
            if (!button) return;
            changeChartType(chartKey, button.dataset.chartType);
            switcher.querySelectorAll('button').forEach(item => item.classList.toggle('on', item === button));
          });
          card.style.position = 'relative';
          card.querySelector('h3').style.paddingRight = '145px';
          card.appendChild(switcher);
        });
      }

      function changeChartType(chartKey, chartType) {
        const chart = dashboardState.charts[chartKey];
        if (!chart) return;
        const isCircular = chartType === 'pie' || chartType === 'doughnut';
        chart.config.type = chartType;
        chart.options.indexAxis = ['highestStations', 'lowestStations', 'lossByStation', 'gainByStation', 'incomeByStation', 'expenseByStation', 'creditByStation'].includes(chartKey) && !isCircular ? 'y' : 'x';
        chart.options.scales = isCircular ? { x: { display: false }, y: { display: false } } : {
          x: { display: true, grid: { display: chart.options.indexAxis === 'y' } },
          y: { display: true, beginAtZero: true, grid: { color: '#e6e9ef' } }
        };
        chart.options.plugins.legend.display = isCircular || chart.data.datasets.length > 1;
        chart.options.cutout = chartType === 'doughnut' ? '63%' : 0;
        chart.update();
      }

      function tableMarkup(columns, rows) {
        return `<div style="overflow:auto"><table><thead><tr>${columns.map(column => `<th>${escapeHtml(column.label)}</th>`).join('')}</tr></thead><tbody>` +
          (rows.length ? rows.map(row => `<tr>${columns.map(column => `<td>${formatCell(row[column.key])}</td>`).join('')}</tr>`).join('') : `<tr><td colspan="${columns.length}">No records found.</td></tr>`) +
          '</tbody></table></div>';
      }

      function openDetailDrawer({ title, subtitle, chips = [], columns = [], rows = [], chart = null, charts = [], tables = [] }) {
        dashboardState.detailCharts.forEach(instance => instance.destroy());
        dashboardState.detailCharts = [];
        elements.detailTitle.textContent = title;
        elements.detailSubtitle.textContent = subtitle || `${formatDate(dashboardState.response.meta.from_date)} – ${formatDate(dashboardState.response.meta.to_date)}`;
        elements.detailChips.innerHTML = chips.map(([label, value]) => `<div><small>${escapeHtml(label)}</small><b>${escapeHtml(value)}</b></div>`).join('');
        elements.detailChartBox.innerHTML = '';
        const detailCharts = charts.length ? charts : (chart ? [chart] : []);
        elements.detailChartBox.style.display = detailCharts.length ? 'grid' : 'none';
        detailCharts.forEach(chartConfig => {
          const chartCard = document.createElement('div');
          chartCard.className = 'cb';
          chartCard.innerHTML = `<h4>${escapeHtml(chartConfig.title)}</h4><div class="cv"><canvas></canvas></div>`;
          elements.detailChartBox.appendChild(chartCard);
          dashboardState.detailCharts.push(new Chart(chartCard.querySelector('canvas'), {
            type: chartConfig.type || 'bar',
            data: { labels: chartConfig.labels, datasets: chartConfig.datasets },
            options: ['doughnut', 'pie', 'polarArea'].includes(chartConfig.type) ? { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } } } : chartOptions({ horizontal: chartConfig.horizontal, stacked: chartConfig.stacked, legend: chartConfig.datasets.length > 1 })
          }));
        });
        const detailTables = tables.length ? tables : (columns.length ? [{ title: 'Detailed records', columns, rows }] : []);
        elements.detailTable.innerHTML = detailTables.map(table => `<div class="dtt">${escapeHtml(table.title)}</div>${tableMarkup(table.columns, table.rows || [])}`).join('');
        elements.detailDrawer.classList.add('on');
        elements.detailOverlay.classList.add('on');
        elements.detailClose.focus();
      }

      function closeDetailDrawer() {
        elements.detailDrawer.classList.remove('on');
        elements.detailOverlay.classList.remove('on');
        dashboardState.detailCharts.forEach(instance => instance.destroy());
        dashboardState.detailCharts = [];
      }

      function openChartDetails(chartKey, dataIndex) {
        const chart = dashboardState.charts[chartKey];
        if (!chart) return;
        const label = chart.data.labels?.[dataIndex] ?? 'Details';
        if (chartKey === 'productShare') {
          const product = dashboardState.response.product_summary.find(item => item.product_name === label);
          if (product) return openProductDetails(Number(product.product_id));
        }
        if (chartKey === 'nozzleSales') return openNozzleSalesDetails();
        if (chartKey === 'nozzleValue') return openNozzleSalesDetails();
        if (chartKey === 'cashDistribution') return openCashDistributionDetails();
        if (chartKey === 'shiftActivity') return openShiftActivityDetails(dataIndex);
        if (chartKey === 'productVariance') {
          const product = dashboardState.response.product_summary.find(item => item.product_name === label);
          if (product) return openProductDetails(Number(product.product_id));
        }
        if (chartKey === 'tankVariance') {
          const variance = [...dashboardState.response.tank_variances].sort((a, b) => Math.abs(Number(b.variance_litres)) - Math.abs(Number(a.variance_litres))).slice(0, 30)[dataIndex];
          if (variance) return openTankDetails(Number(variance.tank_id));
        }
        if (chartKey === 'stationScorecard') {
          const station = dashboardState.response.station_finance.slice(0, 12)[dataIndex];
          if (station) return openStationDetails(station.station_name);
        }
        if (['stationProduct', 'incomeExpenseNet', 'expenseShare'].includes(chartKey)) return openStationDetails(String(label));
        if (['highestStations', 'lowestStations', 'lossByStation', 'gainByStation', 'incomeByStation', 'expenseByStation', 'creditByStation'].includes(chartKey)) {
          return openStationDetails(String(label));
        }
        const rows = chart.data.datasets.map(dataset => ({ series: dataset.label || 'Value', value: Number(dataset.data[dataIndex] ?? 0) }));
        openDetailDrawer({
          title: String(label),
          subtitle: chart.canvas.closest('.card')?.querySelector('h3')?.textContent || 'Chart details',
          chips: rows.map(row => [row.series, formatNumber(row.value)]),
          columns: [{ key: 'series', label: 'Series' }, { key: 'value', label: 'Value' }],
          rows,
          chart: { title: String(label), type: 'doughnut', labels: rows.map(row => row.series), datasets: [{ data: rows.map(row => row.value), backgroundColor: chartPalette }] }
        });
      }

      function openProductDetails(productId) {
        const response = dashboardState.response;
        const product = response.product_summary.find(item => Number(item.product_id) === productId);
        if (!product) return;
        const nozzles = response.nozzle_sales.filter(item => item.product_name === product.product_name);
        const readings = (response.records?.nozzle_readings || []).filter(item => item.product === product.product_name);
        const variances = response.tank_variances.filter(item => item.product_name === product.product_name);
        const stations = Object.values(nozzles.reduce((grouped, item) => {
          grouped[item.station_name] ??= { station_name: item.station_name, litres: 0, amount: 0, nozzles: 0 };
          grouped[item.station_name].litres += Number(item.litres || 0);
          grouped[item.station_name].amount += Number(item.amount || 0);
          grouped[item.station_name].nozzles += 1;
          return grouped;
        }, {})).sort((a, b) => b.litres - a.litres);
        const stationVariance = Object.values(variances.reduce((grouped, item) => {
          grouped[item.station_name] ??= { station_name: item.station_name, loss: 0, gain: 0 };
          Number(item.variance_litres) < 0 ? grouped[item.station_name].loss += Math.abs(Number(item.variance_litres)) : grouped[item.station_name].gain += Number(item.variance_litres || 0);
          return grouped;
        }, {}));
        const trend = response.sales_trend.filter(item => item.product_name === product.product_name);
        const loss = variances.reduce((total, item) => total + Math.max(0, -Number(item.variance_value || 0)), 0);
        const gain = variances.reduce((total, item) => total + Math.max(0, Number(item.variance_value || 0)), 0);
        openDetailDrawer({
          title: product.product_name,
          subtitle: `All stations · ${formatDate(response.meta.from_date)} – ${formatDate(response.meta.to_date)}`,
          chips: [['Litres', `${formatNumber(product.litres)} L`], ['Sales', formatCurrency(product.amount)], ['Nozzles', formatNumber(product.nozzle_count)], ['Rate / L', formatCurrency(product.litres ? product.amount / product.litres : 0)], ['Readings', formatNumber(readings.length)], ['Avg / reading', `${formatNumber(readings.length ? product.litres / readings.length : 0)} L`], ['Stock loss', formatCurrency(loss)], ['Stock gain', formatCurrency(gain)], ['Stations', formatNumber(stations.length)]],
          charts: [
            { title: 'Litres by station', type: 'bar', labels: stations.map(item => item.station_name), datasets: [{ label: 'Litres', data: stations.map(item => item.litres), backgroundColor: '#0e8fa6' }] },
            { title: 'Station share', type: 'doughnut', labels: stations.map(item => item.station_name), datasets: [{ data: stations.map(item => item.litres), backgroundColor: chartPalette }] },
            { title: 'Sales trend', type: 'line', labels: trend.map(item => formatDate(item.date)), datasets: [{ label: 'Litres', data: trend.map(item => item.litres), borderColor: '#0e8fa6', backgroundColor: '#0e8fa6', tension: .3 }] },
            { title: 'Loss and gain by station (L)', type: 'bar', labels: stationVariance.map(item => item.station_name), datasets: [{ label: 'Loss', data: stationVariance.map(item => item.loss), backgroundColor: '#e0453f' }, { label: 'Gain', data: stationVariance.map(item => item.gain), backgroundColor: '#12a05c' }] }
          ],
          tables: [
            { title: 'Station-wise sales', columns: [{ key: 'station_name', label: 'Station' }, { key: 'nozzles', label: 'Nozzles' }, { key: 'litres', label: 'Litres' }, { key: 'amount', label: 'Amount' }], rows: stations },
            { title: 'Nozzle readings', columns: [{ key: 'station', label: 'Station' }, { key: 'dispenser', label: 'Dispenser' }, { key: 'nozzle', label: 'Nozzle' }, { key: 'opening', label: 'Opening' }, { key: 'closing', label: 'Closing' }, { key: 'litres', label: 'Sold (L)' }, { key: 'amount', label: 'Amount' }], rows: readings },
            { title: 'Tanks and stock variance', columns: [{ key: 'station_name', label: 'Station' }, { key: 'tank_name', label: 'Tank' }, { key: 'opening_litres', label: 'Opening' }, { key: 'closing_litres', label: 'Closing' }, { key: 'variance_litres', label: 'Variance' }, { key: 'status', label: 'Result' }], rows: variances }
          ]
        });
      }

      function openNozzleSalesDetails() {
        const response = dashboardState.response;
        const nozzles = [...response.nozzle_sales].sort((a, b) => Number(b.litres) - Number(a.litres));
        const readings = response.records?.nozzle_readings || [];
        const stationRows = Object.values(nozzles.reduce((grouped, item) => {
          grouped[item.station_name] ??= { station_name: item.station_name, litres: 0, amount: 0, nozzles: 0 };
          grouped[item.station_name].litres += Number(item.litres || 0);
          grouped[item.station_name].amount += Number(item.amount || 0);
          grouped[item.station_name].nozzles += 1;
          return grouped;
        }, {})).sort((a, b) => b.litres - a.litres);
        const productRows = Object.values(nozzles.reduce((grouped, item) => {
          grouped[item.product_name] ??= { product_name: item.product_name, litres: 0, amount: 0, nozzles: 0 };
          grouped[item.product_name].litres += Number(item.litres || 0);
          grouped[item.product_name].amount += Number(item.amount || 0);
          grouped[item.product_name].nozzles += 1;
          return grouped;
        }, {}));
        const litres = nozzles.reduce((total, item) => total + Number(item.litres || 0), 0);
        const amount = nozzles.reduce((total, item) => total + Number(item.amount || 0), 0);
        openDetailDrawer({
          title: 'Nozzle-wise sales',
          subtitle: `Complete nozzle overview · ${formatDate(response.meta.from_date)} – ${formatDate(response.meta.to_date)}`,
          chips: [['Active nozzles', formatNumber(nozzles.length)], ['Litres sold', `${formatNumber(litres)} L`], ['Fuel sales', formatCurrency(amount)], ['Avg / nozzle', `${formatNumber(nozzles.length ? litres / nozzles.length : 0)} L`], ['Avg rate / L', formatCurrency(litres ? amount / litres : 0)], ['Stations', formatNumber(stationRows.length)], ['Products', formatNumber(productRows.length)], ['Readings', formatNumber(readings.length)], ['Top nozzle', nozzles[0]?.label || '—']],
          charts: [
            { title: 'Top nozzle sales (litres)', type: 'bar', horizontal: true, labels: nozzles.slice(0, 20).map(item => item.label), datasets: [{ label: 'Litres', data: nozzles.slice(0, 20).map(item => item.litres), backgroundColor: '#0e8fa6' }] },
            { title: 'Litres by station', type: 'bar', labels: stationRows.map(item => item.station_name), datasets: [{ label: 'Litres', data: stationRows.map(item => item.litres), backgroundColor: '#4a90d9' }] },
            { title: 'Nozzles by product', type: 'doughnut', labels: productRows.map(item => item.product_name), datasets: [{ data: productRows.map(item => item.nozzles), backgroundColor: chartPalette }] },
            { title: 'Sales by product (Rs)', type: 'bar', labels: productRows.map(item => item.product_name), datasets: [{ label: 'Sales', data: productRows.map(item => item.amount), backgroundColor: chartPalette }] }
          ],
          tables: [
            { title: 'Station summary', columns: [{ key: 'station_name', label: 'Station' }, { key: 'nozzles', label: 'Nozzles' }, { key: 'litres', label: 'Litres' }, { key: 'amount', label: 'Amount' }], rows: stationRows },
            { title: 'Complete nozzle sales', columns: [{ key: 'station_name', label: 'Station' }, { key: 'label', label: 'Nozzle' }, { key: 'product_name', label: 'Product' }, { key: 'litres', label: 'Litres' }, { key: 'amount', label: 'Amount' }], rows: nozzles },
            { title: 'Nozzle readings', columns: [{ key: 'station', label: 'Station' }, { key: 'shift', label: 'Shift' }, { key: 'dispenser', label: 'Dispenser' }, { key: 'nozzle', label: 'Nozzle' }, { key: 'product', label: 'Product' }, { key: 'opening', label: 'Opening' }, { key: 'closing', label: 'Closing' }, { key: 'litres', label: 'Sold (L)' }, { key: 'amount', label: 'Amount' }], rows: readings }
          ]
        });
      }

      function openCashDistributionDetails() {
        const response = dashboardState.response;
        const cash = response.cash_distribution;
        const payments = [
          { type: 'Cash', amount: Number(cash.cash || 0), color: '#12a05c' },
          { type: 'Bank', amount: Number(cash.bank || 0), color: '#4a90d9' },
          { type: 'Fuel card', amount: Number(cash.fuel_card || 0), color: '#f59e0b' },
          { type: 'Credit card', amount: Number(cash.credit_card || 0), color: '#e0453f' }
        ];
        const total = payments.reduce((sum, item) => sum + item.amount, 0);
        const rows = payments.map(item => ({ ...item, share: `${formatNumber(total ? item.amount / total * 100 : 0)}%` }));
        openDetailDrawer({
          title: 'Cash distribution',
          subtitle: `Latest record per shift · ${formatDate(response.meta.from_date)} – ${formatDate(response.meta.to_date)}`,
          chips: [['Total collected', formatCurrency(total)], ...payments.map(item => [item.type, formatCurrency(item.amount)]), ['Cash share', `${formatNumber(total ? payments[0].amount / total * 100 : 0)}%`]],
          charts: [
            { title: 'Payment type share', type: 'doughnut', labels: payments.map(item => item.type), datasets: [{ data: payments.map(item => item.amount), backgroundColor: payments.map(item => item.color) }] },
            { title: 'Collected amount by type', type: 'bar', labels: payments.map(item => item.type), datasets: [{ label: 'Amount', data: payments.map(item => item.amount), backgroundColor: payments.map(item => item.color) }] }
          ],
          tables: [{ title: 'Payment breakdown', columns: [{ key: 'type', label: 'Type' }, { key: 'amount', label: 'Amount' }, { key: 'share', label: 'Share' }], rows }]
        });
      }

      function openShiftActivityDetails(dataIndex) {
        const response = dashboardState.response;
        const visibleShifts = response.shift_activity.slice(-30);
        const shift = visibleShifts[dataIndex];
        if (!shift) return;
        const shiftRecord = (response.records?.shifts || []).find(item => Number(item.id) === Number(shift.shift_id));
        const readings = (response.records?.nozzle_readings || []).filter(item => Number(item.shift) === Number(shiftRecord?.shift) && item.station === shiftRecord?.station);
        const transactions = (response.records?.transactions || []).filter(item => Number(item.shift) === Number(shiftRecord?.shift) && item.station === shiftRecord?.station);
        const products = Object.values(readings.reduce((grouped, item) => {
          grouped[item.product] ??= { product: item.product, litres: 0, amount: 0 };
          grouped[item.product].litres += Number(item.litres || 0);
          grouped[item.product].amount += Number(item.amount || 0);
          return grouped;
        }, {}));
        openDetailDrawer({
          title: shift.label,
          subtitle: 'Shift activity details',
          chips: [['Litres', `${formatNumber(shift.litres)} L`], ['Sales', formatCurrency(shift.amount)], ['Nozzle readings', formatNumber(shift.reading_count)], ['Transactions', formatNumber(transactions.length)], ['Products', formatNumber(products.length)], ['Avg / reading', `${formatNumber(shift.reading_count ? shift.litres / shift.reading_count : 0)} L`]],
          charts: [
            { title: 'Litres by product', type: 'doughnut', labels: products.map(item => item.product), datasets: [{ data: products.map(item => item.litres), backgroundColor: chartPalette }] },
            { title: 'Nozzle-wise litres', type: 'bar', horizontal: true, labels: readings.map(item => `${item.dispenser} ${item.nozzle}`), datasets: [{ label: 'Litres', data: readings.map(item => item.litres), backgroundColor: '#0e8fa6' }] }
          ],
          tables: [
            { title: 'Nozzle readings', columns: [{ key: 'dispenser', label: 'Dispenser' }, { key: 'nozzle', label: 'Nozzle' }, { key: 'product', label: 'Product' }, { key: 'opening', label: 'Opening' }, { key: 'closing', label: 'Closing' }, { key: 'litres', label: 'Litres' }, { key: 'amount', label: 'Amount' }], rows: readings },
            { title: 'Transactions', columns: [{ key: 'date', label: 'Time' }, { key: 'account', label: 'Account' }, { key: 'type', label: 'Type' }, { key: 'method', label: 'Method' }, { key: 'amount', label: 'Amount' }], rows: transactions }
          ]
        });
      }

      function productSummaryRows(response) {
        const totalLitres = response.product_summary.reduce((sum, row) => sum + Number(row.litres || 0), 0);
        return response.product_summary.map(row => ({
          ...row,
          rate: Number(row.litres) ? Number(row.amount) / Number(row.litres) : 0,
          share: `${formatNumber(totalLitres ? Number(row.litres) / totalLitres * 100 : 0)}%`
        }));
      }

      function openSalesSummaryDetails(metric) {
        const response = dashboardState.response;
        const products = productSummaryRows(response);
        const stations = response.station_performance.map(row => ({
          ...row,
          share: `${formatNumber(response.summary.litres_sold ? Number(row.litres) / Number(response.summary.litres_sold) * 100 : 0)}%`
        }));
        const isLitres = metric === 'litres';
        const valueKey = isLitres ? 'litres' : 'amount';
        const totalValue = products.reduce((sum, row) => sum + Number(row[valueKey] || 0), 0);
        const topStation = [...stations].sort((a, b) => Number(b[valueKey]) - Number(a[valueKey]))[0];
        const topProduct = [...products].sort((a, b) => Number(b[valueKey]) - Number(a[valueKey]))[0];
        openDetailDrawer({
          title: isLitres ? 'Litres sold' : 'Fuel sales',
          subtitle: `All stations · all products · ${formatDate(response.meta.from_date)} – ${formatDate(response.meta.to_date)}`,
          chips: isLitres ? [
            ['Total litres', `${formatNumber(totalValue)} L`], ['Fuel sales', formatCurrency(response.summary.fuel_sales)],
            ['Avg rate / L', formatCurrency(response.summary.litres_sold ? response.summary.fuel_sales / response.summary.litres_sold : 0)],
            ['Active nozzles', formatNumber(response.summary.active_nozzles)], ['Top station', topStation?.station_name || '—'], ['Top product', topProduct?.product_name || '—']
          ] : [
            ['Total sales', formatCurrency(totalValue)], ['Litres', `${formatNumber(response.summary.litres_sold)} L`],
            ['Avg rate / L', formatCurrency(response.summary.litres_sold ? response.summary.fuel_sales / response.summary.litres_sold : 0)],
            ['Shifts', formatNumber(response.summary.shift_count)], ['Top station', topStation?.station_name || '—'], ['Top product', topProduct?.product_name || '—']
          ],
          charts: [
            { title: `${isLitres ? 'Litres' : 'Sales'} by product`, type: 'doughnut', labels: products.map(row => row.product_name), datasets: [{ data: products.map(row => row[valueKey]), backgroundColor: chartPalette }] },
            { title: `${isLitres ? 'Litres' : 'Sales'} by station`, type: 'bar', labels: stations.map(row => row.station_name), datasets: [{ label: isLitres ? 'Litres' : 'Sales', data: stations.map(row => row[valueKey]), backgroundColor: '#f59e0b' }] }
          ],
          tables: [
            { title: 'Product-wise', columns: [{ key: 'product_name', label: 'Product' }, { key: 'nozzle_count', label: 'Nozzles' }, { key: 'litres', label: 'Litres' }, { key: 'rate', label: 'Rate / L' }, { key: 'amount', label: 'Amount' }, { key: 'share', label: 'Share' }], rows: products },
            { title: 'Station-wise', columns: [{ key: 'station_name', label: 'Station' }, { key: 'litres', label: 'Litres' }, { key: 'amount', label: 'Amount' }, { key: 'share', label: 'Share' }], rows: stations }
          ]
        });
      }

      function openAllShiftsDetails() {
        const response = dashboardState.response;
        const shifts = response.records?.shifts || [];
        const activity = response.shift_activity;
        const totalLitres = activity.reduce((sum, row) => sum + Number(row.litres || 0), 0);
        const totalSales = activity.reduce((sum, row) => sum + Number(row.amount || 0), 0);
        const topShift = [...activity].sort((a, b) => Number(b.litres) - Number(a.litres))[0];
        const stationActivity = Object.values(activity.reduce((grouped, row) => {
          const station = (response.records?.shifts || []).find(item => Number(item.id) === Number(row.shift_id))?.station || 'Unknown';
          grouped[station] ??= { station, shifts: 0, litres: 0, amount: 0 };
          grouped[station].shifts += 1; grouped[station].litres += Number(row.litres || 0); grouped[station].amount += Number(row.amount || 0);
          return grouped;
        }, {}));
        openDetailDrawer({
          title: 'Shifts',
          subtitle: `Shift activity · ${formatDate(response.meta.from_date)} – ${formatDate(response.meta.to_date)}`,
          chips: [['Total shifts', formatNumber(shifts.length)], ['Closed', formatNumber(shifts.filter(row => String(row.status).toLowerCase() === 'closed').length)], ['Open', formatNumber(shifts.filter(row => String(row.status).toLowerCase() === 'open').length)], ['Litres', `${formatNumber(totalLitres)} L`], ['Sales', formatCurrency(totalSales)], ['Top shift', topShift?.label || '—']],
          charts: [
            { title: 'Latest shift activity (litres)', type: 'bar', labels: activity.slice(-24).map(row => row.label), datasets: [{ label: 'Litres', data: activity.slice(-24).map(row => row.litres), backgroundColor: '#4a90d9' }] },
            { title: 'Litres by station', type: 'bar', horizontal: true, labels: stationActivity.map(row => row.station), datasets: [{ label: 'Litres', data: stationActivity.map(row => row.litres), backgroundColor: '#0e8fa6' }] }
          ],
          tables: [
            { title: 'Station-wise activity', columns: [{ key: 'station', label: 'Station' }, { key: 'shifts', label: 'Shifts' }, { key: 'litres', label: 'Litres' }, { key: 'amount', label: 'Sales' }], rows: stationActivity },
            { title: 'Shift closing records', columns: [{ key: 'id', label: 'ID' }, { key: 'station', label: 'Station' }, { key: 'shift', label: 'Shift' }, { key: 'start', label: 'Start' }, { key: 'end', label: 'End' }, { key: 'status', label: 'Status' }], rows: shifts }
          ]
        });
      }

      function openCreditDetails() {
        const response = dashboardState.response;
        const credits = response.records?.party_credit || [];
        const stationRows = response.station_finance.map(row => ({ station_name: row.station_name, credit: Number(row.credit || 0) })).sort((a, b) => b.credit - a.credit);
        const total = stationRows.reduce((sum, row) => sum + row.credit, 0);
        openDetailDrawer({
          title: 'Party credit (fuel)',
          subtitle: `Outstanding credit · ${formatDate(response.meta.from_date)} – ${formatDate(response.meta.to_date)}`,
          chips: [['Total credit', formatCurrency(total)], ['Entries', formatNumber(credits.length)], ['Stations', formatNumber(stationRows.filter(row => row.credit > 0).length)], ['Top station', stationRows[0]?.station_name || '—'], ['Highest station credit', formatCurrency(stationRows[0]?.credit || 0)], ['Credit / sales', `${formatNumber(response.summary.fuel_sales ? total / response.summary.fuel_sales * 100 : 0)}%`]],
          charts: [
            { title: 'Credit by station (Rs)', type: 'bar', horizontal: true, labels: stationRows.map(row => row.station_name), datasets: [{ label: 'Credit', data: stationRows.map(row => row.credit), backgroundColor: '#8b5cf6' }] },
            { title: 'Station credit share', type: 'doughnut', labels: stationRows.map(row => row.station_name), datasets: [{ data: stationRows.map(row => row.credit), backgroundColor: chartPalette }] }
          ],
          tables: [
            { title: 'Station-wise credit', columns: [{ key: 'station_name', label: 'Station' }, { key: 'credit', label: 'Outstanding credit' }], rows: stationRows },
            { title: 'Party ledger', columns: [{ key: 'station', label: 'Station' }, { key: 'shift', label: 'Shift' }, { key: 'customer', label: 'Customer' }, { key: 'given_to', label: 'Given to' }, { key: 'vehicle', label: 'Vehicle' }, { key: 'amount', label: 'Amount' }, { key: 'date', label: 'Date' }], rows: credits }
          ]
        });
      }

      function openStockDetails(isLoss) {
        const response = dashboardState.response;
        const rows = response.tank_variances.filter(row => isLoss ? Number(row.variance_litres) < 0 : Number(row.variance_litres) > 0).sort((a, b) => isLoss ? Number(a.variance_litres) - Number(b.variance_litres) : Number(b.variance_litres) - Number(a.variance_litres));
        const field = isLoss ? 'loss' : 'gain';
        const colour = isLoss ? '#e0453f' : '#12a05c';
        const stationRows = response.station_finance.map(row => ({ station_name: row.station_name, value: Number(row[field] || 0) })).sort((a, b) => b.value - a.value);
        const totalValue = stationRows.reduce((sum, row) => sum + row.value, 0);
        const totalLitres = rows.reduce((sum, row) => sum + Math.abs(Number(row.variance_litres || 0)), 0);
        openDetailDrawer({
          title: isLoss ? 'Stock loss' : 'Stock gain',
          subtitle: `Dip stock versus book stock · ${formatDate(response.meta.from_date)} – ${formatDate(response.meta.to_date)}`,
          chips: [['Total value', formatCurrency(totalValue)], ['Variance litres', `${formatNumber(totalLitres)} L`], ['Tanks', formatNumber(rows.length)], [isLoss ? 'Worst station' : 'Best station', stationRows[0]?.station_name || '—'], ['Highest value', formatCurrency(stationRows[0]?.value || 0)], ['Stations', formatNumber(stationRows.filter(row => row.value > 0).length)]],
          charts: [
            { title: `${isLoss ? 'Loss' : 'Gain'} by station (Rs)`, type: 'bar', horizontal: true, labels: stationRows.map(row => row.station_name), datasets: [{ label: isLoss ? 'Loss' : 'Gain', data: stationRows.map(row => row.value), backgroundColor: colour }] },
            { title: 'Variance by tank (L)', type: 'bar', labels: rows.slice(0, 20).map(row => `${row.station_name} · ${row.tank_name}`), datasets: [{ label: 'Litres', data: rows.slice(0, 20).map(row => Math.abs(Number(row.variance_litres))), backgroundColor: colour }] }
          ],
          tables: [
            { title: 'Tank details', columns: [{ key: 'station_name', label: 'Station' }, { key: 'tank_name', label: 'Tank' }, { key: 'product_name', label: 'Product' }, { key: 'opening_litres', label: 'Opening' }, { key: 'closing_litres', label: 'Closing' }, { key: 'variance_litres', label: 'Variance' }, { key: 'variance_value', label: 'Value' }, { key: 'status', label: 'Result' }], rows },
            { title: 'Station-wise', columns: [{ key: 'station_name', label: 'Station' }, { key: 'value', label: isLoss ? 'Stock loss' : 'Stock gain' }], rows: stationRows }
          ]
        });
      }

      function openFinanceDetails(isIncome) {
        const response = dashboardState.response;
        const key = isIncome ? 'income' : 'expense';
        const colour = isIncome ? '#0e8fa6' : '#f59e0b';
        const stations = [...response.station_finance].sort((a, b) => Number(b[key]) - Number(a[key]));
        const transactions = (response.records?.transactions || []).filter(row => row.type === key);
        const total = stations.reduce((sum, row) => sum + Number(row[key] || 0), 0);
        const incomeTotal = response.station_finance.reduce((sum, row) => sum + Number(row.income || 0), 0);
        const expenseTotal = response.station_finance.reduce((sum, row) => sum + Number(row.expense || 0), 0);
        openDetailDrawer({
          title: isIncome ? 'Income' : 'Expenses',
          subtitle: `Accounts transactions · ${formatDate(response.meta.from_date)} – ${formatDate(response.meta.to_date)}`,
          chips: [['Total', formatCurrency(total)], ['Top station', stations[0]?.station_name || '—'], ['Highest amount', formatCurrency(stations[0]?.[key] || 0)], ['Income', formatCurrency(incomeTotal)], ['Expenses', formatCurrency(expenseTotal)], ['Net', formatCurrency(incomeTotal - expenseTotal)]],
          charts: [
            { title: `${isIncome ? 'Income' : 'Expense'} by station (Rs)`, type: 'bar', horizontal: true, labels: stations.map(row => row.station_name), datasets: [{ label: isIncome ? 'Income' : 'Expense', data: stations.map(row => row[key]), backgroundColor: colour }] },
            { title: 'Income versus expense', type: 'bar', labels: stations.map(row => row.station_name), datasets: [{ label: 'Income', data: stations.map(row => row.income), backgroundColor: '#0e8fa6' }, { label: 'Expense', data: stations.map(row => row.expense), backgroundColor: '#f59e0b' }] },
            { title: `${isIncome ? 'Income' : 'Expense'} share`, type: 'doughnut', labels: stations.map(row => row.station_name), datasets: [{ data: stations.map(row => row[key]), backgroundColor: chartPalette }] }
          ],
          tables: [
            { title: 'Station finance', columns: [{ key: 'station_name', label: 'Station' }, { key: 'income', label: 'Income' }, { key: 'expense', label: 'Expense' }, { key: 'credit', label: 'Credit' }, { key: 'loss', label: 'Loss' }, { key: 'gain', label: 'Gain' }], rows: stations },
            { title: `${isIncome ? 'Income' : 'Expense'} transactions`, columns: [{ key: 'date', label: 'Date' }, { key: 'station', label: 'Station' }, { key: 'shift', label: 'Shift' }, { key: 'account', label: 'Account' }, { key: 'method', label: 'Method' }, { key: 'amount', label: 'Amount' }, { key: 'note', label: 'Note' }], rows: transactions }
          ]
        });
      }

      function openSummaryCardDetails(index) {
        if (index === 0) return openSalesSummaryDetails('sales');
        if (index === 1) return openSalesSummaryDetails('litres');
        if (index === 2) return openAllShiftsDetails();
        if (index === 3) return openCashDistributionDetails();
        if (index === 4) return openNozzleSalesDetails();
        if (index === 5) return openCreditDetails();
        if (index === 6) return openStockDetails(true);
        if (index === 7) return openStockDetails(false);
        if (index === 8) return openFinanceDetails(true);
        if (index === 9) return openFinanceDetails(false);
      }

      function openStationDetails(stationName) {
        const response = dashboardState.response;
        const station = response.station_performance.find(item => item.station_name === stationName) || {};
        const finance = response.station_finance.find(item => item.station_name === stationName) || {};
        const nozzles = response.nozzle_sales.filter(item => item.station_name === stationName);
        const tanks = response.tank_levels.filter(item => item.station_name === stationName);
        const variances = response.tank_variances.filter(item => item.station_name === stationName);
        const transactions = (response.records?.transactions || []).filter(item => item.station === stationName);
        const shifts = (response.records?.shifts || []).filter(item => item.station === stationName);
        const products = Object.values(nozzles.reduce((grouped, item) => {
          grouped[item.product_name] ??= { name: item.product_name, litres: 0 };
          grouped[item.product_name].litres += Number(item.litres || 0);
          return grouped;
        }, {}));
        openDetailDrawer({
          title: stationName,
          subtitle: `Station overview · ${formatDate(response.meta.from_date)} – ${formatDate(response.meta.to_date)}`,
          chips: [['Litres', `${formatNumber(station.litres || 0)} L`], ['Sales', formatCurrency(station.amount || 0)], ['Income', formatCurrency(finance.income || 0)], ['Expense', formatCurrency(finance.expense || 0)], ['Credit', formatCurrency(finance.credit || 0)], ['Loss', formatCurrency(finance.loss || 0)], ['Gain', formatCurrency(finance.gain || 0)], ['Tanks', formatNumber(tanks.length)], ['Shifts', formatNumber(shifts.length)]],
          charts: [
            { title: 'Product-wise litres', type: 'doughnut', labels: products.map(item => item.name), datasets: [{ data: products.map(item => item.litres), backgroundColor: chartPalette }] },
            { title: 'Nozzle-wise litres', type: 'bar', labels: nozzles.map(item => item.label), datasets: [{ label: 'Litres', data: nozzles.map(item => item.litres), backgroundColor: '#0e8fa6' }] },
            { title: 'Tank fill levels', type: 'bar', labels: tanks.map(item => item.tank_name), datasets: [{ label: 'Fill %', data: tanks.map(item => item.fill_percentage), backgroundColor: '#12a05c' }] },
            { title: 'Station finance', type: 'bar', labels: ['Income', 'Expense', 'Credit', 'Loss', 'Gain'], datasets: [{ data: [finance.income || 0, finance.expense || 0, finance.credit || 0, finance.loss || 0, finance.gain || 0], backgroundColor: ['#0e8fa6', '#f59e0b', '#8b5cf6', '#e0453f', '#12a05c'] }] }
          ],
          tables: [
            { title: 'Nozzle sales', columns: [{ key: 'label', label: 'Nozzle' }, { key: 'product_name', label: 'Product' }, { key: 'litres', label: 'Litres' }, { key: 'amount', label: 'Amount' }], rows: nozzles },
            { title: 'Tank stock variance', columns: [{ key: 'tank_name', label: 'Tank' }, { key: 'product_name', label: 'Product' }, { key: 'opening_litres', label: 'Opening' }, { key: 'closing_litres', label: 'Closing' }, { key: 'variance_litres', label: 'Variance' }, { key: 'status', label: 'Result' }], rows: variances },
            { title: 'Transactions', columns: [{ key: 'date', label: 'Time' }, { key: 'shift', label: 'Shift' }, { key: 'account', label: 'Account' }, { key: 'type', label: 'Type' }, { key: 'method', label: 'Method' }, { key: 'amount', label: 'Amount' }, { key: 'note', label: 'Note' }], rows: transactions }
          ]
        });
      }

      function bindDashboardDetails() {
        elements.kpis.querySelectorAll('.kpi').forEach((card, index) => card.addEventListener('click', () => openSummaryCardDetails(index)));
        elements.detailClose.onclick = closeDetailDrawer;
        elements.detailOverlay.onclick = closeDetailDrawer;
      }

      document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeDetailDrawer();
      });

      function renderTankLevels(tanks) {
        const visibleTanks = tanks.slice(0, 3);
        elements.tanks.innerHTML = visibleTanks.length ? visibleTanks.map(tank => `<div class="tank" data-tank-id="${tank.tank_id}" role="button" tabindex="0">
          <div class="nm">${escapeHtml(tank.product_name)}<small>${escapeHtml(tank.station_name)} · ${escapeHtml(tank.tank_name)}</small></div>
          <div class="bar"><span style="width:${tank.fill_percentage}%;background:#12a05c"></span></div>
          <div class="lv">${formatNumber(tank.stock_litres)} / ${formatNumber(tank.capacity)} L<br>(${formatNumber(tank.fill_percentage)}%)</div>
        </div>`).join('') + (tanks.length > 3 ? `<button type="button" class="tank-view-all" id="viewAllTanks">View all ${formatNumber(tanks.length)} tanks</button>` : '') : '<div class="dashboard-empty">No tank dip found.</div>';
        elements.tanks.querySelectorAll('[data-tank-id]').forEach(tankElement => {
          const open = () => openTankDetails(Number(tankElement.dataset.tankId));
          tankElement.addEventListener('click', open);
          tankElement.addEventListener('keydown', event => { if (event.key === 'Enter') open(); });
        });
        document.getElementById('viewAllTanks')?.addEventListener('click', openAllTanks);
      }

      function openAllTanks() {
        const tanks = dashboardState.response.tank_levels;
        openDetailDrawer({
          title: 'Tank dip levels',
          subtitle: 'Latest tank readings in the selected range',
          chips: [
            ['Tanks', formatNumber(tanks.length)],
            ['Total capacity', `${formatNumber(tanks.reduce((total, tank) => total + Number(tank.capacity || 0), 0))} L`],
            ['Current stock', `${formatNumber(tanks.reduce((total, tank) => total + Number(tank.stock_litres || 0), 0))} L`]
          ],
          columns: [
            { key: 'station_name', label: 'Station' }, { key: 'tank_name', label: 'Tank' },
            { key: 'product_name', label: 'Product' }, { key: 'capacity', label: 'Capacity (L)' },
            { key: 'stock_litres', label: 'Stock (L)' }, { key: 'dip_litres', label: 'Latest dip (L)' }, { key: 'fill_percentage', label: 'Fill %' }
          ],
          rows: tanks,
          charts: [{ title: 'Tank fill level (%)', type: 'bar', labels: tanks.map(tank => `${tank.station_name} · ${tank.tank_name}`), datasets: [{ label: 'Fill %', data: tanks.map(tank => tank.fill_percentage), backgroundColor: '#12a05c' }] }]
        });
      }

      function openTankDetails(tankId) {
        const tank = dashboardState.response.tank_levels.find(item => Number(item.tank_id) === tankId);
        if (!tank) return;
        const variances = dashboardState.response.tank_variances.filter(item => Number(item.tank_id) === tankId);
        openDetailDrawer({
          title: `${tank.product_name} · ${tank.tank_name}`,
          subtitle: tank.station_name,
          chips: [['Stock', `${formatNumber(tank.stock_litres)} L`], ['Capacity', `${formatNumber(tank.capacity)} L`], ['Fill level', `${formatNumber(tank.fill_percentage)}%`], ['Latest physical dip', `${formatNumber(tank.dip_litres)} L`]],
          columns: [
            { key: 'shift_id', label: 'Shift' }, { key: 'opening_litres', label: 'Opening' },
            { key: 'closing_litres', label: 'Closing' }, { key: 'oil_received_litres', label: 'Received' },
            { key: 'nozzle_sales_litres', label: 'Nozzle sales' }, { key: 'reset_sales_litres', label: 'Reset sales' },
            { key: 'variance_litres', label: 'Variance' }, { key: 'status', label: 'Result' }
          ],
          rows: variances,
          chart: { title: 'Inventory stock and empty capacity', type: 'doughnut', labels: ['Stock', 'Empty'], datasets: [{ data: [Math.min(tank.stock_litres, tank.capacity), Math.max(0, tank.capacity - tank.stock_litres)], backgroundColor: ['#12a05c', '#d5dae1'] }] }
        });
      }

      function renderInsightCards(finances, variances) {
        const highest = (rows, field) => [...rows].sort((a, b) => b[field] - a[field])[0];
        const worstTank = [...variances].sort((a, b) => a.variance_litres - b.variance_litres)[0];
        const bestTank = [...variances].sort((a, b) => b.variance_litres - a.variance_litres)[0];
        const insightRows = [
          ['Highest income', highest(finances, 'income'), 'income'], ['Highest expense', highest(finances, 'expense'), 'expense'],
          ['Highest credit', highest(finances, 'credit'), 'credit'], ['Highest loss', highest(finances, 'loss'), 'loss'],
          ['Highest gain', highest(finances, 'gain'), 'gain']
        ];
        const cards = insightRows.filter(([, row]) => row).map(([label, row, field]) =>
          `<div class="card"><small>${label}</small><b>${escapeHtml(row.station_name)}</b><span>${formatCurrency(row[field])}</span></div>`
        );
        if (worstTank) cards.push(`<div class="card"><small>Worst tank</small><b>${escapeHtml(worstTank.tank_name)}</b><span>${formatNumber(worstTank.variance_litres)} L</span></div>`);
        if (bestTank) cards.push(`<div class="card"><small>Best tank</small><b>${escapeHtml(bestTank.tank_name)}</b><span>${formatNumber(bestTank.variance_litres)} L</span></div>`);
        elements.insights.innerHTML = cards.length ? cards.join('') : '<div class="card dashboard-empty">No insight data available.</div>';
      }

      const recordDefinitions = {
        nozzle_readings: { label: 'Nozzle readings', columns: ['station', 'shift', 'dispenser', 'nozzle', 'product', 'opening', 'closing', 'litres', 'rate', 'amount'] },
        shifts: { label: 'Shifts', columns: ['id', 'station', 'shift', 'start', 'end', 'status'] },
        transactions: { label: 'Transactions', columns: ['station', 'shift', 'account', 'type', 'method', 'amount', 'note', 'date'] },
        party_credit: { label: 'Party credit', columns: ['station', 'shift', 'customer', 'given_to', 'vehicle', 'amount', 'date'] },
        tank_variance: { label: 'Tank loss / gain', columns: ['station_name', 'tank_name', 'product_name', 'opening_litres', 'closing_litres', 'oil_received_litres', 'nozzle_sales_litres', 'reset_sales_litres', 'variance_litres', 'variance_value', 'status'] }
      };

      function renderRecordTables(records) {
        elements.tabs.innerHTML = Object.entries(recordDefinitions).map(([key, definition]) =>
          `<button type="button" class="${key === dashboardState.activeRecordKey ? 'on' : ''}" data-record-key="${key}">${definition.label}</button>`
        ).join('');
        renderActiveRecordTable(records);
        elements.tabs.querySelectorAll('[data-record-key]').forEach(button => button.addEventListener('click', () => {
          dashboardState.activeRecordKey = button.dataset.recordKey;
          dashboardState.recordPage = 1;
          dashboardState.recordSearch = '';
          elements.tabs.querySelectorAll('button').forEach(tab => tab.classList.toggle('on', tab === button));
          renderActiveRecordTable(dashboardState.response.records);
        }));
      }

      function renderActiveRecordTable(records) {
        if (dashboardState.recordDataTable) {
          dashboardState.recordDataTable.destroy();
          dashboardState.recordDataTable = null;
        }
        const key = dashboardState.activeRecordKey;
        const definition = recordDefinitions[key];
        const rows = records[key] || [];
        const search = dashboardState.recordSearch.trim().toLowerCase();
        const filteredRows = search ? rows.filter(row => definition.columns.some(column => String(row[column] ?? '').toLowerCase().includes(search))) : rows;
        const totalPages = Math.max(1, Math.ceil(filteredRows.length / dashboardState.recordPageLength));
        dashboardState.recordPage = Math.min(dashboardState.recordPage, totalPages);
        const start = (dashboardState.recordPage - 1) * dashboardState.recordPageLength;
        const visibleRows = filteredRows.slice(start, start + dashboardState.recordPageLength);
        elements.panes.innerHTML = `<div class="pane on">
          <div class="dashboard-table-tools"><label>Show <select id="dashboardRecordLength"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select> entries</label><label>Search: <input id="dashboardRecordSearch" type="search" value="${escapeHtml(dashboardState.recordSearch)}"></label></div>
          <div style="overflow:auto"><table class="dt2" id="dashboardRecordsTable">
            <thead><tr>${definition.columns.map(column => `<th>${escapeHtml(column.replaceAll('_', ' '))}</th>`).join('')}</tr></thead>
            <tbody>${visibleRows.length ? visibleRows.map(row => `<tr>${definition.columns.map(column => `<td>${formatCell(row[column])}</td>`).join('')}</tr>`).join('') : `<tr><td colspan="${definition.columns.length}" class="dashboard-empty">No records found.</td></tr>`}</tbody>
          </table></div>
          <div class="dashboard-table-footer"><span>Showing ${filteredRows.length ? start + 1 : 0} to ${Math.min(start + dashboardState.recordPageLength, filteredRows.length)} of ${formatNumber(filteredRows.length)} entries</span><div><button type="button" id="dashboardRecordPrevious" ${dashboardState.recordPage <= 1 ? 'disabled' : ''}>Previous</button> <span>Page ${dashboardState.recordPage} of ${totalPages}</span> <button type="button" id="dashboardRecordNext" ${dashboardState.recordPage >= totalPages ? 'disabled' : ''}>Next</button></div></div>
        </div>`;
        const lengthSelect = document.getElementById('dashboardRecordLength');
        lengthSelect.value = String(dashboardState.recordPageLength);
        lengthSelect.addEventListener('change', event => { dashboardState.recordPageLength = Number(event.target.value); dashboardState.recordPage = 1; renderActiveRecordTable(records); });
        document.getElementById('dashboardRecordSearch').addEventListener('input', event => { dashboardState.recordSearch = event.target.value; dashboardState.recordPage = 1; renderActiveRecordTable(records); document.getElementById('dashboardRecordSearch')?.focus(); });
        document.getElementById('dashboardRecordPrevious').addEventListener('click', () => { dashboardState.recordPage -= 1; renderActiveRecordTable(records); });
        document.getElementById('dashboardRecordNext').addEventListener('click', () => { dashboardState.recordPage += 1; renderActiveRecordTable(records); });
      }

      function exportActiveTableToCsv() {
        if (!dashboardState.response) return;
        const key = dashboardState.activeRecordKey;
        const definition = recordDefinitions[key];
        const rows = dashboardState.response.records[key] || [];
        const csvRows = [definition.columns, ...rows.map(row => definition.columns.map(column => row[column] ?? ''))];
        const csv = csvRows.map(row => row.map(value => `"${String(value).replaceAll('"', '""')}"`).join(',')).join('\r\n');
        const link = document.createElement('a');
        link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
        link.download = `pump360-${key}-${elements.fromDate.value}-to-${elements.toDate.value}.csv`;
        link.click();
        URL.revokeObjectURL(link.href);
      }

      document.getElementById('applyDashboardFilters').addEventListener('click', fetchDashboardData);
      document.getElementById('exportDashboardCsv').addEventListener('click', exportActiveTableToCsv);
      document.getElementById('printDashboard').addEventListener('click', () => window.print());
      setDefaultDates();
      fetchDashboardData();
    })();
  </script>
@endsection
@section('content')

  <div id="pump360-main-dashboard">
    <div class="bar0">
      <div>
        <h2>Complete Overview</h2>
        <p id="sub">Loading live dashboard data...</p>
      </div>
      <div class="fl">
        <label class="dashboard-filter"><span>From</span><input type="date" id="dashboardFromDate"></label>
        <label class="dashboard-filter"><span>To</span><input type="date" id="dashboardToDate"></label>
        <select id="st" aria-label="Station">
          <option value="">All stations</option>
        </select>
        <div class="seg" id="dashboardProductSegment" aria-label="Product filter"></div>
        <button type="button" class="dashboard-action" id="applyDashboardFilters">Apply</button>
        <button type="button" class="dashboard-action secondary" id="exportDashboardCsv">Export CSV</button>
        <button type="button" class="dashboard-action secondary" id="printDashboard">Print / PDF</button>
      </div>
    </div>
    <div class="body">
      <div class="grid kpis" id="kpis"></div>
      <div class="grid pcs" id="pcs"></div>
      <div class="grid g2">
        <div class="card">
          <h3>Sales trend</h3>
          <div class="sub">Litres per product in the selected range</div>
          <div class="ch"><canvas id="cTrend"></canvas></div>
        </div>
        <div class="card">
          <h3>Product share</h3>
          <div class="sub">Litres sold in the selected range</div>
          <div class="ch"><canvas id="cProd"></canvas></div>
        </div>
      </div>
      <div class="card" style="margin-bottom:16px">
        <h3>Nozzle-wise sales</h3>
        <div class="sub">Top 15 nozzles by litres — click the graph for complete details</div>
        <div class="ch"><canvas id="cNoz"></canvas></div>
      </div>
      <div class="grid g22">
        <div class="card">
          <h3>Highest selling stations</h3>
          <div class="sub">Top 5 by litres</div>
          <div class="ch sm"><canvas id="cTop"></canvas></div>
        </div>
        <div class="card">
          <h3>Lowest selling stations</h3>
          <div class="sub">Bottom 5 by litres, needs attention</div>
          <div class="ch sm"><canvas id="cLow"></canvas></div>
        </div>
      </div>
      <div class="grid g3">
        <div class="card">
          <h3>Shift activity</h3>
          <div class="sub">Litres sold per shift in the selected range</div>
          <div class="ch sm"><canvas id="cHour"></canvas></div>
        </div>
        <div class="card">
          <h3>Cash distribution</h3>
          <div class="sub">Shift closing split</div>
          <div class="ch sm"><canvas id="cCash"></canvas></div>
        </div>
        <div class="card">
          <h3>Tank dip levels</h3>
          <div class="sub">Latest dip reading</div>
          <div id="tanks"></div>
        </div>
      </div>
      <h3 style="margin:8px 0 12px">Stock loss and gain, income, expense and credit</h3>
      <div class="grid ins" id="ins"></div>
      <div class="grid g22">
        <div class="card">
          <h3>Loss by station</h3>
          <div class="sub">Stock loss value (dip vs book), highest first</div>
          <div class="ch sm"><canvas id="cLossS"></canvas></div>
        </div>
        <div class="card">
          <h3>Gain by station</h3>
          <div class="sub">Stock gain value, highest first</div>
          <div class="ch sm"><canvas id="cGainS"></canvas></div>
        </div>
      </div>
      <div class="grid g22">
        <div class="card">
          <h3>Product-wise loss and gain</h3>
          <div class="sub">Value in Rs for the selected station</div>
          <div class="ch sm"><canvas id="cProdLG"></canvas></div>
        </div>
        <div class="card">
          <h3>Tank-wise loss and gain</h3>
          <div class="sub">Litres per tank, red is loss and green is gain</div>
          <div class="ch sm"><canvas id="cTankLG"></canvas></div>
        </div>
      </div>
      <div class="grid g3">
        <div class="card">
          <h3>Income by station</h3>
          <div class="sub">Highest first</div>
          <div class="ch sm"><canvas id="cInc"></canvas></div>
        </div>
        <div class="card">
          <h3>Expense by station</h3>
          <div class="sub">Highest first</div>
          <div class="ch sm"><canvas id="cExp"></canvas></div>
        </div>
        <div class="card">
          <h3>Credit by station</h3>
          <div class="sub">Outstanding party credit, highest first</div>
          <div class="ch sm"><canvas id="cCre"></canvas></div>
        </div>
      </div>
      <h3 style="margin:8px 0 12px">More views</h3>
      <div class="grid g22">
        <div class="card">
          <h3>Station by product</h3>
          <div class="sub">Stacked litres, click a bar for station details</div>
          <div class="ch sm"><canvas id="xStack"></canvas></div>
        </div>
        <div class="card">
          <h3>Income, expense and net</h3>
          <div class="sub">Bars with a net profit line, click a station</div>
          <div class="ch sm"><canvas id="xMix"></canvas></div>
        </div>
      </div>
      <div class="grid g3">
        <div class="card">
          <h3>Station performance matrix</h3>
          <div class="sub">Income vs net stock result; bubble size represents litres sold</div>
          <div class="ch sm"><canvas id="xRad"></canvas></div>
        </div>
        <div class="card">
          <h3>Expense distribution</h3>
          <div class="sub">Professional doughnut share by station</div>
          <div class="ch sm"><canvas id="xPol"></canvas></div>
        </div>
        <div class="card">
          <h3>Nozzle litres vs amount</h3>
          <div class="sub">Bubble size is rate, colour is product</div>
          <div class="ch sm"><canvas id="xBub"></canvas></div>
        </div>
      </div>
      <div class="card">
        <h3>Records</h3>
        <div class="sub">Follows the station and product filters above</div>
        <div class="tabs" id="tabs"></div>
        <div id="panes"></div>
      </div>
    </div>

    <div class="ov" id="dOv"></div>
    <div class="dr" id="dR" role="dialog" aria-label="Details">
      <div class="dh">
        <div>
          <h3 id="dT"></h3>
          <p id="dS"></p>
        </div><button id="dX" aria-label="Close details">&times;</button>
      </div>
      <div class="chips" id="dChips"></div>
      <div class="dbox" id="dBox"><canvas id="dCv"></canvas></div>
      <div id="dTb"></div>
    </div>
    <div class="dashboard-loader" id="dashboardLoader" aria-live="polite">
      <div class="dashboard-spinner"></div>
      <strong>Dashboard data loading...</strong>
    </div>
    <div class="dashboard-error" id="dashboardError" role="alert"></div>
  </div>
@endsection
@section('legacy-dashboard-js')

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    const rs = n => 'Rs ' + Math.round(n).toLocaleString(), nf = n => Math.round(n).toLocaleString(), f1 = n => Number(n).toLocaleString(undefined, { minimumFractionDigits: 1, maximumFractionDigits: 1 });
    const tag = p => `<span class="tag t-${p}">${p}</span>`;
    const P = ['Petrol', 'Diesel', 'Hi-Octane'], PC = { Petrol: '#f59e0b', Diesel: '#0e8fa6', 'Hi-Octane': '#8b5cf6' }, PR = { Petrol: 288.2, Diesel: 291.5, 'Hi-Octane': 305 }, BASE = { Petrol: 2400, Diesel: 1500, 'Hi-Octane': 640 }, CAP = { Petrol: 30000, Diesel: 30000, 'Hi-Octane': 15000 };
    const ST = ['Lahore Ring Rd', 'Karachi Shahrah-e-Faisal', 'Islamabad Blue Area', 'Multan Bosan Rd', 'Faisalabad Jhang Rd', 'Khuzdar Hwy'], F = [1, .9, .77, .69, .61, .08];
    const NZ = [], TK = [];
    ST.forEach((s, si) => {
      ['Petrol', 'Petrol', 'Diesel', 'Diesel', 'Hi-Octane'].forEach((p, ni) => { const l = Math.round(BASE[p] * F[si] * (.9 + ((si * 7 + ni * 3) % 5) / 20) * 10) / 10, o = 1000000 + si * 100000 + ni * 50000; NZ.push({ s: si, id: 'N' + (ni + 1), d: 'D' + (Math.floor(ni / 2) + 1), p, o, c: o + l, l, a: l * PR[p] }) });
      P.forEach((p, pi) => TK.push({ s: si, p, cap: CAP[p], dip: Math.round(CAP[p] * (((si * 13 + pi * 29) % 70) + 12) / 100), v: ((si * 5 + pi * 7) % 11 - 5) * (30 + si * 8) }))
    });
    const HR = [22, 10, 14, 48, 95, 120, 132, 118, 141, 160, 128, 64], MULT = [.9, .97, .89, 1.02, 1.08, 1.2, 1.12], DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    let st = -1, pr = 'all', tab = 0;
    const sum = (a, k) => a.reduce((x, y) => x + y[k], 0);

    document.getElementById('st').innerHTML = '<option value="-1">All stations</option>' + ST.map((s, i) => `<option value="${i}">${s}</option>`).join('');
    const names = ['Nozzle readings', 'Product-wise sales', 'Shift closing', 'Oil purchases', 'Fuel to party', 'Fuel layers', 'Tank loss / gain', 'Station finance', 'Transactions (24h)'];
    tabs.innerHTML = names.map((n, i) => `<button class="${i ? '' : 'on'}" data-i="${i}">${n}</button>`).join('');

    const dark = matchMedia('(prefers-color-scheme: dark)').matches, g = { color: dark ? '#243240' : '#eaeef3' };
    Chart.defaults.font.family = 'Manrope,system-ui,sans-serif'; Chart.defaults.maintainAspectRatio = false; Chart.defaults.plugins.legend.labels.usePointStyle = true; if (dark) Chart.defaults.color = '#92a0ad';
    const mk = (el, cfg) => new Chart(el, cfg), C = {};
    C.trend = mk(cTrend, { type: 'line', data: { labels: DAYS, datasets: [] }, options: { interaction: { mode: 'index', intersect: false }, scales: { y: { grid: g }, x: { grid: { display: false } } } } });
    C.prod = mk(cProd, { type: 'doughnut', data: { labels: P, datasets: [{ data: [], backgroundColor: P.map(p => PC[p]), borderWidth: 0 }] }, options: { cutout: '65%', plugins: { legend: { position: 'bottom' } } } });
    C.noz = mk(cNoz, { type: 'bar', data: { labels: [], datasets: [{ label: 'Litres', data: [], backgroundColor: [], borderRadius: 6 }] }, options: { plugins: { legend: { display: false } }, scales: { y: { grid: g }, x: { grid: { display: false } } } } });
    const hbo = { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { grid: g }, y: { grid: { display: false } } } };
    C.top = mk(cTop, { type: 'bar', data: { labels: [], datasets: [{ label: 'Litres', data: [], backgroundColor: [], borderRadius: 6 }] }, options: hbo });
    C.low = mk(cLow, { type: 'bar', data: { labels: [], datasets: [{ label: 'Litres', data: [], backgroundColor: [], borderRadius: 6 }] }, options: hbo });
    C.hour = mk(cHour, { type: 'bar', data: { labels: HR.map((_, i) => i * 2 + ':00'), datasets: [{ label: 'Transactions', data: [], backgroundColor: '#4a90d9', borderRadius: 4 }] }, options: { plugins: { legend: { display: false } }, scales: { y: { grid: g }, x: { grid: { display: false } } } } });
    C.cash = mk(cCash, { type: 'doughnut', data: { labels: ['Cash', 'Card', 'Credit/Party', 'Fuel card'], datasets: [{ data: [], backgroundColor: ['#12a05c', '#4a90d9', '#e0453f', '#f59e0b'], borderWidth: 0 }] }, options: { cutout: '62%', plugins: { legend: { position: 'bottom' } } } });

    const hbc = el => mk(el, { type: 'bar', data: { labels: [], datasets: [{ label: 'Rs', data: [], backgroundColor: [], borderRadius: 6 }] }, options: hbo });
    C.lossS = hbc(cLossS); C.gainS = hbc(cGainS); C.inc = hbc(cInc); C.exp = hbc(cExp); C.cre = hbc(cCre);
    C.prodLG = mk(cProdLG, { type: 'bar', data: { labels: P, datasets: [{ label: 'Loss (Rs)', data: [], backgroundColor: '#e0453f', borderRadius: 6 }, { label: 'Gain (Rs)', data: [], backgroundColor: '#12a05c', borderRadius: 6 }] }, options: { scales: { y: { grid: g }, x: { grid: { display: false } } } } });
    C.tankLG = mk(cTankLG, { type: 'bar', data: { labels: [], datasets: [{ label: 'Litres', data: [], backgroundColor: [], borderRadius: 4 }] }, options: { plugins: { legend: { display: false } }, scales: { y: { grid: g }, x: { grid: { display: false } } } } });
    const PAL = ['#0e8fa6', '#f59e0b', '#8b5cf6', '#12a05c', '#e0453f', '#4a90d9', '#ec4899', '#64748b', '#84cc16', '#14b8a6'];
    function apply(c) {
      const t = c._t, pie = t == 'pie' || t == 'doughnut';
      c.config.type = t;
      const oo = pie ? { cutout: t == 'doughnut' ? '62%' : 0, plugins: { legend: { display: c.data.labels.length <= 10, position: 'bottom' } } } : { indexAxis: c._h ? 'y' : 'x', interaction: c._i ? { mode: 'index', intersect: false } : undefined, plugins: { legend: { display: !!c._leg } }, scales: c._h ? { x: { grid: g }, y: { grid: { display: false } } } : { y: { grid: g }, x: { grid: { display: false } } } };
      oo.onClick = (e, els) => { if (els.length) chartClick(c, els[0]) }; oo.onHover = hv; c.options = oo;
      c.data.datasets.forEach(d => {
        if (c._pal) d.backgroundColor = pie ? c.data.labels.map((_, i) => PAL[i % PAL.length]) : (c._bg0 || d.backgroundColor);
        if (t == 'line') { const b = d.backgroundColor; d.borderColor = d.borderColor || (Array.isArray(b) ? b[0] : b); d.pointBackgroundColor = b; d.tension = .35 }
        else if (t == 'bar' && c == C.trend) d.backgroundColor = d.borderColor;
      });
    }
    const ALLOW = { trend: ['line', 'bar'], prod: ['doughnut', 'pie', 'bar'], noz: ['bar', 'line', 'pie'], top: ['bar', 'line', 'pie'], low: ['bar', 'line', 'pie'], hour: ['bar', 'line', 'pie'], cash: ['doughnut', 'pie', 'bar'], lossS: ['bar', 'line', 'pie'], gainS: ['bar', 'line', 'pie'], inc: ['bar', 'line', 'pie'], exp: ['bar', 'line', 'pie'], cre: ['bar', 'line', 'pie'], prodLG: ['bar', 'line'], tankLG: ['bar', 'line'] };
    const LBL = { bar: 'Bar', line: 'Line', pie: 'Pie', doughnut: 'Donut' };
    Object.entries(ALLOW).forEach(([k, al]) => {
      const c = C[k]; c._t = c.config.type; c._h = c.options.indexAxis == 'y'; c._leg = k == 'trend' || k == 'prodLG'; c._i = k == 'trend';
      if (['top', 'low', 'lossS', 'gainS', 'inc', 'exp', 'cre', 'hour'].includes(k)) c._pal = true;
      if (k == 'hour') c._bg0 = '#4a90d9';
      const card = c.canvas.closest('.card'), w = document.createElement('div'); w.className = 'sw';
      w.innerHTML = al.map(t => `<button data-t="${t}" class="${t == c._t ? 'on' : ''}">${LBL[t]}</button>`).join('');
      w.onclick = e => { const b = e.target.closest('button'); if (!b) return; c._t = b.dataset.t; w.querySelectorAll('button').forEach(x => x.classList.toggle('on', x == b)); render() };
      card.style.position = 'relative'; card.querySelector('h3').style.paddingRight = '170px'; card.appendChild(w);
    });
    let FIN = [], INSF = [], TX = [], RUN = true, tickN = 0, dch = null;
    const ctx = () => (st < 0 ? 'All stations' : ST[st]) + ' · ' + (pr == 'all' ? 'all products' : pr);
    const hv = (e, els) => { e.native.target.style.cursor = els.length ? 'pointer' : 'default' };
    const hmv = (si, h) => Math.round(HR[h] * sum(NZ.filter(n => n.s == si && (pr == 'all' || n.p == pr)), 'l') / 11000);
    const gv = t => t.v * PR[t.p], vr = t => t.v >= 0 ? '+' + nf(t.v) : `<span class="neg">${nf(t.v)}</span>`;
    const tbl = (h, r) => h ? `<table><thead><tr>${h.map(x => `<th>${x}</th>`).join('')}</tr></thead><tbody>${r.length ? r.map(a => `<tr>${a.map(c => `<td>${c}</td>`).join('')}</tr>`).join('') : `<tr><td colspan="${h.length}">No records</td></tr>`}</tbody></table>` : '';
    function openD(o) {
      dT.textContent = o.t; dS.textContent = o.s || ctx();
      dChips.innerHTML = (o.chips || []).map(c => `<div><small>${c[0]}</small><b>${c[1]}</b></div>`).join('');
      if (dch) { dch.destroy(); dch = null }
      dBox.style.display = o.ch ? 'block' : 'none';
      if (o.ch) { const t = o.ch.type, pie = t == 'doughnut'; dch = new Chart(dCv, { type: t, data: { labels: o.ch.labels, datasets: [{ data: o.ch.data, backgroundColor: o.ch.colors, borderColor: t == 'line' ? o.ch.colors[0] : undefined, tension: .35, borderRadius: 5, borderWidth: pie ? 0 : undefined }] }, options: { plugins: { legend: { display: pie, position: 'bottom' } }, scales: pie ? {} : { y: { grid: g }, x: { grid: { display: false } } } } }) }
      dTb.innerHTML = tbl(o.h, o.r || []);
      dR.classList.add('on'); dOv.classList.add('on'); dX.focus();
    }
    const closeD = () => { dR.classList.remove('on'); dOv.classList.remove('on') };
    dX.onclick = dOv.onclick = closeD; addEventListener('keydown', e => { if (e.key == 'Escape') closeD(); if (e.key == 'Enter' && e.target.matches && e.target.matches('.kpi')) e.target.click() });
    function stD(si) {
      if (si < 0) return; const r = NZ.filter(n => n.s == si && (pr == 'all' || n.p == pr)), f = FIN[si];
      openD({ t: ST[si], s: 'Station overview · ' + (pr == 'all' ? 'all products' : pr), chips: [['Sales', rs(sum(r, 'a'))], ['Litres', nf(sum(r, 'l')) + ' L'], ['Income', rs(f.inc)], ['Expense', rs(f.exp)], ['Credit', rs(f.cre)], ['Stock loss / gain', rs(f.loss) + ' / ' + rs(f.gain)]], ch: { type: 'doughnut', labels: P, data: P.map(p => Math.round(sum(r.filter(n => n.p == p), 'l'))), colors: P.map(p => PC[p]) }, h: ['Tank', 'Product', 'Dip (L)', 'Variance (L)'], r: TK.filter(t => t.s == si && (pr == 'all' || t.p == pr)).map(t => ['T-' + (P.indexOf(t.p) + 1), tag(t.p), nf(t.dip), vr(t)]) })
    }
    function nzD(n) { const tx = TX.filter(x => x.n == n); openD({ t: ST[n.s] + ' · ' + n.d + ' ' + n.id, s: n.p + ' nozzle', chips: [['Product', n.p], ['Rate / L', rs(PR[n.p])], ['Opening', f1(n.o)], ['Closing', f1(n.c)], ['Litres sold', f1(n.l) + ' L'], ['Amount', rs(n.a)]], h: ['Time', 'Litres', 'Amount', 'Payment'], r: tx.map(x => [x.t.toLocaleTimeString(), f1(x.l), rs(x.a), x.pay]) }) }
    function tkD(t) { const p = Math.round(t.dip / t.cap * 100); openD({ t: ST[t.s] + ' · ' + t.p + ' tank', s: 'Tank dip and stock variance', chips: [['Capacity', nf(t.cap) + ' L'], ['Dip stock', nf(t.dip) + ' L'], ['Fill level', p + '%'], ['Variance', nf(t.v) + ' L'], ['Value', rs(gv(t))], ['Result', t.v >= 0 ? 'Gain' : 'Loss']], ch: { type: 'doughnut', labels: ['Stock', 'Empty'], data: [t.dip, t.cap - t.dip], colors: [PC[t.p], '#d5dae1'] } }) }
    function pD(p) {
      const A = NZ.filter(n => n.p == p && (st < 0 || n.s == st)), bs = ST.map((s, i) => [s, sum(A.filter(n => n.s == i), 'l')]).filter(x => x[1] > 0);
      openD({ t: p, s: ctx(), chips: [['Litres', nf(sum(A, 'l')) + ' L'], ['Sales', rs(sum(A, 'a'))], ['Nozzles', A.length], ['Rate / L', rs(PR[p])]], ch: { type: 'bar', labels: bs.map(x => x[0]), data: bs.map(x => Math.round(x[1])), colors: bs.map(() => PC[p]) }, h: ['Station', 'Nozzle', 'Litres', 'Amount'], r: A.map(n => [ST[n.s], n.d + ' ' + n.id, f1(n.l), rs(n.a)]) })
    }
    function dayD(i) { const d = C.trend.data.datasets; openD({ t: DAYS[i], s: ctx(), chips: [['Litres', nf(d.reduce((x, y) => x + y.data[i], 0)) + ' L'], ['Est. sales', rs(d.reduce((x, y) => x + y.data[i] * PR[y.label], 0))]], ch: { type: 'bar', labels: d.map(y => y.label), data: d.map(y => y.data[i]), colors: d.map(y => PC[y.label]) }, h: ['Product', 'Litres'], r: d.map(y => [tag(y.label), nf(y.data[i])]) }) }
    function cashD(i) { const L = C.cash.data.labels[i], sh = [.66, .10, .21, .03][i], fs = FIN.filter(x => st < 0 || x.i == st); openD({ t: L, s: ctx(), chips: [['Amount', rs(C.cash.data.datasets[0].data[i])], ['Share', Math.round(sh * 100) + '%']], ch: { type: 'bar', labels: fs.map(x => x.s), data: fs.map(x => Math.round(x.sale * sh)), colors: fs.map(() => PAL[i]) }, h: ['Station', L], r: fs.map(x => [x.s, rs(x.sale * sh)]) }) }
    function hmD(si, h) { const ss = si < 0 ? ST.map((_, i) => i) : [si], rows = ss.map(i => [ST[i], hmv(i, h)]), tot = rows.reduce((x, r) => x + r[1], 0); openD({ t: h * 2 + ':00 to ' + (h * 2 + 2) + ':00', s: (si < 0 ? 'All stations' : ST[si]) + ' · ' + (pr == 'all' ? 'all products' : pr), chips: [['Transactions', nf(tot)], ['Est. litres', nf(tot * 11.8) + ' L'], ['Est. sales', rs(tot * 11.8 * 290)]], ch: { type: 'bar', labels: rows.map(r => r[0]), data: rows.map(r => r[1]), colors: rows.map(() => '#4a90d9') }, h: ['Station', 'Transactions'], r: rows.map(r => [r[0], nf(r[1])]) }) }
    function kpiD(k) {
      const A = NZ.filter(n => (st < 0 || n.s == st) && (pr == 'all' || n.p == pr)), Af = FIN.filter(x => st < 0 || x.i == st), tk = TK.filter(t => (st < 0 || t.s == st) && (pr == 'all' || t.p == pr));
      const bp = P.map(p => ({ p, l: sum(A.filter(n => n.p == p), 'l'), a: sum(A.filter(n => n.p == p), 'a') })).filter(x => x.l > 0);
      const fx = (key, t, col) => openD({ t, s: ctx(), chips: [['Total', rs(sum(Af, key))], ['Stations', Af.length]], ch: { type: 'bar', labels: Af.map(x => x.s), data: Af.map(x => Math.round(x[key])), colors: Af.map(() => col) }, h: ['Station', t], r: [...Af].sort((a, b) => b[key] - a[key]).map(x => [x.s, rs(x[key])]) });
      const tv = neg => { const r = tk.filter(t => neg ? t.v < 0 : t.v > 0).sort((a, b) => neg ? a.v - b.v : b.v - a.v); openD({ t: neg ? 'Stock loss' : 'Stock gain', s: ctx(), chips: [['Total value', rs(r.reduce((x, t) => x + Math.abs(gv(t)), 0))], ['Tanks', r.length]], ch: { type: 'bar', labels: r.map(t => ST[t.s].split(' ')[0] + ' · ' + t.p), data: r.map(t => Math.abs(t.v)), colors: r.map(() => neg ? '#e0453f' : '#12a05c') }, h: ['Station', 'Product', 'Litres', 'Value'], r: r.map(t => [ST[t.s], tag(t.p), nf(t.v), rs(Math.abs(gv(t)))]) }) };
      if (k == 0) openD({ t: 'Today’s sales', s: ctx(), chips: [['Total', rs(sum(A, 'a'))], ['Litres', nf(sum(A, 'l')) + ' L']], ch: { type: 'doughnut', labels: bp.map(x => x.p), data: bp.map(x => Math.round(x.a)), colors: bp.map(x => PC[x.p]) }, h: ['Product', 'Litres', 'Amount'], r: bp.map(x => [tag(x.p), nf(x.l), rs(x.a)]) });
      else if (k == 1) { const bs = ST.map((s, i) => ({ s, l: sum(A.filter(n => n.s == i), 'l') })).filter(x => x.l > 0); openD({ t: 'Litres sold', s: ctx(), chips: [['Total', nf(sum(A, 'l')) + ' L'], ['Stations', bs.length]], ch: { type: 'bar', labels: bs.map(x => x.s), data: bs.map(x => Math.round(x.l)), colors: bs.map(() => '#0e8fa6') }, h: ['Station', 'Litres'], r: bs.map(x => [x.s, nf(x.l)]) }) }
      else if (k == 2) { const l = C.hour.data.labels, d = C.hour.data.datasets[0].data; openD({ t: 'Transactions (24h)', s: ctx(), chips: [['Total', nf(d.reduce((x, y) => x + y, 0))], ['Busiest slot', l[d.indexOf(Math.max(...d))]]], ch: { type: 'line', labels: l, data: d, colors: ['#4a90d9'] }, h: ['Slot', 'Transactions'], r: l.map((x, i) => [x, nf(d[i])]) }) }
      else if (k == 3) { const l = C.cash.data.labels, d = C.cash.data.datasets[0].data; openD({ t: 'Cash distribution', s: ctx(), chips: [['Total', rs(d.reduce((x, y) => x + y, 0))], ['Cash', rs(d[0])]], ch: { type: 'doughnut', labels: l, data: d, colors: ['#12a05c', '#4a90d9', '#e0453f', '#f59e0b'] }, h: ['Type', 'Amount'], r: l.map((x, i) => [x, rs(d[i])]) }) }
      else if (k == 4) openD({ t: 'Active nozzles', s: ctx(), chips: [['Nozzles', A.length], ['Litres', nf(sum(A, 'l')) + ' L']], h: ['Station', 'Nozzle', 'Product', 'Litres'], r: A.map(n => [ST[n.s], n.d + ' ' + n.id, tag(n.p), f1(n.l)]) });
      else if (k == 5) fx('cre', 'Party credit', '#8b5cf6'); else if (k == 6) tv(true); else if (k == 7) tv(false); else if (k == 8) fx('inc', 'Income', '#0e8fa6'); else fx('exp', 'Expenses', '#f59e0b');
    }
    function chartClick(c, el) {
      const k = Object.keys(C).find(x => C[x] == c), i = el.index, lb = c.data.labels[i];
      if (['top', 'low', 'lossS', 'gainS', 'inc', 'exp', 'cre'].includes(k)) stD(ST.indexOf(lb));
      else if (k == 'noz' && c._items[i]) nzD(c._items[i]);
      else if (k == 'tankLG' && c._items[i]) tkD(c._items[i]);
      else if (k == 'prod' || k == 'prodLG') pD(lb);
      else if (k == 'trend') dayD(i);
      else if (k == 'cash') cashD(i);
      else if (k == 'hour') hmD(st, i);
    }
    const X = {};
    const xo = k => ({ onClick: (e, els) => { if (els.length) xClick(k, els[0]) }, onHover: hv });
    X.stack = mk(xStack, { type: 'bar', data: { labels: ST, datasets: [] }, options: { ...xo('stack'), plugins: { legend: { position: 'bottom' } }, scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, grid: g } } } });
    X.mix = mk(xMix, { type: 'bar', data: { labels: ST, datasets: [] }, options: { ...xo('mix'), plugins: { legend: { position: 'bottom' } }, scales: { y: { grid: g }, x: { grid: { display: false } } } } });
    X.rad = mk(xRad, { type: 'radar', data: { labels: [], datasets: [] }, options: { ...xo('rad'), plugins: { legend: { display: false } }, scales: { r: { grid: g, angleLines: g, ticks: { display: false }, suggestedMin: 0, suggestedMax: 100 } } } });
    X.pol = mk(xPol, { type: 'polarArea', data: { labels: ST, datasets: [{ data: [], backgroundColor: [] }] }, options: { ...xo('pol'), plugins: { legend: { display: false } }, scales: { r: { grid: g, ticks: { display: false } } } } });
    X.bub = mk(xBub, { type: 'bubble', data: { datasets: [] }, options: { ...xo('bub'), plugins: { legend: { position: 'bottom' } }, scales: { x: { title: { display: true, text: 'Litres' }, grid: g }, y: { title: { display: true, text: 'Amount (Rs 000)' }, grid: g } } } });
    function xClick(k, el) { if (k == 'bub') { const n = X.bub._items[el.datasetIndex][el.index]; if (n) nzD(n) } else if (k == 'rad') stD(el.datasetIndex); else stD(el.index) }
    function updX(lv) {
      const pl = P.filter(p => pr == 'all' || pr == p);
      X.stack.data.datasets = pl.map(p => ({ label: p, data: ST.map((_, i) => Math.round(sum(NZ.filter(n => n.s == i && n.p == p), 'l'))), backgroundColor: PC[p], borderRadius: 3 }));
      X.mix.data.datasets = [{ label: 'Income', data: FIN.map(x => Math.round(x.inc)), backgroundColor: '#0e8fa6', borderRadius: 5 }, { label: 'Expense', data: FIN.map(x => Math.round(x.exp)), backgroundColor: '#f59e0b', borderRadius: 5 }, { type: 'line', label: 'Net', data: FIN.map(x => Math.round(x.inc - x.exp)), borderColor: '#12a05c', backgroundColor: '#12a05c', tension: .3, pointRadius: 4 }];
      const M = ['sale', 'inc', 'exp', 'cre', 'loss'], mx = M.map(k => Math.max(...FIN.map(x => x[k])) || 1);
      X.rad.data = { labels: ['Sales', 'Income', 'Expense', 'Credit', 'Stock loss'], datasets: FIN.map((x, i) => ({ label: x.s, data: M.map((k, j) => Math.round(x[k] / mx[j] * 100)), borderColor: PAL[i], backgroundColor: PAL[i] + (st == i ? '55' : '11'), borderWidth: st == i ? 3 : 1.5, pointRadius: 2 })) };
      X.pol.data = { labels: ST, datasets: [{ data: FIN.map(x => Math.round(x.exp)), backgroundColor: PAL.slice(0, 6).map(c => c + 'cc') }] };
      X.bub._items = pl.map(p => NZ.filter(n => n.p == p && (st < 0 || n.s == st)));
      X.bub.data.datasets = pl.map((p, i) => ({ label: p, data: X.bub._items[i].map(n => ({ x: n.l, y: Math.round(n.a / 1000), r: Math.max(3, (PR[p] - 280) / 2.5) })), backgroundColor: PC[p] + 'aa' }));
      Object.values(X).forEach(c => c.update(lv ? 'none' : undefined));
      if (0) {
        const mxh = Math.max(1, ...ST.flatMap((_, i) => HR.map((_, h) => hmv(i, h))));
        hm.innerHTML = '<tr><th></th>' + HR.map((_, h) => `<th>${h * 2}:00</th>`).join('') + '</tr>' + ST.map((s, i) => `<tr><th style="${st == i ? 'color:var(--ink)' : ''}">${s}</th>` + HR.map((_, h) => { const v = hmv(i, h), a = .08 + .85 * v / mxh; return `<td data-s="${i}" data-h="${h}" style="background:rgba(14,143,166,${a.toFixed(2)});color:${a > .5 ? '#fff' : 'inherit'}">${v}</td>` }).join('') + '</tr>').join('');
        const fl = TX.filter(x => (st < 0 || x.n.s == st) && (pr == 'all' || x.n.p == pr));
        feed.innerHTML = fl.length ? fl.map(x => `<div data-i="${TX.indexOf(x)}" class="${x.fresh ? 'nw' : ''}"><span>${x.t.toLocaleTimeString()}</span><span>${ST[x.n.s]}</span><span>${x.n.d} ${x.n.id}</span><span>${tag(x.n.p)}</span><span>${f1(x.l)} L</span><b>${rs(x.a)}</b><span>${x.pay}</span></div>`).join('') : '<div>Waiting for transactions…</div>';
      }
    }
    function tick(r) {
      const n = NZ[Math.floor(Math.random() * NZ.length)], l = Math.round((8 + Math.random() * 50) * 10) / 10;
      n.l = Math.round((n.l + l) * 10) / 10; n.c = n.o + n.l; n.a = n.l * PR[n.p];
      const tk = TK.find(t => t.s == n.s && t.p == n.p); if (tk) tk.dip = Math.max(0, tk.dip - Math.round(l));
      TX.forEach(x => x.fresh = false);
      TX.unshift({ t: new Date(), n, l, a: l * PR[n.p], pay: ['Cash', 'Cash', 'Card', 'Credit', 'Fuel card'][Math.floor(Math.random() * 5)], fresh: r !== false }); TX = TX.slice(0, 10);
      lt.textContent = new Date().toLocaleTimeString();
      if (r !== false) render(true);
    }
    if (0) lp.onclick = () => { RUN = !RUN; lp.textContent = RUN ? 'Pause' : 'Resume'; lvs.textContent = RUN ? 'Live' : 'Paused'; lvb.classList.toggle('off', !RUN) };

    function render(lv) {
      const sN = NZ.filter(n => st < 0 || n.s == st), fN = sN.filter(n => pr == 'all' || n.p == pr);
      const L = sum(fN, 'l'), A = sum(fN, 'a'), T = Math.round(L / 11.8);
      const vv = t => t.v * PR[t.p], S = (a, f) => a.reduce((x, y) => x + f(y), 0), loss = a => S(a, t => t.v < 0 ? -vv(t) : 0), gain = a => S(a, t => t.v > 0 ? vv(t) : 0);
      const tS = TK.filter(t => st < 0 || t.s == st), tF = tS.filter(t => pr == 'all' || t.p == pr), tP = TK.filter(t => pr == 'all' || t.p == pr);
      const fin = ST.map((s, i) => { const a = sum(NZ.filter(n => n.s == i && (pr == 'all' || n.p == pr)), 'a'), r = tP.filter(t => t.s == i); return { s, i, sale: a, inc: a * .045, exp: a * .028 + 18000 * (i % 3 + 1), cre: a * [.28, .19, .24, .15, .3, .1][i], loss: loss(r), gain: gain(r) } });
      const fS = fin.filter(x => st < 0 || x.i == st); FIN = fin;
      sub.textContent = '22 Oct 2026 · ' + (st < 0 ? 'all stations' : ST[st]) + ' · ' + (pr == 'all' ? 'all products' : pr) + ' (demo data)';
      kpis.innerHTML = [['Today’s sales', rs(A)], ['Litres sold', nf(L) + ' L'], ['Transactions (24h)', nf(T)], ['Cash collected', rs(A * .66)], ['Active nozzles', fN.length], ['Party credit (fuel)', rs(sum(fS, 'cre'))], ['Stock loss', rs(loss(tF))], ['Stock gain', rs(gain(tF))], ['Income', rs(sum(fS, 'inc'))], ['Expenses', rs(sum(fS, 'exp'))]].map((k, i) => `<div class="card kpi" data-k="${i}" tabindex="0"><small>${k[0]}</small><b>${k[1]}</b></div>`).join('');
      const PT = P.map(p => { const r = sN.filter(n => n.p == p); return { p, l: sum(r, 'l'), a: sum(r, 'a'), n: r.length } }), TL = sum(PT, 'l') || 1;
      pcs.innerHTML = PT.map(x => `<button class="pc ${pr == x.p ? 'on' : ''}" data-p="${x.p}" style="--c:${PC[x.p]}"><span>${x.p}</span><b>${nf(x.l)} L</b><small>${rs(x.a)} · ${x.n} nozzles · ${Math.round(x.l / TL * 100)}%</small><i style="width:${x.l / TL * 100}%"></i></button>`).join('');
      document.querySelectorAll('#seg button').forEach(b => b.classList.toggle('on', b.dataset.p == pr));
      C.trend.data.datasets = PT.filter(x => pr == 'all' || pr == x.p).map(x => ({ label: x.p, data: MULT.map(m => Math.round(x.l * m)), borderColor: PC[x.p], backgroundColor: PC[x.p] + '22', fill: pr != 'all', tension: .35 }));
      C.prod.data.datasets[0].data = PT.map(x => x.l);
      const sh = n => st < 0 ? ST[n.s].split(' ')[0] + ' · ' + n.d + n.id : n.d + ' · ' + n.id;
      C.noz._items = fN; C.noz.data.labels = fN.map(sh); C.noz.data.datasets[0].data = fN.map(n => n.l); C.noz.data.datasets[0].backgroundColor = fN.map(n => PC[n.p]);
      const bySt = ST.map((s, i) => ({ s, i, l: sum(NZ.filter(n => n.s == i && (pr == 'all' || n.p == pr)), 'l') }));
      const col = r => r.map(x => PC[pr == 'all' ? 'Diesel' : pr] + (st < 0 || x.i == st ? '' : '55')), top = [...bySt].sort((a, b) => b.l - a.l).slice(0, 5), low = [...bySt].sort((a, b) => a.l - b.l).slice(0, 5);
      [[C.top, top], [C.low, low]].forEach(([c, r]) => { c.data.labels = r.map(x => x.s); c.data.datasets[0].data = r.map(x => x.l); c.data.datasets[0].backgroundColor = r.map(x => (c == C.top ? '#12a05c' : '#e0453f') + (st < 0 || x.i == st ? '' : '55')) });
      C.hour.data.datasets[0].data = HR.map(v => Math.round(v * T / 1052));
      C.cash.data.datasets[0].data = [.66, .10, .21, .03].map(v => Math.round(A * v));
      const t1 = k => [...fin].sort((a, b) => b[k] - a[k])[0], tkw = [...tP].sort((a, b) => vv(a) - vv(b)), tl = tkw[0], tg = tkw[tkw.length - 1], pl2 = P.map(p => ({ p, l: loss(tS.filter(t => t.p == p)), g: gain(tS.filter(t => t.p == p)) }));
      const pL = [...pl2].sort((a, b) => b.l - a.l)[0], pG = [...pl2].sort((a, b) => b.g - a.g)[0];
      INSF = [() => stD(t1('loss').i), () => stD(t1('gain').i), () => pD(pL.p), () => pD(pG.p), () => tkD(tl), () => tkD(tg), () => stD(t1('inc').i), () => stD(t1('exp').i), () => stD(t1('cre').i)];
      ins.innerHTML = [['Most stock loss (station)', t1('loss').s, rs(t1('loss').loss), '#e0453f'], ['Most stock gain (station)', t1('gain').s, rs(t1('gain').gain), '#12a05c'], ['Most loss (product)', pL.p, rs(pL.l), '#e0453f'], ['Most gain (product)', pG.p, rs(pG.g), '#12a05c'], ['Worst tank', ST[tl.s] + ' · ' + tl.p, nf(tl.v) + ' L', '#e0453f'], ['Best tank', ST[tg.s] + ' · ' + tg.p, '+' + nf(tg.v) + ' L', '#12a05c'], ['Highest income', t1('inc').s, rs(t1('inc').inc), '#0e8fa6'], ['Highest expense', t1('exp').s, rs(t1('exp').exp), '#f59e0b'], ['Highest credit', t1('cre').s, rs(t1('cre').cre), '#8b5cf6']].map((x, i) => `<div class="card" data-i="${i}" style="cursor:pointer;border-left:4px solid ${x[3]}"><small style="color:var(--mut);font-weight:600">${x[0]}</small><b>${x[1]}</b><span>${x[2]}</span></div>`).join('');
      const hb = (c, k, col) => { const r = [...fin].sort((a, b) => b[k] - a[k]); c.data.labels = r.map(x => x.s); c.data.datasets[0].data = r.map(x => Math.round(x[k])); c.data.datasets[0].backgroundColor = r.map(x => col + (st < 0 || x.i == st ? '' : '55')) };
      hb(C.lossS, 'loss', '#e0453f'); hb(C.gainS, 'gain', '#12a05c'); hb(C.inc, 'inc', '#0e8fa6'); hb(C.exp, 'exp', '#f59e0b'); hb(C.cre, 'cre', '#8b5cf6');
      C.prodLG.data.datasets[0].data = pl2.map(x => Math.round(x.l)); C.prodLG.data.datasets[1].data = pl2.map(x => Math.round(x.g));
      const tt = [...tF].sort((a, b) => a.v - b.v); C.tankLG._items = tt; C.tankLG.data.labels = tt.map(t => (st < 0 ? ST[t.s].split(' ')[0] + ' · ' : '') + t.p); C.tankLG.data.datasets[0].data = tt.map(t => t.v); C.tankLG.data.datasets[0].backgroundColor = tt.map(t => t.v < 0 ? '#e0453f' : '#12a05c');
      Object.values(C).forEach(c => { apply(c); c.update(lv ? 'none' : undefined) });
      updX(lv);
      let tk = TK.filter(t => (st < 0 || t.s == st) && (pr == 'all' || t.p == pr));
      if (st < 0) tk = P.filter(p => pr == 'all' || pr == p).map(p => { const r = tk.filter(t => t.p == p); return { p, cap: sum(r, 'cap'), dip: sum(r, 'dip'), name: 'All' } });
      tanks.innerHTML = tk.map((t, i) => { const p = Math.round(t.dip / t.cap * 100), c = p < 20 ? 'var(--bad)' : p < 40 ? '#f59e0b' : 'var(--good)'; return `<div class="tank"><div class="nm">${st < 0 ? t.p : 'Tank ' + (i + 1)}<small>${st < 0 ? 'all stations' : t.p}</small></div><div class="bar"><span style="width:${p}%;background:${c}"></span></div><div class="lv">${nf(t.dip)} / ${nf(t.cap)} L (${p}%)</div></div>` }).join('');
      // tables
      const stl = (st < 0 ? ST.map((_, i) => i) : [st]), pl = P.filter(p => pr == 'all' || pr == p);
      const pur = []; stl.forEach(si => pl.forEach(p => { const pi = P.indexOf(p); if ((si + pi) % 2 == 0) pur.push({ s: si, p, l: 15000, r: PR[p] - (si % 3), rem: Math.round(15000 * (.2 + ((si * 3 + pi) % 7) / 10)) }) }));
      const D = [
        [['Station', 'Dispenser', 'Nozzle', 'Product', 'Opening', 'Closing', 'Sold (L)', 'Amount'], fN.map(n => [ST[n.s], n.d, n.id, tag(n.p), f1(n.o), f1(n.c), f1(n.l), rs(n.a)])],
        [['Product', 'Nozzles', 'Litres', 'Rate / L', 'Amount', 'Share'], PT.filter(x => pr == 'all' || pr == x.p).map(x => [tag(x.p), x.n, nf(x.l), PR[x.p].toFixed(2), rs(x.a), Math.round(x.l / TL * 100) + '%'])],
        [['Station', 'Shift', 'Opened', 'Closed', 'Litres', 'Sales', 'Cash', 'Short / excess', 'Status'], stl.flatMap(si => { const r = NZ.filter(n => n.s == si && (pr == 'all' || n.p == pr)), l = sum(r, 'l'), a = sum(r, 'a'), sh = ((si * 5) % 3 - 1) * 600; return [[ST[si], 'SH-' + (1042 + si), '06:00', '—', nf(l * .4), rs(a * .4), rs(a * .3), '0', '<span class="tag s-Open">Open</span>'], [ST[si], 'SH-' + (1030 + si), '06:00', '06:00', nf(l), rs(a), rs(a * .78), sh < 0 ? `<span class="neg">${sh}</span>` : sh, '<span class="tag s-Closed">Closed</span>']] })],
        [['Date', 'Station', 'Supplier', 'Product', 'Litres', 'Rate', 'Total'], pur.map(x => ['22 Oct', ST[x.s], 'PSO Depot', tag(x.p), nf(x.l), x.r.toFixed(2), rs(x.l * x.r)])],
        [['Station', 'Party', 'Product', 'Litres', 'Amount', 'Outstanding'], stl.flatMap(si => pl.filter(p => p != 'Hi-Octane').map(p => { const l = sum(NZ.filter(n => n.s == si && n.p == p), 'l') * .18; return [ST[si], ['Al-Hamd Transport', 'City Logistics', 'Metro Taxi Co.'][si % 3], tag(p), nf(l), rs(l * PR[p]), `<span class="neg">${rs(l * PR[p] * 3.5)}</span>`] }))],
        [['Station', 'Product', 'Layer', 'Received (L)', 'Cost / L', 'Remaining (L)'], pur.map(x => [ST[x.s], tag(x.p), 'L-' + (2200 + x.s * 3 + P.indexOf(x.p)), nf(x.l), x.r.toFixed(2), nf(x.rem)])],
        [['Station', 'Tank', 'Product', 'Variance (L)', 'Value', 'Result'], tF.map(t => [ST[t.s], 'T-' + (P.indexOf(t.p) + 1), tag(t.p), t.v >= 0 ? '+' + nf(t.v) : `<span class="neg">${nf(t.v)}</span>`, rs(vv(t)), t.v >= 0 ? '<span class="tag s-Closed">Gain</span>' : '<span class="tag s-Open">Loss</span>'])],
        [['Station', 'Income', 'Expense', 'Net', 'Credit', 'Stock loss', 'Stock gain'], fS.map(x => [x.s, rs(x.inc), rs(x.exp), x.inc - x.exp < 0 ? `<span class="neg">${rs(x.inc - x.exp)}</span>` : rs(x.inc - x.exp), rs(x.cre), rs(x.loss), rs(x.gain)])],
        [['Time', 'Station', 'Nozzle', 'Product', 'Litres', 'Amount', 'Payment'], TX.filter(x => (st < 0 || x.n.s == st) && (pr == 'all' || x.n.p == pr)).sort((a, b) => b.t - a.t).map(x => [x.t.toLocaleString([], { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }), ST[x.n.s], x.n.d + ' ' + x.n.id, tag(x.n.p), f1(x.l), rs(x.a), x.pay])]];
      if (!lv || (++tickN % 6 == 0 && ![...document.querySelectorAll('.dataTables_filter input')].some(i => i.value))) {
        panes.innerHTML = D.map(([h, r], i) => `<div class="pane ${i == tab ? 'on' : ''}" id="p${i}"><table class="dt"><thead><tr>${h.map(x => `<th>${x}</th>`).join('')}</tr></thead><tbody>${r.map(row => `<tr>${row.map(c => `<td>${c}</td>`).join('')}</tr>`).join('')}</tbody></table></div>`).join('');
        $('table.dt').DataTable({ pageLength: 10, order: [], language: { emptyTable: 'No records for this filter' } });
      }
    }
    document.getElementById('st').onchange = e => { st = +e.target.value; render() };
    seg.onclick = e => { const b = e.target.closest('button'); if (b) { pr = b.dataset.p; render() } };
    pcs.onclick = e => { const b = e.target.closest('.pc'); if (b) pD(b.dataset.p) };
    kpis.onclick = e => { const c = e.target.closest('.kpi'); if (c) kpiD(+c.dataset.k) };
    ins.onclick = e => { const c = e.target.closest('.card'); if (c && INSF[+c.dataset.i]) INSF[+c.dataset.i]() };
    if (0) feed.onclick = e => { const r = e.target.closest('[data-i]'); if (r && TX[+r.dataset.i]) nzD(TX[+r.dataset.i].n) };
    if (0) hm.onclick = e => { const c = e.target.closest('td'); if (c) hmD(+c.dataset.s, +c.dataset.h) };
    tabs.onclick = e => { const b = e.target.closest('button'); if (!b) return; tab = +b.dataset.i; document.querySelectorAll('#tabs button,.pane').forEach(x => x.classList.remove('on')); b.classList.add('on'); document.getElementById('p' + tab).classList.add('on') };
    (() => { let sd = 7; const rnd = () => (sd = (sd * 9301 + 49297) % 233280) / 233280; for (let i = 0; i < 240; i++) { const n = NZ[Math.floor(rnd() * NZ.length)], l = Math.round((8 + rnd() * 52) * 10) / 10; TX.push({ t: new Date(Date.now() - i / 240 * 864e5), n, l, a: l * PR[n.p], pay: ['Cash', 'Cash', 'Card', 'Credit', 'Fuel card'][Math.floor(rnd() * 5)] }) } })();
    render();
  </script>
  <script>
    const SF = () => FIN.filter(x => st < 0 || x.i == st), NA = () => NZ.filter(n => (st < 0 || n.s == st) && (pr == 'all' || n.p == pr)), TA = () => TK.filter(t => (st < 0 || t.s == st) && (pr == 'all' || t.p == pr)), XA = () => TX.filter(x => (st < 0 || x.n.s == st) && (pr == 'all' || x.n.p == pr));
    const pc = (a, b) => b ? Math.round(a / b * 100) + '%' : '0%';
    const tf = d => d.toLocaleString([], { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
    const lossOf = a => a.reduce((x, t) => x + (t.v < 0 ? -gv(t) : 0), 0), gainOf = a => a.reduce((x, t) => x + (t.v > 0 ? gv(t) : 0), 0);
    const TXH = ['Time', 'Station', 'Nozzle', 'Product', 'Litres', 'Amount', 'Payment'], TKH = ['Station', 'Tank', 'Product', 'Capacity', 'Dip (L)', 'Fill', 'Variance (L)', 'Value', 'Result'], NZH = ['Station', 'Dispenser', 'Nozzle', 'Product', 'Opening', 'Closing', 'Sold (L)', 'Amount'], FNH = ['Station', 'Income', 'Expense', 'Net', 'Credit', 'Stock loss', 'Stock gain'];
    const txr = a => [...a].sort((x, y) => y.t - x.t).map(x => [tf(x.t), ST[x.n.s], x.n.d + ' ' + x.n.id, tag(x.n.p), f1(x.l), rs(x.a), x.pay]);
    const tkr = a => a.map(t => [ST[t.s], 'T-' + (P.indexOf(t.p) + 1), tag(t.p), nf(t.cap), nf(t.dip), Math.round(t.dip / t.cap * 100) + '%', vr(t), rs(gv(t)), t.v >= 0 ? '<span class="tag s-Closed">Gain</span>' : '<span class="tag s-Open">Loss</span>']);
    const nzr = a => a.map(n => [ST[n.s], n.d, n.id, tag(n.p), f1(n.o), f1(n.c), f1(n.l), rs(n.a)]);
    const fnr = a => a.map(x => [x.s, rs(x.inc), rs(x.exp), rs(x.inc - x.exp), rs(x.cre), rs(x.loss), rs(x.gain)]);
    const prt = si => P.filter(p => p != 'Hi-Octane' && (pr == 'all' || pr == p)).map(p => { const l = sum(NZ.filter(n => n.s == si && n.p == p), 'l') * .18; return [ST[si], ['Al-Hamd Transport', 'City Logistics', 'Metro Taxi Co.'][si % 3], tag(p), nf(l), rs(l * PR[p]), `<span class="neg">${rs(l * PR[p] * 3.5)}</span>`] });
    const PRH = ['Station', 'Party', 'Product', 'Litres', 'Amount', 'Outstanding'];
    let dchs = [];
    const tbl2 = (h, r) => `<div style="overflow-x:auto"><table class="dt2"><thead><tr>${h.map(x => `<th>${x}</th>`).join('')}</tr></thead><tbody>${r.map(a => `<tr>${a.map(c => `<td>${c}</td>`).join('')}</tr>`).join('')}</tbody></table></div>`;
    function openD(o) {
      dT.textContent = o.t; dS.textContent = o.s || ctx();
      dChips.innerHTML = (o.chips || []).map(c => `<div><small>${c[0]}</small><b>${c[1]}</b></div>`).join('');
      dchs.forEach(c => c.destroy()); dchs = [];
      const cs = o.charts || [];
      dBox.style.display = cs.length ? 'grid' : 'none';
      dBox.innerHTML = cs.map((c, i) => `<div class="cb"><h4>${c.t}</h4><div class="cv"><canvas id="dc${i}"></canvas></div></div>`).join('');
      cs.forEach((c, i) => {
        const pie = c.type == 'doughnut', hb = c.type == 'hbar';
        dchs.push(new Chart(document.getElementById('dc' + i), { type: hb ? 'bar' : c.type, data: { labels: c.labels, datasets: c.sets || [{ label: c.t, data: c.data, backgroundColor: c.colors, borderColor: c.type == 'line' ? c.colors[0] : undefined, tension: .35, borderRadius: 5, borderWidth: pie ? 0 : undefined }] }, options: { indexAxis: hb ? 'y' : 'x', plugins: { legend: { display: pie || !!c.sets, position: 'bottom' } }, scales: pie ? {} : { y: { grid: g, stacked: !!c.stack }, x: { grid: { display: false }, stacked: !!c.stack } } } }))
      });
      dTb.innerHTML = (o.tables || []).map(t => `<div class="dtt">${t.t}</div>${tbl2(t.h, t.r)}`).join('');
      $('#dTb table').DataTable({ pageLength: 5, order: [], lengthChange: false, language: { emptyTable: 'No records' } });
      dR.classList.add('on'); dOv.classList.add('on'); dX.focus();
    }
    const lgS = (a, keys) => ({ label: '', data: [] });
    function stD(si) {
      if (si < 0) return;
      const A = NZ.filter(n => n.s == si && (pr == 'all' || n.p == pr)), tk = TK.filter(t => t.s == si && (pr == 'all' || t.p == pr)), tx = TX.filter(x => x.n.s == si && (pr == 'all' || x.n.p == pr)), f = FIN[si], L = sum(A, 'l'), Am = sum(A, 'a'), sh = ((si * 5) % 3 - 1) * 600;
      const lg = P.filter(p => pr == 'all' || pr == p).map(p => { const r = tk.filter(t => t.p == p); return { p, l: lossOf(r), g: gainOf(r) } });
      openD({
        t: ST[si], s: 'Station overview · ' + (pr == 'all' ? 'all products' : pr),
        chips: [['Sales', rs(Am)], ['Litres', nf(L) + ' L'], ['Transactions (24h)', nf(tx.length)], ['Income', rs(f.inc)], ['Expense', rs(f.exp)], ['Net', rs(f.inc - f.exp)], ['Credit', rs(f.cre)], ['Stock loss', rs(f.loss)], ['Stock gain', rs(f.gain)]],
        charts: [{ t: 'Litres by product', type: 'doughnut', labels: P, data: P.map(p => Math.round(sum(A.filter(n => n.p == p), 'l'))), colors: P.map(p => PC[p]) },
        { t: 'Litres by nozzle', type: 'bar', labels: A.map(n => n.d + ' ' + n.id), data: A.map(n => Math.round(n.l)), colors: A.map(n => PC[n.p]) },
        { t: 'Tank fill level (%)', type: 'bar', labels: tk.map(t => t.p), data: tk.map(t => Math.round(t.dip / t.cap * 100)), colors: tk.map(t => PC[t.p]) },
        { t: 'Loss and gain by product (Rs)', type: 'bar', labels: lg.map(x => x.p), sets: [{ label: 'Loss', data: lg.map(x => Math.round(x.l)), backgroundColor: '#e0453f', borderRadius: 5 }, { label: 'Gain', data: lg.map(x => Math.round(x.g)), backgroundColor: '#12a05c', borderRadius: 5 }] },
        { t: 'Last 7 days (litres)', type: 'line', labels: DAYS, data: MULT.map(m => Math.round(L * m)), colors: ['#0e8fa6'] },
        { t: 'Income vs expense vs credit (Rs)', type: 'bar', labels: ['Income', 'Expense', 'Credit'], data: [Math.round(f.inc), Math.round(f.exp), Math.round(f.cre)], colors: ['#0e8fa6', '#f59e0b', '#8b5cf6'] }],
        tables: [{ t: 'Nozzle readings', h: NZH, r: nzr(A) }, { t: 'Tanks and stock variance', h: TKH, r: tkr(tk) }, { t: 'Transactions (last 24 hours)', h: TXH, r: txr(tx) },
        { t: 'Shift closing', h: ['Shift', 'Opened', 'Closed', 'Litres', 'Sales', 'Cash', 'Short / excess', 'Status'], r: [['SH-' + (1042 + si), '06:00', '—', nf(L * .4), rs(Am * .4), rs(Am * .3), '0', '<span class="tag s-Open">Open</span>'], ['SH-' + (1030 + si), '06:00', '06:00', nf(L), rs(Am), rs(Am * .78), sh < 0 ? `<span class="neg">${sh}</span>` : sh, '<span class="tag s-Closed">Closed</span>']] },
        { t: 'Party credit', h: PRH, r: prt(si) }]
      })
    }
    function nzD(n) {
      const sib = NZ.filter(x => x.s == n.s), tx = TX.filter(x => x.n == n), sl = sum(sib, 'l'), pay = {}; tx.forEach(x => pay[x.pay] = (pay[x.pay] || 0) + x.a);
      const slots = HR.map((_, h) => tx.filter(x => Math.floor(x.t.getHours() / 2) == h).reduce((a, x) => a + x.l, 0)), tl = sum(tx, 'l');
      openD({
        t: ST[n.s] + ' · ' + n.d + ' ' + n.id, s: n.p + ' nozzle', chips: [['Product', n.p], ['Rate / L', rs(PR[n.p])], ['Opening', f1(n.o)], ['Closing', f1(n.c)], ['Litres sold', f1(n.l) + ' L'], ['Amount', rs(n.a)], ['Transactions (24h)', tx.length], ['Avg / transaction', f1(tx.length ? tl / tx.length : 0) + ' L'], ['Share of station', pc(n.l, sl)]],
        charts: [{ t: 'Litres by 2-hour slot (24h)', type: 'bar', labels: HR.map((_, h) => h * 2 + ':00'), data: slots.map(Math.round), colors: slots.map(() => PC[n.p]) }, { t: 'Payment mix (Rs)', type: 'doughnut', labels: Object.keys(pay), data: Object.values(pay).map(Math.round), colors: PAL }, { t: 'Nozzles in this station (litres)', type: 'bar', labels: sib.map(x => x.d + ' ' + x.id), data: sib.map(x => Math.round(x.l)), colors: sib.map(x => x == n ? '#101828' : PC[x.p] + '88') }],
        tables: [{ t: 'Transactions (last 24 hours)', h: ['Time', 'Product', 'Litres', 'Amount', 'Payment'], r: [...tx].sort((a, b) => b.t - a.t).map(x => [tf(x.t), tag(x.n.p), f1(x.l), rs(x.a), x.pay]) }, { t: 'Nozzles in this station', h: ['Dispenser', 'Nozzle', 'Product', 'Litres', 'Amount', 'Share'], r: sib.map(x => [x.d, x.id, tag(x.p), f1(x.l), rs(x.a), pc(x.l, sl)]) }]
      })
    }
    function tkD(t) {
      const same = TK.filter(x => x.p == t.p), inSt = TK.filter(x => x.s == t.s), p = Math.round(t.dip / t.cap * 100);
      openD({
        t: ST[t.s] + ' · ' + t.p + ' tank', s: 'Tank dip and stock variance', chips: [['Capacity', nf(t.cap) + ' L'], ['Dip stock', nf(t.dip) + ' L'], ['Fill level', p + '%'], ['Book stock', nf(t.dip - t.v) + ' L'], ['Variance', nf(t.v) + ' L'], ['Value', rs(gv(t))], ['Empty space', nf(t.cap - t.dip) + ' L'], ['Result', t.v >= 0 ? 'Gain' : 'Loss'], ['Rate / L', rs(PR[t.p])]],
        charts: [{ t: 'Stock vs empty space', type: 'doughnut', labels: ['Stock', 'Empty'], data: [t.dip, t.cap - t.dip], colors: [PC[t.p], '#d5dae1'] }, { t: 'Tanks in this station (fill %)', type: 'bar', labels: inSt.map(x => x.p), data: inSt.map(x => Math.round(x.dip / x.cap * 100)), colors: inSt.map(x => x == t ? '#101828' : PC[x.p] + '88') }, { t: 'Same product, all stations (variance L)', type: 'bar', labels: same.map(x => ST[x.s].split(' ')[0]), data: same.map(x => x.v), colors: same.map(x => x.v < 0 ? '#e0453f' : '#12a05c') }],
        tables: [{ t: 'Same product across stations', h: TKH, r: tkr(same) }, { t: 'Tanks in this station', h: TKH, r: tkr(inSt) }]
      })
    }
    function pD(p) {
      const A = NZ.filter(n => n.p == p && (st < 0 || n.s == st)), tk = TK.filter(t => t.p == p && (st < 0 || t.s == st)), tx = TX.filter(x => x.n.p == p && (st < 0 || x.n.s == st)), L = sum(A, 'l'), Am = sum(A, 'a');
      const bs = ST.map((s, i) => { const r = A.filter(n => n.s == i); return { s, i, n: r.length, l: sum(r, 'l'), a: sum(r, 'a') } }).filter(x => x.n);
      openD({
        t: p, s: ctx(), chips: [['Litres', nf(L) + ' L'], ['Sales', rs(Am)], ['Nozzles', A.length], ['Rate / L', rs(PR[p])], ['Transactions (24h)', tx.length], ['Avg / transaction', f1(tx.length ? sum(tx, 'l') / tx.length : 0) + ' L'], ['Stock loss', rs(lossOf(tk))], ['Stock gain', rs(gainOf(tk))], ['Stations', bs.length]],
        charts: [{ t: 'Litres by station', type: 'bar', labels: bs.map(x => x.s), data: bs.map(x => Math.round(x.l)), colors: bs.map(() => PC[p]) }, { t: 'Station share', type: 'doughnut', labels: bs.map(x => x.s), data: bs.map(x => Math.round(x.l)), colors: PAL }, { t: 'Last 7 days (litres)', type: 'line', labels: DAYS, data: MULT.map(m => Math.round(L * m)), colors: [PC[p]] }, { t: 'Loss and gain by station (Rs)', type: 'bar', labels: bs.map(x => x.s), sets: [{ label: 'Loss', data: bs.map(x => Math.round(lossOf(tk.filter(t => t.s == x.i)))), backgroundColor: '#e0453f', borderRadius: 5 }, { label: 'Gain', data: bs.map(x => Math.round(gainOf(tk.filter(t => t.s == x.i)))), backgroundColor: '#12a05c', borderRadius: 5 }] }],
        tables: [{ t: 'Station-wise sales', h: ['Station', 'Nozzles', 'Litres', 'Amount', 'Share'], r: bs.map(x => [x.s, x.n, nf(x.l), rs(x.a), pc(x.a, Am)]) }, { t: 'Nozzle readings', h: NZH, r: nzr(A) }, { t: 'Tanks and stock variance', h: TKH, r: tkr(tk) }, { t: 'Transactions (last 24 hours)', h: TXH, r: txr(tx) }]
      })
    }
    function dayD(i) {
      const d = C.trend.data.datasets, tot = d.reduce((x, y) => x + y.data[i], 0), sa = d.reduce((x, y) => x + y.data[i] * PR[y.label], 0), avg = d.reduce((x, y) => x + y.data.reduce((a, b) => a + b, 0), 0) / 7, sc = NA(), bs = ST.map((s, j) => ({ s, l: sum(sc.filter(n => n.s == j), 'l') * MULT[i] })).filter(x => x.l > 0);
      openD({
        t: DAYS[i], s: ctx(), chips: [['Litres', nf(tot) + ' L'], ['Est. sales', rs(sa)], ['vs 7-day average', (tot >= avg ? '+' : '') + Math.round((tot / avg - 1) * 100) + '%'], ['Top product', d.length ? [...d].sort((a, b) => b.data[i] - a.data[i])[0].label : '-'], ['Stations', bs.length], ['Products', d.length]],
        charts: [{ t: 'Litres by product', type: 'doughnut', labels: d.map(y => y.label), data: d.map(y => y.data[i]), colors: d.map(y => PC[y.label]) }, { t: 'Sales by product (Rs)', type: 'bar', labels: d.map(y => y.label), data: d.map(y => Math.round(y.data[i] * PR[y.label])), colors: d.map(y => PC[y.label]) }, { t: 'Litres by station', type: 'bar', labels: bs.map(x => x.s), data: bs.map(x => Math.round(x.l)), colors: bs.map(() => '#0e8fa6') }],
        tables: [{ t: 'Product-wise', h: ['Product', 'Litres', 'Rate / L', 'Amount', 'Share'], r: d.map(y => [tag(y.label), nf(y.data[i]), rs(PR[y.label]), rs(y.data[i] * PR[y.label]), pc(y.data[i], tot)]) }, { t: 'Station-wise (estimated)', h: ['Station', 'Litres', 'Share'], r: bs.map(x => [x.s, nf(x.l), pc(x.l, tot)]) }]
      })
    }
    function cashD(i) {
      const L = C.cash.data.labels[i], v = C.cash.data.datasets[0].data[i], sh = [.66, .10, .21, .03][i], fs = SF(), A = NA();
      openD({
        t: L, s: ctx(), chips: [['Amount', rs(v)], ['Share', Math.round(sh * 100) + '%'], ['Stations', fs.length], ['Top station', fs.length ? [...fs].sort((a, b) => b.sale - a.sale)[0].s : '-']],
        charts: [{ t: L + ' by station (Rs)', type: 'bar', labels: fs.map(x => x.s), data: fs.map(x => Math.round(x.sale * sh)), colors: fs.map(() => PAL[i]) }, { t: L + ' by product (Rs)', type: 'doughnut', labels: P, data: P.map(p => Math.round(sum(A.filter(n => n.p == p), 'a') * sh)), colors: P.map(p => PC[p]) }],
        tables: [{ t: 'Station-wise', h: ['Station', L, 'Share'], r: fs.map(x => [x.s, rs(x.sale * sh), Math.round(sh * 100) + '%']) }, { t: 'Product-wise', h: ['Product', L], r: P.map(p => [tag(p), rs(sum(A.filter(n => n.p == p), 'a') * sh)]) }]
      })
    }
    function hourD(i) {
      const d = C.hour.data.datasets[0].data, T = d[i], tt = d.reduce((a, b) => a + b, 0), A = NA(), L = sum(A, 'l') || 1, bs = ST.map((s, j) => ({ s, j, v: hmv(j, i) })).filter(x => st < 0 || x.j == st), tx = XA().filter(x => Math.floor(x.t.getHours() / 2) == i);
      openD({
        t: C.hour.data.labels[i] + ' to ' + (i * 2 + 2) + ':00', s: ctx(), chips: [['Transactions', nf(T)], ['Share of 24h', pc(T, tt)], ['Est. litres', nf(T * 11.8) + ' L'], ['Est. sales', rs(T * 11.8 * 290)], ['Busiest slot', d.indexOf(Math.max(...d)) == i ? 'Yes' : 'No'], ['Recorded in this slot', tx.length]],
        charts: [{ t: 'Transactions by station', type: 'bar', labels: bs.map(x => x.s), data: bs.map(x => x.v), colors: bs.map(() => '#4a90d9') }, { t: 'Litres by product', type: 'doughnut', labels: P, data: P.map(p => Math.round(sum(A.filter(n => n.p == p), 'l') / L * T * 11.8)), colors: P.map(p => PC[p]) }, { t: 'All slots (transactions)', type: 'line', labels: C.hour.data.labels, data: d, colors: ['#4a90d9'] }],
        tables: [{ t: 'Station-wise transactions', h: ['Station', 'Transactions'], r: bs.map(x => [x.s, nf(x.v)]) }, { t: 'Transactions in this slot (last 24 hours)', h: TXH, r: txr(tx) }]
      })
    }
    function kpiD(k) {
      const A = NA(), Af = SF(), tk = TA(), tx = XA(), L = sum(A, 'l'), Am = sum(A, 'a');
      const bp = P.map(p => { const r = A.filter(n => n.p == p); return { p, n: r.length, l: sum(r, 'l'), a: sum(r, 'a') } }).filter(x => x.n);
      const bs = ST.map((s, i) => { const r = A.filter(n => n.s == i); return { s, i, n: r.length, l: sum(r, 'l'), a: sum(r, 'a'), t: sum(r, 'l') / 11.8 } }).filter(x => x.n);
      const top = (a, key, f) => a.length ? [...a].sort((x, y) => y[key] - x[key])[0][f] : '-';
      const sC = (t, key, col) => ({ t, type: 'bar', labels: bs.map(x => x.s), data: bs.map(x => Math.round(x[key])), colors: bs.map(() => col) });
      const pC = (t, key) => ({ t, type: 'doughnut', labels: bp.map(x => x.p), data: bp.map(x => Math.round(x[key])), colors: bp.map(x => PC[x.p]) });
      const fC = (t, key, col) => ({ t, type: 'bar', labels: Af.map(x => x.s), data: Af.map(x => Math.round(x[key])), colors: Af.map(() => col) });
      const sT = { t: 'Station-wise', h: ['Station', 'Nozzles', 'Litres', 'Amount', 'Share'], r: bs.map(x => [x.s, x.n, nf(x.l), rs(x.a), pc(x.a, Am)]) };
      const pT = { t: 'Product-wise', h: ['Product', 'Nozzles', 'Litres', 'Rate / L', 'Amount', 'Share'], r: bp.map(x => [tag(x.p), x.n, nf(x.l), rs(PR[x.p]), rs(x.a), pc(x.a, Am)]) };
      const fT = { t: 'Station finance', h: FNH, r: fnr(Af) };
      if (k == 0) openD({ t: 'Today’s sales', s: ctx(), chips: [['Total sales', rs(Am)], ['Litres', nf(L) + ' L'], ['Avg rate / L', rs(L ? Am / L : 0)], ['Transactions (24h)', nf(tx.length)], ['Top station', top(bs, 'a', 's')], ['Top product', top(bp, 'a', 'p')]], charts: [pC('Sales by product (Rs)', 'a'), sC('Sales by station (Rs)', 'a', '#e8941a')], tables: [pT, sT] });
      else if (k == 1) openD({ t: 'Litres sold', s: ctx(), chips: [['Total', nf(L) + ' L'], ['Stations', bs.length], ['Products', bp.length], ['Top station', top(bs, 'l', 's')], ['Top product', top(bp, 'l', 'p')], ['Avg / nozzle', nf(A.length ? L / A.length : 0) + ' L']], charts: [sC('Litres by station', 'l', '#0e8fa6'), pC('Litres by product', 'l'), { t: 'Litres by station and product', type: 'bar', stack: true, labels: bs.map(x => x.s), sets: bp.map(b => ({ label: b.p, data: bs.map(x => Math.round(sum(A.filter(n => n.s == x.i && n.p == b.p), 'l'))), backgroundColor: PC[b.p], borderRadius: 3 })) }], tables: [{ t: 'Station by product (litres)', h: ['Station', ...bp.map(b => b.p), 'Total'], r: bs.map(x => [x.s, ...bp.map(b => nf(sum(A.filter(n => n.s == x.i && n.p == b.p), 'l'))), nf(x.l)]) }, pT] });
      else if (k == 2) { const hl = C.hour.data.labels, hd = C.hour.data.datasets[0].data, tt = hd.reduce((a, b) => a + b, 0); openD({ t: 'Transactions (24h)', s: ctx(), chips: [['Total', nf(tt)], ['Busiest slot', hl[hd.indexOf(Math.max(...hd))]], ['Avg litres / txn', f1(L / (tt || 1))], ['Avg value / txn', rs(Am / (tt || 1))], ['Recorded transactions', tx.length], ['Stations', bs.length]], charts: [{ t: 'Transactions per slot', type: 'line', labels: hl, data: hd, colors: ['#4a90d9'] }, sC('Transactions by station', 't', '#4a90d9'), pC('Litres by product', 'l')], tables: [{ t: 'Slot-wise', h: ['Slot', 'Transactions', 'Share'], r: hl.map((x, i) => [x, nf(hd[i]), pc(hd[i], tt)]) }, { t: 'Station-wise (estimated)', h: ['Station', 'Transactions', 'Litres'], r: bs.map(x => [x.s, nf(x.t), nf(x.l)]) }, { t: 'Transactions (last 24 hours)', h: TXH, r: txr(tx) }] }) }
      else if (k == 3) { const cl = C.cash.data.labels, cd = C.cash.data.datasets[0].data, ct = cd.reduce((a, b) => a + b, 0); openD({ t: 'Cash distribution', s: ctx(), chips: [['Total collected', rs(ct)], ['Cash', rs(cd[0])], ['Card', rs(cd[1])], ['Credit / party', rs(cd[2])], ['Fuel card', rs(cd[3])], ['Cash share', pc(cd[0], ct)]], charts: [{ t: 'Payment types', type: 'doughnut', labels: cl, data: cd, colors: ['#12a05c', '#4a90d9', '#e0453f', '#f59e0b'] }, { t: 'Cash by station (Rs)', type: 'bar', labels: Af.map(x => x.s), data: Af.map(x => Math.round(x.sale * .66)), colors: Af.map(() => '#12a05c') }, { t: 'Payment types by station', type: 'bar', stack: true, labels: Af.map(x => x.s), sets: cl.map((c, i) => ({ label: c, data: Af.map(x => Math.round(x.sale * [.66, .10, .21, .03][i])), backgroundColor: ['#12a05c', '#4a90d9', '#e0453f', '#f59e0b'][i] })) }], tables: [{ t: 'Payment types', h: ['Type', 'Amount', 'Share'], r: cl.map((c, i) => [c, rs(cd[i]), pc(cd[i], ct)]) }, { t: 'Station-wise', h: ['Station', ...cl], r: Af.map(x => [x.s, ...[.66, .10, .21, .03].map(s => rs(x.sale * s))]) }] }) }
      else if (k == 4) openD({ t: 'Active nozzles', s: ctx(), chips: [['Nozzles', A.length], ['Litres', nf(L) + ' L'], ['Avg / nozzle', nf(A.length ? L / A.length : 0) + ' L'], ['Top nozzle', A.length ? [...A].sort((a, b) => b.l - a.l).map(n => ST[n.s].split(' ')[0] + ' ' + n.d + n.id)[0] : '-'], ['Stations', bs.length], ['Products', bp.length]], charts: [{ t: 'Litres by nozzle', type: 'bar', labels: A.map(n => (st < 0 ? ST[n.s].split(' ')[0] + ' ' : '') + n.d + n.id), data: A.map(n => Math.round(n.l)), colors: A.map(n => PC[n.p]) }, pC('Nozzles by product', 'n'), sC('Nozzles by station', 'n', '#8b5cf6')], tables: [{ t: 'Nozzle readings', h: NZH, r: nzr(A) }, pT] });
      else if (k == 5) openD({ t: 'Party credit (fuel)', s: ctx(), chips: [['Total credit', rs(sum(Af, 'cre'))], ['Top station', top(Af, 'cre', 's')], ['Credit / sales', pc(sum(Af, 'cre'), sum(Af, 'sale'))], ['Stations', Af.length]], charts: [fC('Credit by station (Rs)', 'cre', '#8b5cf6'), { t: 'Credit by product (Rs)', type: 'doughnut', labels: bp.map(x => x.p), data: bp.map(x => Math.round(x.a * .21)), colors: bp.map(x => PC[x.p]) }], tables: [{ t: 'Station-wise credit', h: ['Station', 'Credit', '% of sales'], r: Af.map(x => [x.s, rs(x.cre), pc(x.cre, x.sale)]) }, { t: 'Party ledger', h: PRH, r: Af.flatMap(x => prt(x.i)) }] });
      else if (k == 6 || k == 7) { const neg = k == 6, r = tk.filter(t => neg ? t.v < 0 : t.v > 0).sort((a, b) => neg ? a.v - b.v : b.v - a.v), col = neg ? '#e0453f' : '#12a05c', key = neg ? 'loss' : 'gain', fv = a => neg ? lossOf(a) : gainOf(a); openD({ t: neg ? 'Stock loss' : 'Stock gain', s: ctx(), chips: [['Total value', rs(r.reduce((x, t) => x + Math.abs(gv(t)), 0))], ['Litres', nf(r.reduce((x, t) => x + Math.abs(t.v), 0)) + ' L'], ['Tanks', r.length], ['Worst station', top(Af, key, 's')]], charts: [fC('Value by station (Rs)', key, col), { t: 'Value by product (Rs)', type: 'bar', labels: P, data: P.map(p => Math.round(fv(r.filter(t => t.p == p)))), colors: P.map(p => PC[p]) }, { t: 'Litres by tank', type: 'bar', labels: r.map(t => ST[t.s].split(' ')[0] + ' · ' + t.p), data: r.map(t => Math.abs(t.v)), colors: r.map(() => col) }], tables: [{ t: 'Tanks', h: TKH, r: tkr(r) }, { t: 'Station-wise', h: ['Station', 'Stock loss', 'Stock gain', 'Net'], r: Af.map(x => [x.s, rs(x.loss), rs(x.gain), rs(x.gain - x.loss)]) }] }) }
      else { const inc = k == 8, key = inc ? 'inc' : 'exp'; openD({ t: inc ? 'Income' : 'Expenses', s: ctx(), chips: [['Total', rs(sum(Af, key))], ['Top station', top(Af, key, 's')], ['Income', rs(sum(Af, 'inc'))], ['Expense', rs(sum(Af, 'exp'))], ['Net', rs(sum(Af, 'inc') - sum(Af, 'exp'))], ['Stations', Af.length]], charts: [fC((inc ? 'Income' : 'Expense') + ' by station (Rs)', key, inc ? '#0e8fa6' : '#f59e0b'), { t: 'Income vs expense (Rs)', type: 'bar', labels: Af.map(x => x.s), sets: [{ label: 'Income', data: Af.map(x => Math.round(x.inc)), backgroundColor: '#0e8fa6', borderRadius: 5 }, { label: 'Expense', data: Af.map(x => Math.round(x.exp)), backgroundColor: '#f59e0b', borderRadius: 5 }] }, { t: 'Share by station', type: 'doughnut', labels: Af.map(x => x.s), data: Af.map(x => Math.round(x[key])), colors: PAL }], tables: [fT] }) }
    }
    function chartClick(c, el) {
      const k = Object.keys(C).find(x => C[x] == c), i = el.index, lb = c.data.labels[i];
      if (['top', 'low', 'lossS', 'gainS', 'inc', 'exp', 'cre'].includes(k)) stD(ST.indexOf(lb));
      else if (k == 'noz' && c._items[i]) nzD(c._items[i]);
      else if (k == 'tankLG' && c._items[i]) tkD(c._items[i]);
      else if (k == 'prod' || k == 'prodLG') pD(lb);
      else if (k == 'trend') dayD(i);
      else if (k == 'cash') cashD(i);
      else if (k == 'hour') hourD(i);
    }
  </script>
@endsection
