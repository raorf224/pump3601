@extends('partials.layouts.master')

@section('title', 'Account Statement')

@section('title-sub', 'Base UI')
@section('Account Statement', 'Datatable')
@section('css')
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
@endsection
@section('content')

    <!--begin::App-->
    <div id="layout-wrapper">

        <div class="container-fluid mt-4">
            <div class="col-12">
                <div class="card">
                    <!--start::card-->
                    <div class="card-header">
                        <h5 class="card-title mb-0"> Account Statment </h5>
                    </div>
                    <div class="card-body">
                        <table id="buttons-datatables" class="table table-striped text-center w-100">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Site</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Total Credit</th>
                                    <th>Total Debit</th>
                                    <th>Net Balance</th>

                                </tr>
                            </thead>

                        </table>
                        <!-- end:: Buttons Datatables -->
                    </div>
                </div>
                <!--end::card-->
            </div>

        </div><!--End row-->
    </div><!--End container-fluid-->
    </main><!--End app-wrapper-->

@endsection

@section('js')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <script src="assets/js/table/datatable.init.js"></script>
    <script type="module" src="assets/js/app.js"></script>
    <script>
        const AUTH_USER_ID = "{{ Auth::id() }}";
        console.log('user id', AUTH_USER_ID);

        const AUTH_ROLE = "{{ Auth::user()->role }}"; 
        console.log("Authenticated User role:", AUTH_ROLE);


        $(document).ready(function () {
            let apiUrl = '';

            if (AUTH_ROLE === 'admin') {
                // Admin: all stations
                apiUrl = `api/account/view`;
            } else if (AUTH_ROLE === 'owner') {
                // Owner: only their stations
                apiUrl = `api/account/view/${AUTH_USER_ID}`;
            } else if (AUTH_ROLE === 'employee') {
                // Employee: only their assigned station
                apiUrl = `api/stations_emp/${AUTH_USER_ID}`; // fetch station first
            }

            if (AUTH_ROLE === 'employee') {
                $.get(`api/account/emp/${AUTH_USER_ID}`, function (stationData) {
                    if (stationData && stationData.owner_user_id) {
                        loadTable(`api/account/view/${stationData.owner_user_id}`);
                    } else {
                        console.error("No assigned station found for employee.");
                    }
                }).fail(function (err) {
                    console.error("Error fetching employee station:", err);
                });
            }
            else {
                // Admin/Owner directly load
                loadTable(apiUrl);
            }

            function loadTable(url) {
                $('#buttons-datatables').DataTable({
                    destroy: true,
                    ajax: {
                        url: url,
                        dataSrc: ""
                    },
                    columns: [
                        { data: null, render: (data, type, row, meta) => meta.row + 1 },
                        { data: "station_name", defaultContent: "N/A" },
                        { data: "account_name", defaultContent: "N/A" },
                        { data: "account_type", defaultContent: "N/A" },
                        { data: "total_income", defaultContent: "0.00" },
                        { data: "total_expense", defaultContent: "0.00" },
                        { data: "net_balance", defaultContent: "0.00" },
                    ],
                    responsive: true,
                    dom: 'Bfrtip',
                });
            }
        });
    </script>




@endsection