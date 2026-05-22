@extends('partials.layouts.master')

@section('title', 'Transactions | ' . Auth::user()->full_name)
@section('title-sub', 'Finance')
@section('pagetitle', 'Transactions')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">

<style>
    .card-hover:hover {
        transform: translateY(-5px);
        transition: transform 0.3s ease;
    }

    .required:after {
        content: " *";
        color: red;
    }

    .dataTables_filter,
    .dataTables_length,
    .dataTables_info,
    .dataTables_paginate {
        display: none !important;
    }

    .custom-pagination {
        margin-top: 15px;
        text-align: right;
    }

    .table-box {
        overflow-x: hidden !important;
        overflow-y: hidden !important;
        max-height: none !important;
    }

    /* Dynamic Fields Styling */
    .dynamic-field {
        transition: all 0.3s ease;
    }
</style>
@endsection

@section('content')
<div id="layout-wrapper">
    <div class="container-fluid mt-4">

        <!-- Accordion -->
        <div class="accordion accordion-primary accordion-border-box mb-4" id="accountAccordion">
            <!-- Create Account -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#createAccountCollapse" aria-expanded="true">
                        <i class="bi bi-person-plus me-2"></i> Transaction
                    </button>
                </h2>

                <!-- Toast Container -->
                <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
                    <div id="mainToast" class="toast align-items-center border-0" role="alert" aria-live="assertive"
                        aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body" id="toastMessage"></div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                aria-label="Close"></button>
                        </div>
                    </div>
                </div>

                <div id="createAccountCollapse" class="accordion-collapse collapse show"
                    data-bs-parent="#accountAccordion">
                    <div class="accordion-body">
                        <form id="accountForm" method="POST">
                            @csrf
                            <div class="row g-3 align-items-end">

                                <!-- Stations -->
                                <div class="col-3">
                                    <label for="stations" class="form-label">Station *</label>
                                    <select name="station_id" id="stations" class="form-select" required>
                                        <option value="">Search stations...</option>
                                    </select>
                                </div>

                                <!-- Shift (DYNAMIC - Based on Station) -->
                                <div class="col-3 dynamic-field" id="shift_container">
                                    <label for="shift_id" class="form-label">Shift *</label>
                                    <select name="shift_id" id="shift_id" class="form-select" required>
                                        <option value="">Select Shift...</option>
                                    </select>
                                </div>

                                <!-- User Type -->
                                <div class="col-3">
                                    <label class="form-label d-block">User Type *</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="user_type" id="customerRadio"
                                            value="Customer" required>
                                        <label class="form-check-label" for="customerRadio">Customer</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="user_type" id="supplierRadio"
                                            value="Supplier" required>
                                        <label class="form-check-label" for="supplierRadio">Supplier</label>
                                    </div>
                                </div>

                                <!-- User -->
                                <div class="col-3">
                                    <label for="user_account" class="form-label">User *</label>
                                    <select name="account_id" id="user_account" class="form-select" required>
                                        <option value="">Search User...</option>
                                    </select>
                                </div>

                                <!-- Method -->
                                <div class="col-3">
                                    <label for="method" class="form-label">Method *</label>
                                    <select name="method" id="method" class="form-select" required>
                                        <option value="">Select Method...</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank">Bank</option>
                                        <!-- Card aur Credit comment kardo -->
                                        <!-- <option value="card">Card</option> -->
                                        <!-- <option value="credit">Credit</option> -->
                                    </select>
                                </div>

                                <!-- To Account (DYNAMIC - Only for Bank) -->
                                <div class="col-3 dynamic-field" id="to_account_container" style="display: none;">
                                    <label for="to_account" class="form-label">To Account *</label>
                                    <select name="to_account" id="to_account" class="form-select">
                                        <option value="">Select Account...</option>
                                    </select>
                                </div>

                                <!-- Amount -->
                                <div class="col-3">
                                    <label for="amount" class="form-label">Amount *</label>
                                    <input type="number" name="amount" id="amount" class="form-control" step="0.01"
                                        min="0" required>
                                </div>

                                <!-- Date -->
                                <div class="col-3">
                                    <label for="date" class="form-label">Date *</label>
                                    <input type="text" class="form-control" id="accountdate" name="date"
                                        placeholder="Select a date" required>
                                </div>

                                <!-- Type -->
                                <div class="col-3">
                                    <label for="type" class="form-label">Type *</label>
                                    <select name="type" id="type" class="form-select" required>
                                        <option value="">Select Type...</option>
                                        <option value="income">Income - Credit</option>
                                        <option value="expense">Expense - Debit</option>
                                    </select>
                                </div>

                                <!-- Note -->
                                <div class="col-3">
                                    <label for="note" class="form-label">Note</label>
                                    <input type="text" name="note" id="note" class="form-control">
                                </div>

                            </div>

                            <input type="hidden" id="account_id" name="id">

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Create Transaction</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div><!-- /accordion -->

        <!-- Accounts Table -->
        <div class="card shadow-sm card-hover">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Accounts List</h5>
            </div>
            <div class="col-3" style="padding-left:1em;padding-top:1em;">
                <select name="type" id="tabletype" class="form-select" required>
                    <option value="">Select Type...</option>
                    <option value="Income">Income</option>
                    <option value="Expense">Expense</option>
                </select>
            </div>
            <div class="card-body">
                <div class="table-box table-responsive">
                    <table id="accountsTable" class="table text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>SNO</th>
                                <th>Date</th>
                                <th>Station</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Payer/Receiver</th> <!-- Updated header -->
                                <th>Method</th>
                                <th>Bank Account</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>
<script src="{{ asset('assets/js/ui/air-datepicker.init.js') }}"></script>

<script>
    const AUTH_USER_ID = "{{ Auth::id() }}";
    const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";

    let table;
    let currentTableAjaxUrl = null;
    let userPermissions = [];

    $(document).ready(function () {
        // ✅ Permissions load karo
        $.get(`/api/getpermissionbyuserid/{{ Auth::user()->id }}/{{ Auth::user()->role }}`, function (
            permissions) {
            userPermissions = permissions;
            console.log("Loaded permissions:", userPermissions);

            // Hide Add button if not allowed
            if (!hasPermission('transactions', 'create')) {
                $('#accountAccordion').hide();
            }
        });

        // ✅ Load dropdowns from API
        loadStations();

        // ✅ Init Date Picker
        new AirDatepicker('#accountdate', {
            autoClose: true,
            dateFormat: 'yyyy-MM-dd',
            locale: localeEn,
        });

        // ✅ Station change pe shifts load karo
        $('#stations').on('change', function () {
            const stationId = $(this).val();
            if (stationId) {
                loadShifts(stationId);
                loadUsersStationAware($("input[name='user_type']:checked").val());
            } else {
                clearShiftsDropdown();
            }
        });

        // ✅ Method change pe dynamic fields control
        $('#method').on('change', function () {
            handleMethodChange($(this).val());
        });

        // ✅ User Type change
        $("input[name='user_type']").on("change", function () {
            const stationId = $('#stations').val();
            if (stationId) {
                loadUsersStationAware($(this).val());
            } else {
                loadUsers($(this).val());
            }
        });

        // ✅ Type filter change handler
        $('#tabletype').on('change', function () {
            if (table && table.ajax) {
                const typeFilter = $(this).val();
                let url = currentTableAjaxUrl;

                if (typeFilter) {
                    url = currentTableAjaxUrl + (currentTableAjaxUrl.includes('?') ? '&' : '?') + 'type=' + typeFilter.toLowerCase();
                }

                table.ajax.url(url).load();
            }
        });

        // ✅ DataTable init (role-aware) - FIXED VERSION
        (function initTransactionsTable() {
            // Define URLs based on role
            let apiUrl;
            if (AUTH_ROLE === 'admin') {
                apiUrl = '/api/transactions';
            } else if (AUTH_ROLE === 'employee') {
                apiUrl = `/api/transactions/employee/${AUTH_USER_ID}`;
            } else {
                apiUrl = `/api/transactions/user/${AUTH_USER_ID}`;
            }

            currentTableAjaxUrl = apiUrl;

            // Create DataTable configuration
            table = $('#accountsTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: apiUrl,
                    dataSrc: "" // Direct array aa raha hai
                },
                paging: false,
                searching: false,
                info: false,
                lengthChange: false,
                ordering: false,
                dom: 't',
                columns: [
                    {
                        data: null,
                        render: function (data, type, row, meta) {
                            // S.No generate karo
                            return meta.row + 1;
                        }
                    },
                    {
                        data: "created_at",
                        render: function (data) {
                            return data ? new Date(data).toLocaleDateString() : '-';
                        }
                    },
                    {
                        data: "station_name",
                        render: function (data) {
                            return data || '-';
                        }
                    },
                    {
                        data: "debit",
                        render: function (data) {
                            return data && parseFloat(data) > 0 ? 'Rs. ' + parseFloat(data).toFixed(2) : '-';
                        },
                        className: "text-end"
                    },
                    {
                        data: "credit",
                        render: function (data) {
                            return data && parseFloat(data) > 0 ? 'Rs. ' + parseFloat(data).toFixed(2) : '-';
                        },
                        className: "text-end"
                    },
                    {
                        // Conditional column: Payer/Receiver
                        data: null,
                        render: function (data, type, row) {
                            if (row.type === 'income') {
                                // Income case: Show Payer
                                return `<span class="badge bg-success">Payer:</span> ${row.account_name || '-'}`;
                            } else if (row.type === 'expense') {
                                // Expense case: Show Receiver
                                return `<span class="badge bg-danger">Receiver:</span> ${row.account_name || '-'}`;
                            }
                            return row.account_name || '-';
                        }
                    },
                    {
                        data: "method",
                        render: function (data) {
                            if (!data) return '-';
                            const methodMap = {
                                'cash': 'Cash',
                                'bank': 'Bank',
                                'card': 'Card',
                                'credit': 'Credit'
                            };
                            return methodMap[data] || data.charAt(0).toUpperCase() + data.slice(1);
                        }
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            // Bank account details show karein agar bank method hai
                            if (row.method === 'bank') {
                                if (row.to_account_name) {
                                    return row.to_account_name;
                                } else if (row.bank_name) {
                                    return row.bank_name + (row.account_number ? ` (${row.account_number})` : '');
                                }
                            }
                            return 'N/A';
                        }
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            let buttons = '';

                            if (hasPermission('transactions', 'update')) {
                                buttons += `
                            <button class="btn btn-sm btn-primary" onclick="viewAccount(${row.id})">
                                <i class="bi bi-eye"></i>
                            </button>`;
                            }

                            if (hasPermission('transactions', 'delete')) {
                                buttons += `
                            <button class="btn btn-sm btn-danger" onclick="deleteAccount(${row.id})">
                                <i class="bi bi-trash"></i>
                            </button>`;
                            }

                            return buttons ?
                                `<div class="btn-group btn-group-sm" role="group">${buttons}</div>` :
                                `<span class="text-muted small">No actions</span>`;
                        },
                        className: "text-center"
                    }
                ],
                language: {
                    emptyTable: "No transactions found",
                    loadingRecords: "Loading...",
                    zeroRecords: "No matching records found"
                },

            });
        })();

        // ✅ Submit Form
        // ✅ Submit Form
        $('#accountForm').on('submit', function (e) {
            e.preventDefault();

            // Validate required fields
            if (!$('#stations').val()) {
                showToast("Please select a station!", true);
                return;
            }

            if (!$('#shift_id').val()) {
                showToast("Please select a shift!", true);
                return;
            }

            // Bank method ke liye additional validation
            const method = $('#method').val();
            if (method === 'bank' && !$('#to_account').val()) {
                showToast("Please select a bank account!", true);
                return;
            }

            let accountId = $("#account_id").val();
            let formData = $(this).serialize();
            let url = "/api/transactions";
            let methodType = "POST";

            if (accountId) {
                url = `/api/transactions/${accountId}`;
                methodType = "PUT";
            }

            // Show loading
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

            $.ajax({
                url,
                method: methodType,
                data: formData,
                success: function (res) {
                    let successMsg = accountId ? "✅ Updated successfully!" : "✅ Created successfully!";

                    // Agar bank method hai toh additional message
                    if (method === 'bank') {
                        successMsg += " Bank account balance has been updated.";
                    }

                    showToast(successMsg);

                    // ✅ DataTable refresh karo
                    if (table && table.ajax) {
                        table.ajax.reload(null, false);
                    }

                    $('#accountForm')[0].reset();
                    $("#account_id").val("");
                    // Reset dynamic fields
                    $('#to_account_container').hide();
                    clearShiftsDropdown();

                    // Reset button
                    submitBtn.prop('disabled', false).html(originalText);
                },
                error: xhr => {
                    console.error(xhr.responseText);
                    const errorMsg = xhr.responseJSON?.message || '❌ Error saving!';
                    showToast(errorMsg, true);

                    // Reset button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    });

    // ✅ Load Shifts for selected Station
    function loadShifts(stationId) {
        if (!stationId) return;

        $.ajax({
            url: `/api/stations/${stationId}/open-shifts`,
            method: 'GET',
            success: function (resp) {
                console.log("Shifts API Response:", resp);

                if (resp && resp.data && Array.isArray(resp.data)) {
                    const shifts = resp.data;
                    populateShiftsDropdown(shifts);
                } else {
                    showToast("No open shifts found for this station", true);
                    clearShiftsDropdown();
                }
            },
            error: function (xhr) {
                console.error("Error loading shifts:", xhr.responseText);
                showToast("Error loading shifts", true);
                clearShiftsDropdown();
            }
        });
    }

    // ✅ Populate Shifts Dropdown
    function populateShiftsDropdown(shifts) {
        const element = document.querySelector('#shift_id');
        if (!element) return;

        if (element.choicesInstance) {
            element.choicesInstance.destroy();
        }

        const choices = new Choices(element, {
            searchEnabled: true,
            removeItemButton: false,
            placeholderValue: 'Select Shift',
            shouldSort: false
        });

        element.choicesInstance = choices;

        // Format shifts for display
        const shiftOptions = shifts.map(shift => ({
            value: shift.id,
            label: `Shift #${shift.shift_no} (${shift.start_time})`,
            customProperties: {
                shift_no: shift.shift_no,
                start_time: shift.start_time
            }
        }));

        choices.setChoices(shiftOptions, 'value', 'label', true);
    }

    // ✅ Clear Shifts Dropdown
    function clearShiftsDropdown() {
        const element = document.querySelector('#shift_id');
        if (!element) return;

        if (element.choicesInstance) {
            element.choicesInstance.destroy();
        }

        const choices = new Choices(element, {
            searchEnabled: true,
            removeItemButton: false,
            placeholderValue: 'Select Shift',
            shouldSort: false
        });

        element.choicesInstance = choices;
        choices.setChoices([{
            value: '',
            label: 'Select Shift...'
        }], 'value', 'label', true);
    }

    // ✅ Handle Method Change (Dynamic Fields)
    function handleMethodChange(method) {
        const toAccountContainer = $('#to_account_container');

        if (method === 'bank') {
            // Show to_account field and load bank accounts
            toAccountContainer.show();
            loadBankAccounts();
            // Make to_account required
            $('#to_account').prop('required', true);
        } else {
            // Hide to_account field
            toAccountContainer.hide();
            // Make to_account not required
            $('#to_account').prop('required', false);
            // Clear selection
            $('#to_account').val('');
        }
    }

    // ✅ Load Stations (role-aware, uses absolute API path and normalizes response)
    function loadStations() {
        let apiUrl;
        if (AUTH_ROLE === 'admin') {
            apiUrl = '/api/stations';
        } else if (AUTH_ROLE === 'employee') {
            apiUrl = `/api/stations_emp/${AUTH_USER_ID}`;
        } else {
            apiUrl = `/api/stations/${AUTH_USER_ID}`;
        }

        $.ajax({
            url: apiUrl,
            method: 'GET',
            success: function (resp) {
                // normalize response: either [] or { data: [] }
                const stations = Array.isArray(resp) ? resp : (resp && Array.isArray(resp.data) ?
                    resp.data : []);
                populateDropdown('#stations', stations, 'id', 'name');
            },
            error: function () {
                showToast("Error loading stations", true);
            }
        });
    }

    // ✅ Load Users by Type (normalized AJAX)
    function loadUsers(type) {
        if (!type) return;
        const url = `/api/accounts/category/${type.toLowerCase()}`;
        $.ajax({
            url: url,
            method: 'GET',
            success: function (resp) {
                const users = Array.isArray(resp) ? resp : (resp && Array.isArray(resp.data) ?
                    resp.data : []);
                populateDropdown('#user_account', users, 'id', 'name');
            },
            error: function () {
                showToast("Error loading users", true);
            }
        });
    }

    // ✅ Station-aware users loader: if a station is selected, fetch station accounts and filter by type.
    function loadUsersStationAware(type) {
        if (!type) return;
        const typeLower = type.toLowerCase();
        const stationId = $('#stations').val();

        if (stationId) {
            // fetch accounts for the station and filter client-side by type
            $.ajax({
                url: `/api/stations/${stationId}/accounts`,
                method: 'GET',
                success: function (resp) {
                    const accounts = Array.isArray(resp) ? resp : (resp && Array.isArray(resp.data) ?
                        resp.data : []);
                    const filtered = accounts.filter(a => (a.type || '').toString()
                        .toLowerCase() === typeLower);
                    populateDropdown('#user_account', filtered, 'id', 'name');
                },
                error: function () {
                    // fallback to category endpoint on error
                    loadUsers(type);
                }
            });
        } else {
            // no station selected — fallback to category endpoint
            loadUsers(type);
        }
    }

    // ✅ Load Bank Accounts (Only for Bank method)
    function loadBankAccounts() {
        const stationId = $('#stations').val();
        if (stationId) {
            $.ajax({
                url: `/api/stations/${stationId}/accounts`,
                method: 'GET',
                success: function (resp) {
                    const accounts = Array.isArray(resp) ? resp : (resp && Array.isArray(resp.data) ?
                        resp.data : []);
                    const banks = accounts.filter(a => (a.type || '').toString()
                        .toLowerCase() === 'bank');

                    // Format for display: Bank Name - Account Number
                    const formattedBanks = banks.map(bank => ({
                        id: bank.id,
                        name: `${bank.name} - ${bank.account_number} (${bank.bank_name})`
                    }));

                    populateDropdown('#to_account', formattedBanks, 'id', 'name');
                },
                error: function () {
                    // fallback
                    $.get(`/api/accounts/category/bank`, res => {
                        const banks = Array.isArray(res) ? res : (res && Array.isArray(res
                            .data) ? res.data : []);
                        const formattedBanks = banks.map(bank => ({
                            id: bank.id,
                            name: `${bank.name} - ${bank.account_number} (${bank.bank_name})`
                        }));
                        populateDropdown('#to_account', formattedBanks, 'id', 'name');
                    }).fail(() => showToast("Error loading bank accounts", true));
                }
            });
        } else {
            $.get(`/api/accounts/category/bank`, res => {
                const banks = Array.isArray(res) ? res : (res && Array.isArray(res.data) ?
                    res.data : []);
                const formattedBanks = banks.map(bank => ({
                    id: bank.id,
                    name: `${bank.name} - ${bank.account_number} (${bank.bank_name})`
                }));
                populateDropdown('#to_account', formattedBanks, 'id', 'name');
            }).fail(() => showToast("Error loading bank accounts", true));
        }
    }

    // ✅ View Account
    function viewAccount(id) {
        $.get(`/api/transactions/${id}`, function (data) {
            console.log("View Account Data:", data);

            // ✅ Step 1: Basic fields fill karo
            $("#account_id").val(data.id);
            $("#amount").val(data.debit > 0 ? data.debit : data.credit);
            $("#accountdate").val(data.created_at.split(" ")[0]);
            $("#note").val(data.note || "");
            $("#method").val(data.method);
            $("#type").val(data.type);

            // ✅ Step 2: Handle method dynamic field
            handleMethodChange(data.method);

            // If method is bank, also set to_account
            if (data.method === 'bank' && data.to_account) {
                setTimeout(() => {
                    $('#to_account').val(data.to_account);
                    if ($('#to_account').data('choices')) {
                        $('#to_account').data('choices').setChoiceByValue(data.to_account
                            .toString());
                    }
                }, 300);
            }

            // ✅ Step 3: Station dropdown
            $.get("/api/stations", function (stations) {
                const stationsArray = Array.isArray(stations) ? stations : (stations &&
                    Array.isArray(stations.data) ? stations.data : []);
                populateDropdown('#stations', stationsArray, 'id', 'name', data.station_id);

                // Load shifts for this station
                if (data.station_id) {
                    setTimeout(() => {
                        loadShifts(data.station_id);
                        // Set shift value after loading
                        if (data.shift_id) {
                            setTimeout(() => {
                                $('#shift_id').val(data.shift_id);
                                if ($('#shift_id').data('choices')) {
                                    $('#shift_id').data('choices').setChoiceByValue(data
                                        .shift_id.toString());
                                }
                            }, 500);
                        }
                    }, 300);
                }
            });

            // ✅ Step 4: User type set karo + users load karo
            let userType = data.type === "income" ? "customer" : "supplier";
            console.log("Setting user type to:", userType);

            // Radio button select karo
            $(`#${userType}Radio`).prop("checked", true);

            // ✅ IMPORTANT: Directly users load karo without waiting for change event
            const stationId = data.station_id;
            if (stationId) {
                $.get(`/api/stations/${stationId}/accounts`, function (accounts) {
                    const accountsArray = Array.isArray(accounts) ? accounts : (accounts &&
                        Array.isArray(accounts.data) ? accounts.data : []);
                    const filtered = accountsArray.filter(a => (a.type || '')
                        .toString().toLowerCase() === userType);
                    populateDropdown('#user_account', filtered, 'id', 'name', data
                        .account_id);
                }).fail(() => {
                    // Fallback
                    $.get(`/api/accounts/category/${userType}`, function (users) {
                        const usersArray = Array.isArray(users) ? users : (users &&
                            Array.isArray(users.data) ? users.data : []);
                        populateDropdown('#user_account', usersArray, 'id', 'name',
                            data.account_id);
                    });
                });
            } else {
                $.get(`/api/accounts/category/${userType}`, function (users) {
                    const usersArray = Array.isArray(users) ? users : (users && Array
                        .isArray(users.data) ? users.data : []);
                    populateDropdown('#user_account', usersArray, 'id', 'name', data
                        .account_id);
                });
            }

        }).fail(function () {
            showToast("Error loading transaction data", true);
        });
    }

    // ✅ Delete
    function deleteAccount(id) {
        if (!confirm("Are you sure you want to delete this transaction?")) return;

        $.ajax({
            url: `/api/transactions/${id}`,
            method: "DELETE",
            success: function () {
                showToast("Deleted successfully!");
                if (table && table.ajax) {
                    table.ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error deleting!';
                showToast(errorMsg, true);
            }
        });
    }

    // ✅ Toast
    function showToast(message, isError = false) {
        const toastEl = document.getElementById("mainToast");
        const toastBody = document.getElementById("toastMessage");
        toastBody.textContent = message;
        toastEl.className = "toast align-items-center border-0 " +
            (isError ? "text-bg-danger" : "text-bg-success");
        new bootstrap.Toast(toastEl, {
            delay: 3000
        }).show();
    }

    // ✅ Populate Dropdown Choices
    function populateDropdown(selector, items, valueField, textField, selectedValue = null) {
        const element = document.querySelector(selector);
        if (!element) return;

        if (element.choicesInstance) element.choicesInstance.destroy();

        const choices = new Choices(element, {
            searchEnabled: true,
            removeItemButton: false,
            placeholderValue: 'Select',
            shouldSort: false
        });

        element.choicesInstance = choices;

        choices.setChoices(
            items.map(i => ({
                value: i[valueField],
                label: i[textField],
                selected: selectedValue && selectedValue == i[valueField],
                disabled: false
            })),
            'value', 'label', true
        );

        if (selectedValue) {
            choices.setChoiceByValue(selectedValue.toString());
        }
    }

    // ✅ Permission check function
    function hasPermission(moduleName, action) {
        const module = userPermissions.find(p => p.name === moduleName);
        if (!module) return false;
        return module[action] == 1;
    }
</script>
@endsection