@extends('partials.layouts.master')

@section('title', 'Site Total Amount | ' . Auth::user()->full_name)
@section('title-sub', 'Accounts')
@section('pagetitle', 'Site Total Amount Management')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>

            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Site Total Amount</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAmountModal">
                        <i class="bi bi-plus-circle me-2"></i> Add New Amount
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped  align-middle text-center" id="amountTable">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Station Name</th>
                                    <th>Account Name</th>
									<th>Type</th>
                                    <th>Account Number</th>
                                    <th>Previous Amount</th>
                                    <th>Current Amount</th>
                                    <th>Date</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center">Loading records...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Amount Modal -->
    <div class="modal fade" id="addAmountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Site Total Amount</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addAmountForm">
                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label class="form-label required-label">Select Station</label>
                                <select class="form-select" id="modalStation" name="station_id" required>
                                    <option value="">Search Station...</option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label required-label">Select Account</label>
                                <select class="form-select" id="modalAccount" name="account_id" required>
                                    <option value="">Select Account...</option>
                                </select>
                            </div>

                            <!-- User can enter previous amount manually -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label required-label">Previous Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs</span>
                                    <input type="number" class="form-control" id="manualPreviousAmount"
                                        name="manual_previous_amount" placeholder="Enter previous amount" step="0.01"
                                        min="0" value="0" readonly>
                                </div>
                                <small class="text-muted">
                                    Enter previous amount manually (for first deposit, enter 0)
                                </small>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="useManualPrevious">
                                    <label class="form-check-label" for="useManualPrevious">
                                        Use manual previous amount
                                    </label>
                                </div>
                            </div>

                            <!-- Current/New Amount -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label required-label">New Amount (Deposit)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs</span>
                                    <input type="number" class="form-control" id="modalAmount" name="amount"
                                        placeholder="Enter deposit amount" step="0.01" min="0.01" required>
                                </div>
                                <small class="text-muted">Enter the amount to deposit</small>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label required-label">Date</label>
                                <input type="text" class="form-control" id="modalDate" name="date" placeholder="Select date"
                                    required>
                                <small class="text-muted">Select the date of deposit</small>
                            </div>

                            <!-- Display auto-fetched previous amount -->
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Previous Amount:</strong>
                                    Rs. <span id="autoPreviousAmount">0.00</span>

                                </div>
                            </div>

                            <!-- Total after deposit -->
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-success">
                                    <i class="bi bi-calculator me-2"></i>
                                    <strong>Total After Deposit:</strong>
                                    Rs. <span id="totalAfterDeposit">0.00</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveAmount">Save Amount</button>
                </div>
            </div>
        </div>
    </div>
    </main>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        const apiBase = "api";
        const AUTH_USER_ID = "{{ Auth::id() }}";
        const AUTH_ROLE = "{{ Auth::check() ? strtolower(Auth::user()->role) : '' }}";

        let stationChoices, accountChoices;
        let selectedStationId = null;

        // ✅ Initialize everything
        $(document).ready(function () {
            // Load initial data
            loadStationsForModal();
            loadAmountRecords();

            // Initialize date picker
            flatpickr("#modalDate", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "F j, Y",
                allowInput: true,
                defaultDate: "today"
            });

            // Toastr configuration
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };
        });

        // ✅ Load stations for modal
        function loadStationsForModal() {
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
                success: function (data) {
                    let stationSelect = $("#modalStation");
                    stationSelect.empty().append(`<option value="">Search Station...</option>`);
                    (Array.isArray(data) ? data : []).forEach(st => stationSelect.append(
                        `<option value="${st.id}">${st.name}</option>`));

                    if (stationChoices) stationChoices.destroy();
                    stationChoices = new Choices("#modalStation", {
                        searchEnabled: true,
                        itemSelectText: '',
                        shouldSort: false,
                        placeholderValue: "Search Station...",
                        removeItemButton: true
                    });
                },
                error: function (err) {
                    console.error('Failed to load stations', err);
                }
            });
        }

        // ✅ Load accounts when station is selected
        $(document).on('change', '#modalStation', function () {
            selectedStationId = $(this).val();
            if (selectedStationId) {
                loadAccountsForStation(selectedStationId);
                // Load previous amount for this station-account combination
                loadPreviousAmount();
            } else {
                $('#modalAccount').html('<option value="">Select Account...</option>');
                $('#previousAmount').text('0.00');
                if (accountChoices) accountChoices.destroy();
            }
        });

// ✅ Load BANK accounts for selected station
function loadAccountsForStation(stationId) {
    // Change the URL to use the correct route
    $.ajax({
        url: `/api/site-total-amount/${stationId}/accounts`,  // Updated URL
        method: 'GET',
        success: function (resp) {
            const accounts = Array.isArray(resp) ? resp : (resp && Array.isArray(resp.data) ? resp.data : []);

            let accountSelect = $("#modalAccount");
            accountSelect.empty().append(`<option value="">Select Bank Account...</option>`);

            if (accounts && accounts.length > 0) {
                accounts.forEach(account => {
                    // Format display name for bank accounts
                    let displayName = account.name;
                    if (account.account_number) {
                        displayName += ` - ${account.account_number}`;
                    }
                    if (account.bank_name) {
                        displayName += ` (${account.bank_name})`;
                    }

                    accountSelect.append(
                        `<option value="${account.id}">${displayName}</option>`
                    );
                });
            } else {
                accountSelect.append(`<option value="">No bank accounts found</option>`);
            }

            // Initialize Choices.js for accounts
            if (accountChoices) accountChoices.destroy();
            accountChoices = new Choices("#modalAccount", {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false,
                placeholderValue: "Select Bank Account...",
                removeItemButton: true
            });
            
            // Previous amount load karein jab accounts load ho jayein
            if ($('#modalAccount').val()) {
                loadPreviousAmount();
            }
        },
        error: function (err) {
            console.error('Failed to load bank accounts:', err);
            $('#modalAccount').html('<option value="">Error loading bank accounts</option>');
            toastr.error('Failed to load bank accounts for this station');
        }
    });
}

        // ✅ Load previous amount for station-account combination
        function loadPreviousAmount() {
            const stationId = $('#modalStation').val();
            const accountId = $('#modalAccount').val();
            const useManual = $('#useManualPrevious').is(':checked');

            if (!stationId || !accountId) {
                $('#autoPreviousAmount').text('0.00');
                calculateTotal();
                return;
            }

            // If user is using manual input, don't fetch
            if (useManual) {
                $('#autoPreviousAmount').text('0.00 (manual input in use)');
                calculateTotal();
                return;
            }

            $.ajax({
                url: `${apiBase}/site-total-amount/latest`,
                method: 'GET',
                data: {
                    station_id: stationId,
                    account_id: accountId
                },
                success: function (response) {
                    let previousAmount = 0;

                    if (response && response.previous_amount !== undefined) {
                        previousAmount = response.previous_amount;
                        $('#autoPreviousAmount').text(previousAmount.toFixed(2));

                        // Also update manual input field with this value (optional)
                        $('#manualPreviousAmount').val(previousAmount);
                    } else {
                        $('#autoPreviousAmount').text('0.00 (no previous record)');
                        $('#manualPreviousAmount').val(0);
                    }

                    calculateTotal();
                },
                error: function (err) {
                    console.error('Failed to load previous amount:', err);
                    $('#autoPreviousAmount').text('0.00');
                    calculateTotal();
                }
            });
        }

        // ✅ Toggle manual previous amount input
        $('#useManualPrevious').on('change', function () {
            const isChecked = $(this).is(':checked');

            if (isChecked) {
                $('#manualPreviousAmount').prop('readonly', false).focus();
                $('#autoPreviousAmount').text('0.00 (manual input enabled)');
            } else {
                $('#manualPreviousAmount').prop('readonly', true);
                // Reload auto-fetched value
                loadPreviousAmount();
            }
            calculateTotal();
        });

        // ✅ Calculate total after deposit
        function calculateTotal() {
            let previousAmount = 0;
            const useManual = $('#useManualPrevious').is(':checked');

            if (useManual) {
                // Use manual input
                previousAmount = parseFloat($('#manualPreviousAmount').val()) || 0;
            } else {
                // Use auto-fetched value
                previousAmount = parseFloat($('#autoPreviousAmount').text()) || 0;
            }

            const newAmount = parseFloat($('#modalAmount').val()) || 0;
            const total = previousAmount + newAmount;

            $('#totalAfterDeposit').text(total.toFixed(2));
        }

        // ✅ When manual input or new amount changes
        $('#manualPreviousAmount, #modalAmount').on('input', function () {
            calculateTotal();
        });

        // ✅ When account is changed, also load previous amount
        $(document).on('change', '#modalAccount', function () {
            loadPreviousAmount();
        });

        // ✅ Load all amount records
        function loadAmountRecords() {
            let apiUrl;
            if (AUTH_ROLE === 'admin') {
                apiUrl = `${apiBase}/site-total-amount`;
            } else if (AUTH_ROLE === 'employee') {
                apiUrl = `${apiBase}/site-total-amount/employee/${AUTH_USER_ID}`;
            } else {
                apiUrl = `${apiBase}/site-total-amount/user/${AUTH_USER_ID}`;
            }

            $.ajax({
                url: apiUrl,
                method: 'GET',
                success: function (response) {
                    const records = Array.isArray(response) ? response :
                        (response && response.data && Array.isArray(response.data) ? response.data :
                            (response && Array.isArray(response) ? response : []));

                    let rows = "";
                    if (records && records.length > 0) {
                        records.forEach((record, index) => {
                            // Format dates
                            const date = record.date ? new Date(record.date).toLocaleDateString() : '-';
                            const createdAt = record.created_at ? new Date(record.created_at).toLocaleDateString() : '-';

                            rows += `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${record.station_name || '-'}</td>
                                            <td>${record.account_name || '-'}</td>
											<td>${record.type || '-'}</td>
                                            <td>${record.account_number || '-'}</td>
                                            <td class="text-end">Rs. ${(parseFloat(record.previous_amount) || 0).toFixed(2)}</td>
                                            <td class="text-end">Rs. ${(parseFloat(record.amount) || 0).toFixed(2)}</td>
                                            <td>${date}</td>
                                            <td>${createdAt}</td>
                                        </tr>`;
                        });
                    } else {
                        rows = `<tr><td colspan="7" class="text-center">No records found</td></tr>`;
                    }

                    $("#amountTable tbody").html(rows);
                },
                error: function (err) {
                    console.error('Failed to load amount records:', err);
                    $("#amountTable tbody").html(
                        `<tr><td colspan="7" class="text-center text-danger">Failed to load records</td></tr>`);
                }
            });
        }

        // ✅ Save new amount
$('#saveAmount').on('click', function () {
    const stationId = $('#modalStation').val();
    const accountId = $('#modalAccount').val();
    const useManual = $('#useManualPrevious').is(':checked');

    let previousAmount = 0;
    if (useManual) {
        previousAmount = parseFloat($('#manualPreviousAmount').val()) || 0;
    } else {
        previousAmount = parseFloat($('#autoPreviousAmount').text()) || 0;
    }

    const newAmount = parseFloat($('#modalAmount').val()) || 0;
    const date = $('#modalDate').val();

    // Validation
    if (!stationId) {
        toastr.error('Please select a station');
        return;
    }

    if (!accountId) {
        toastr.error('Please select an account');
        return;
    }

    if (newAmount <= 0) {
        toastr.error('Please enter a valid amount');
        return;
    }

    if (!date) {
        toastr.error('Please select a date');
        return;
    }

    // Show loading
    const saveBtn = $(this);
    const originalText = saveBtn.html();
    saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    // ✅ CORRECTED: Prepare data WITHOUT created_by
    const formData = {
        station_id: parseInt(stationId),
        account_id: parseInt(accountId),
        amount: parseFloat(newAmount),
        previous_amount: parseFloat(previousAmount),
        date: date
    };

    console.log("📦 Sending data:", formData);

    // Send request
    $.ajax({
        url: `${apiBase}/site-total-amount`,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function (response) {
            console.log("✅ Response:", response);
            
            if (response.success) {
                toastr.success('Amount saved successfully!');
                
                // Close modal and reset form
                $('#addAmountModal').modal('hide');
                $('#addAmountForm')[0].reset();
                $('#useManualPrevious').prop('checked', false);
                $('#autoPreviousAmount').text('0.00');
                $('#totalAfterDeposit').text('0.00');

                // Reset choices
                if (stationChoices) stationChoices.clearStore();
                if (accountChoices) accountChoices.clearStore();

                // Reload records
                loadAmountRecords();
            } else {
                toastr.error(response.message || 'Failed to save amount');
            }
            
            // Reset button
            saveBtn.prop('disabled', false).html(originalText);
        },
        error: function (xhr) {
            console.error('Save error:', xhr.responseText);
            const errorMsg = xhr.responseJSON?.message || 'Failed to save amount';
            toastr.error(errorMsg);
            saveBtn.prop('disabled', false).html(originalText);
        }
    });
});
        // ✅ Reset modal when closed
        $('#addAmountModal').on('hidden.bs.modal', function () {
            $('#addAmountForm')[0].reset();
            $('#previousAmount').text('0.00');
            if (accountChoices) accountChoices.clearStore();
            selectedStationId = null;
        });
    </script>
@endsection