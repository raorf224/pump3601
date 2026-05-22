@extends('partials.layouts.master')

@section('title', 'Edit Close Shift | ' . Auth::user()->full_name)
@section('title-sub', 'Employee')
@section('pagetitle', 'Edit Closed Shift')

@section('css')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
	<style>
		.reset-fields-container {
			background-color: #f8f9fa;
			border-radius: 5px;
			padding: 15px;
		}

		.validation-message {
			font-size: 0.8rem;
		}

		.loading-spinner {
			display: none;
		}

		.cash-return-section {
			background-color: #f8f9fa;
			padding: 15px;
			border-radius: 5px;
			margin-top: 20px;
			border-left: 4px solid #0d6efd;
		}

		.edit-mode-badge {
			background-color: #ffc107;
			color: #000;
			padding: 5px 10px;
			border-radius: 5px;
			font-size: 0.8rem;
		}

		.field-changed {
			border-left: 3px solid #ffc107 !important;
			background-color: #fff8e7 !important;
		}

		.driver-credit-form {
			background-color: #f8f9fa;
		}

		.expense-row {
			background-color: #f8f9fa;
			border-radius: 5px;
			margin-bottom: 10px;
			padding: 10px;
		}

		.expense-card {
			border-left: 3px solid #dc3545;
		}
	</style>
@endsection

@section('content')
	<div id="layout-wrapper">
		<div class="container-fluid mt-4">
			<div class="card shadow-sm">
				<div class="card-body">
					<!-- Toast Container -->
					<div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
						<div id="toastContainer"></div>
					</div>

					<div class="d-flex justify-content-between align-items-center mb-4">
						<div>
							<h4 class="card-title mb-0">Edit Closed Shift</h4>
							<span class="edit-mode-badge mt-1 d-inline-block">
								<i class="bi bi-pencil-square me-1"></i>Edit Mode
							</span>
						</div>
						<a href="/shifts" class="btn btn-secondary">
							<i class="bi bi-arrow-left me-2"></i>Back to Shifts
						</a>
					</div>

					<form id="edit_close_shift_form">
						<input type="hidden" name="shift_id" id="close_shift_id" value="{{ request('shift_id') }}">
						<input type="hidden" name="shift_start_time" id="shift_start_time">

						<!-- SHIFT INFORMATION SECTION -->
						<div class="row mb-4">
							<div class="col-md-12">
								<div class="card">
									<div class="card-header bg-light">
										<h6 class="mb-0">Shift Information</h6>
									</div>
									<div class="card-body">
										<div class="row mb-3">
											<div class="col-md-3">
												<label class="form-label required-label">End Time</label>
												<input type="datetime-local" class="form-control" name="end_time"
													id="close_end_time" required>
											</div>
											<div class="col-md-3">
												<label class="form-label">Shift Status</label>
												<input type="text" class="form-control" value="Closed" readonly
													style="background-color: #fff3cd;">
											</div>
											<div class="col-md-3">
												<label class="form-label fw-semibold required-label">Cash Return</label>
												<input type="number" name="cash_return" id="cash_return"
													class="form-control" min="0" step="0.01" placeholder="0.00">
												<small class="text-muted">Cash returned at shift end</small>
											</div>
											<div class="col-md-3">
												<label class="form-label">&nbsp;</label>
												<div class="d-grid">
													<button type="button" class="btn btn-warning" id="save_edit_shift">
														<span class="spinner-border spinner-border-sm d-none" role="status"
															id="save_loading"></span>
														<i class="bi bi-save me-2"></i>Update Shift
													</button>
												</div>
											</div>
										</div>
										<div id="shift_info_container">
											<!-- Shift info will load here -->
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- TANK DIPS SECTION -->
						<div class="card mb-4">
							<div class="card-header bg-light d-flex justify-content-between align-items-center">
								<h6 class="mb-0">Tank Dip Readings</h6>
								<button type="button" class="btn btn-sm btn-outline-primary" id="edit_all_tanks">
									<i class="bi bi-pencil-square me-1"></i>Edit All
								</button>
							</div>
							<div class="card-body">
								<div id="tank_dips_container">
									<div class="text-center">
										<div class="spinner-border" role="status"></div>
										<p>Loading tanks...</p>
									</div>
								</div>
							</div>
						</div>

						<!-- NOZZLE READINGS SECTION -->
						<div class="card mb-4">
							<div class="card-header bg-light d-flex justify-content-between align-items-center">
								<h6 class="mb-0">Nozzle Readings</h6>
								<button type="button" class="btn btn-sm btn-outline-primary" id="edit_all_nozzles">
									<i class="bi bi-pencil-square me-1"></i>Edit All
								</button>
							</div>
							<div class="card-body">
								<div id="nozzle_readings_container">
									<div class="text-center">
										<div class="spinner-border" role="status"></div>
										<p>Loading nozzles...</p>
									</div>
								</div>
							</div>
						</div>

						<!-- ✅ EXPENSES SECTION -->
						<div class="card mb-4 expense-card">
							<div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
								<h6 class="mb-0">
									<i class="bi bi-receipt me-2"></i>Shift Expenses (Cash)
								</h6>
								<button type="button" class="btn btn-light btn-sm" id="add_expense_btn">
									<i class="bi bi-plus-circle me-1"></i> Add Expense
								</button>
							</div>
							<div class="card-body p-3" id="expenses_container">
								<div id="expense_forms_container"></div>
								<div id="no_expense_msg" class="text-center text-muted py-3">
									<i class="bi bi-info-circle me-2"></i>No expenses added yet. Click "Add Expense" to add.
								</div>
							</div>
							<div class="card-footer bg-light text-end">
								<strong>Total Expenses: <span id="total_expenses_display"
										class="text-danger">0.00</span></strong>
							</div>
						</div>


						<!-- CASH FLOW SUMMARY SECTION -->
						<div class="card mb-4">
							<div class="card-header bg-light">
								<h6 class="mb-0">Cash Flow Summary</h6>
							</div>
							<div class="card-body">
								<!-- Loading State -->
								<div id="cash_flow_loading" class="text-center">
									<div class="spinner-border" role="status"></div>
									<p>Calculating sales summary...</p>
								</div>

								<!-- Summary Table -->
								<div id="cash_flow_summary" style="display: none;">
									<div class="table-responsive">
										<table class="table table-bordered">
											<thead class="table-light">
												<tr>
													<th>Product</th>
													<th>Rate (per liter)</th>
													<th>Total Volume (Liters)</th>
													<th>Testing (LTR)</th>
													<th>Testing Expense</th>
													<th>Total Amount</th>
												</tr>
											</thead>
											<tbody id="product_summary_body">
												<!-- Product rows will be populated here -->
											</tbody>
											<tfoot class="table-dark">
												<tr>
													<td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
													<td><strong id="grand_total_amount">0.00</strong></td>
												</tr>
											</tfoot>
										</table>
									</div>

									<!-- Cash Distribution Inputs -->
									<div class="row mt-4">
										<div class="col-md-3">
											<label class="form-label required-label">Grand Total Amount</label>
											<input type="number" class="form-control" id="total_cash" readonly
												style="background-color: #f8f9fa;">
										</div>
										<div class="col-md-3">
											<label class="form-label required-label">In Hand (Cash)</label>
											<input type="number" class="form-control" id="in_hand" name="in_hand" min="0"
												required>
										</div>
										<div class="col-md-3">
											<label class="form-label required-label">In Bank</label>
											<input type="number" class="form-control" id="in_bank" name="in_bank" min="0"
												value="0">
										</div>
									</div>

									<div class="row mt-3">
										<div class="col-md-3">
											<label class="form-label">Fuel Card</label>
											<input type="number" class="form-control" id="fuel_card" name="fuel_card"
												min="0" value="0">
										</div>
										<div class="col-md-3">
											<label class="form-label">Credit Card</label>
											<input type="number" class="form-control" id="credit_card" name="credit_card"
												min="0" value="0">
										</div>
										<div class="col-md-6">
											<div class="d-flex gap-3 align-items-center pt-4">
												<div class="form-check">
													<input class="form-check-input" type="checkbox"
														id="transfer_to_bank_checkbox">
													<label class="form-check-label" for="transfer_to_bank_checkbox">
														<strong>Transfer to Bank?</strong>
													</label>
												</div>
												<div class="form-check">
													<input class="form-check-input" type="checkbox" id="fuel_card_checkbox">
													<label class="form-check-label" for="fuel_card_checkbox">
														<strong>Fuel Card</strong>
													</label>
												</div>
												<div class="form-check">
													<input class="form-check-input" type="checkbox"
														id="credit_card_checkbox">
													<label class="form-check-label" for="credit_card_checkbox">
														<strong>Credit Card</strong>
													</label>
												</div>
												<div class="form-check">
													<input class="form-check-input" type="checkbox"
														id="credit_to_driver_checkbox">
													<label class="form-check-label" for="credit_to_driver_checkbox">
														<strong>Credit to Driver?</strong>
													</label>
												</div>
											</div>
										</div>
									</div>

									<!-- BANK TRANSFER SECTION -->
									<div class="row mt-3" id="bank_transfer_section" style="display: none;">
										<div class="col-md-12">
											<div class="card border-primary">
												<div class="card-header bg-primary text-white">
													<h6 class="mb-0">Bank Transfer Details</h6>
												</div>
												<div class="card-body">
													<div class="row">
														<div class="col-md-4">
															<label class="form-label required-label">Bank Account</label>
															<select class="form-control" id="bank_account_select">
																<option value="">Select Bank Account...</option>
															</select>
														</div>
														<div class="col-md-4">
															<label class="form-label required-label">Transfer Amount</label>
															<input type="number" class="form-control" id="transfer_amount"
																min="0" step="0.01">
															<small class="text-muted">Amount to transfer to bank</small>
														</div>
														<div class="col-md-4">
															<label class="form-label">New In Bank Total</label>
															<input type="number" class="form-control" id="new_in_bank_total"
																readonly style="background-color: #f8f9fa;">
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- FUEL CARD SECTION -->
									<div class="row mt-3" id="fuelcard_section" style="display: none;">
										<div class="col-md-12">
											<div class="card border-info">
												<div class="card-header bg-info text-white">
													<h6 class="mb-0">Fuel Card Details</h6>
												</div>
												<div class="card-body">
													<div class="row">
														<div class="col-md-6">
															<label class="form-label required-label">Fuel Card
																Account</label>
															<select class="form-control" id="fuelcard_account_select">
																<option value="">Select Fuel Card Account...</option>
															</select>
														</div>
														<div class="col-md-6">
															<label class="form-label required-label">Fuel Card
																Amount</label>
															<input type="number" class="form-control" id="fuelcard_amount"
																min="0" step="0.01">
															<small class="text-muted">Amount paid via fuel card</small>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- CREDIT CARD SECTION -->
									<div class="row mt-3" id="creditcard_section" style="display: none;">
										<div class="col-md-12">
											<div class="card border-warning">
												<div class="card-header bg-warning text-white">
													<h6 class="mb-0">Credit Card Details</h6>
												</div>
												<div class="card-body">
													<div class="row">
														<div class="col-md-6">
															<label class="form-label required-label">Credit Card
																Account</label>
															<select class="form-control" id="creditcard_account_select">
																<option value="">Select Credit Card Account...</option>
															</select>
														</div>
														<div class="col-md-6">
															<label class="form-label required-label">Credit Card
																Amount</label>
															<input type="number" class="form-control" id="creditcard_amount"
																min="0" step="0.01">
															<small class="text-muted">Amount paid via credit card</small>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- CREDIT TO DRIVER SECTION -->
									<div class="row mt-3" id="credit_driver_section" style="display: none;">
										<div class="col-md-12">
											<div class="card border-success">
												<div
													class="card-header bg-success text-white d-flex justify-content-between align-items-center">
													<h6 class="mb-0">Credit to Driver Details</h6>
													<button type="button" class="btn btn-light btn-sm"
														id="add_another_driver">
														<i class="bi bi-plus-circle me-1"></i> Add Another
													</button>
												</div>
												<div class="card-body">
													<div id="credit_driver_forms_container"></div>
													<template id="credit_driver_form_template">
														<div class="driver-credit-form mb-4 p-3 border rounded">
															<div class="row">
																<div class="col-md-12 mb-3">
																	<h6 class="text-primary">Driver Credit Entry <span
																			class="form-number">#1</span>
																		<button type="button"
																			class="btn btn-danger btn-sm float-end remove-driver-form">
																			<i class="bi bi-trash"></i> Remove
																		</button>
																	</h6>
																</div>
																<div class="col-md-3">
																	<label class="form-label required-label">Station</label>
																	<select class="form-control credit-station-select"
																		required>
																		<option value="">Select Station...</option>
																	</select>
																</div>
																<div class="col-md-3">
																	<label
																		class="form-label required-label">Customer</label>
																	<select class="form-control credit-customer-select"
																		required>
																		<option value="">Select Customer...</option>
																	</select>
																</div>
																<div class="col-md-3">
																	<label class="form-label required-label">Amount Given
																		To</label>
																	<div class="mt-2">
																		<div class="form-check form-check-inline">
																			<input class="form-check-input amount-given-to"
																				type="radio"
																				name="AMOUNT_GIVEN_TO_PLACEHOLDER"
																				value="Driver" checked>
																			<label class="form-check-label">Driver</label>
																		</div>
																		<div class="form-check form-check-inline">
																			<input class="form-check-input amount-given-to"
																				type="radio"
																				name="AMOUNT_GIVEN_TO_PLACEHOLDER"
																				value="Vehicle">
																			<label class="form-check-label">Vehicle</label>
																		</div>
																	</div>
																</div>
																<div class="col-md-3">
																	<label class="form-label required-label">Amount</label>
																	<input type="number" class="form-control credit-amount"
																		step="0.01" min="0" placeholder="0.00" required>
																</div>
															</div>
															<div class="row mt-2 driver-cnic-section">
																<div class="col-md-4">
																	<label class="form-label required-label">Driver
																		CNIC</label>
																	<input type="text" class="form-control credit-cnic"
																		placeholder="Enter 13-digit CNIC" maxlength="13">
																	<small class="text-muted">13 digits without
																		dashes</small>
																</div>
															</div>
															<div class="row mt-2 vehicle-number-section"
																style="display: none;">
																<div class="col-md-4">
																	<label class="form-label required-label">Vehicle
																		Number</label>
																	<input type="text"
																		class="form-control credit-vehicle-number"
																		placeholder="Enter vehicle number">
																</div>
															</div>
														</div>
													</template>
												</div>
											</div>
										</div>
									</div>

									<!-- Validation Message -->
									<div class="row mt-3">
										<div class="col-md-12">
											<div class="alert alert-warning" id="cash_validation_msg"
												style="display: none;">
												<i class="bi bi-exclamation-triangle me-2"></i>
												<span id="validation_text"></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('js')
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script>
		const AUTH_USER_ID = "{{ Auth::id() }}";
		let originalShiftData = {};
		let changedFields = [];
		let nozzlesData = [];
		let productsMap = new Map();

		document.addEventListener("DOMContentLoaded", function () {
			const shiftId = $("#close_shift_id").val();

			if (!shiftId) {
				showToast("Shift ID not found! Please go back and try again.", "error");
				return;
			}

			// Load all shift data
			loadAllShiftData(shiftId);

			function showToast(message, type = "success") {
				const toastId = `toast-${Date.now()}`;
				const bgClass = type === "success" ? "bg-success text-white" : type === "warning" ? "bg-warning text-dark" : "bg-danger text-white";

				const toastHtml = `
									<div id="${toastId}" class="toast align-items-center ${bgClass} border-0 mb-2" role="alert">
										<div class="d-flex">
											<div class="toast-body">${message}</div>
											<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
										</div>
									</div>
								`;

				$("#toastContainer").append(toastHtml);
				const toastElement = document.getElementById(toastId);
				const bsToast = new bootstrap.Toast(toastElement, { delay: 3000 });
				bsToast.show();
				toastElement.addEventListener("hidden.bs.toast", () => $(toastElement).remove());
			}

			function trackChange(fieldName, newValue, originalValue) {
				if (newValue != originalValue) {
					if (!changedFields.includes(fieldName)) {
						changedFields.push(fieldName);
					}
					$(`#${fieldName}`).addClass('field-changed');
				} else {
					changedFields = changedFields.filter(f => f !== fieldName);
					$(`#${fieldName}`).removeClass('field-changed');
				}
			}

			function loadAllShiftData(shiftId) {
				// Load shift details
				$.ajax({
					url: `/api/shifts/${shiftId}`,
					method: "GET",
					success: function (shift) {
						originalShiftData = shift;
						$("#shift_start_time").val(shift.start_time);
						$("#close_end_time").val(shift.end_time);
						$("#cash_return").val(shift.cash_return || 0);

						originalShiftData.end_time = shift.end_time;
						originalShiftData.cash_return = shift.cash_return;

						$("#close_end_time").attr("min", shift.start_time);
						$("#close_end_time").on("change", function () {
							const start = shift.start_time;
							const end = $(this).val();
							if (new Date(end) < new Date(start)) {
								alert("End date/time cannot be earlier than start date/time");
								$(this).val(shift.end_time);
							}
							trackChange('close_end_time', end, shift.end_time);
							calculateCashFlowSummary();
						});

						$("#cash_return").on("input", function () {
							trackChange('cash_return', $(this).val(), shift.cash_return);
						});

						$("#shift_info_container").html(`
								<div class="row">
									<div class="col-md-6">
										<strong>Station:</strong> ${shift.station_name || 'N/A'}<br>
										<strong>Shift:</strong> ${shift.shift_no === 1 ? 'Day' : 'Night'}<br>
										<strong>Start Time:</strong> ${shift.start_time}
									</div>
									<div class="col-md-6">
										<strong>Shift Incharge:</strong> ${shift.shift_incharger_name || 'N/A'}<br>
										<strong>Station ID:</strong> ${shift.station_id || 'N/A'}
									</div>
								</div>
							`);

						// Load tanks and nozzles
						loadTanksAndNozzlesForEdit(shiftId, shift.station_id);
						loadCashFlowData(shiftId);

						// Load bank transfer, fuel card, and credit card data
						loadBankTransferData(shiftId, shift.station_id);
						loadFuelCardData(shiftId, shift.station_id);
						loadCreditCardData(shiftId, shift.station_id);
						loadDriverCredits(shiftId, shift.station_id);



						$(document).on('click', '.remove-expense-row', function () {
							const rowId = $(this).data('row-id');
							$(`#${rowId}`).remove();

							let index = 1;
							$(".expense-row").each(function () {
								$(this).find('.text-danger').first().html(`Expense #${index}`);
								index++;
							});

							if ($(".expense-row").length === 0) {
								$("#no_expense_msg").show();
								expenseRowCounter = 0;
							}

							calculateTotalExpenses();
							calculateCashFlowSummary();
						});

						$("#add_expense_btn").off('click').on('click', function () {
							addExpenseForm();
						});


					},
					error: function (xhr) {
						console.error("Error fetching shift details:", xhr.responseText);
						showToast("Error loading shift details!", "error");
					}
				});
			}

			function loadBankTransferData(shiftId, stationId) {
				$.ajax({
					url: `/api/edit-shift/${shiftId}/bank-transfer`,
					method: "GET",
					success: function (bankTransfer) {
						if (bankTransfer && bankTransfer.in_bank > 0) {
							// Enable bank transfer checkbox
							$("#transfer_to_bank_checkbox").prop('checked', true);
							$("#bank_transfer_section").show();

							// Set values
							$("#transfer_amount").val(bankTransfer.in_bank);
							$("#in_bank").val(bankTransfer.in_bank);
							$("#new_in_bank_total").val(bankTransfer.in_bank);

							// Load bank accounts and select the one used if baccountid exists
							if (bankTransfer.baccountid) {
								loadBankAccountsForStation(stationId, bankTransfer.baccountid);
							} else {
								loadBankAccountsForStation(stationId);
							}

							originalShiftData.bank_transfer_amount = bankTransfer.in_bank;
							originalShiftData.bank_account_id = bankTransfer.baccountid;

							// Track changes
							$("#transfer_amount").off('input').on('input', function () {
								trackChange('transfer_amount', $(this).val(), originalShiftData.bank_transfer_amount);
								const totalSales = parseFloat($("#total_cash").val()) || 0;
								autoCalculateDistribution(totalSales);
							});
						}
					},
					error: function (xhr) {
						console.log("No bank transfer found for this shift");
					}
				});
			}



			// ==================== EXPENSE SECTION FUNCTIONS (GLOBAL) ====================
			let expenseRowCounter = 0;

			function addExpenseForm() {
				expenseRowCounter++;
				const rowId = `expense_row_${expenseRowCounter}`;

				const formHtml = `
				<div class="row expense-row p-3 mb-3 bg-light rounded" id="${rowId}" style="border-left: 3px solid #dc3545;">
					<div class="col-md-12 mb-2">
						<div class="d-flex justify-content-between align-items-center">
							<strong class="text-danger">Expense #${expenseRowCounter}</strong>
							<button type="button" class="btn btn-sm btn-outline-danger remove-expense-row" data-row-id="${rowId}">
								<i class="bi bi-trash me-1"></i> Remove
							</button>
						</div>
					</div>
					<div class="col-md-6">
						<label class="form-label required-label">Amount (Rs)</label>
						<input type="number" class="form-control expense-amount" step="0.01" min="0" placeholder="0.00" required>
					</div>
					<div class="col-md-6">
						<label class="form-label">Note (Optional)</label>
						<input type="text" class="form-control expense-note" placeholder="e.g., Electricity bill, Maintenance">
					</div>
				</div>
			`;

				$("#no_expense_msg").hide();
				$("#expense_forms_container").append(formHtml);

				$(`#${rowId} .expense-amount`).on('input', function () {
					calculateTotalExpenses();
					calculateCashFlowSummary();
				});
			}

			function calculateTotalExpenses() {
				let total = 0;
				$(".expense-amount").each(function () {
					total += parseFloat($(this).val()) || 0;
				});
				$("#total_expenses_display").text(total.toFixed(2));
				return total;
			}

			$(document).on('click', '.remove-expense-row', function () {
				const rowId = $(this).data('row-id');
				$(`#${rowId}`).remove();

				let index = 1;
				$(".expense-row").each(function () {
					$(this).find('.text-danger').first().html(`Expense #${index}`);
					index++;
				});

				if ($(".expense-row").length === 0) {
					$("#no_expense_msg").show();
					expenseRowCounter = 0;
				}

				calculateTotalExpenses();
				calculateCashFlowSummary();
			});

			$("#add_expense_btn").off('click').on('click', function () {
				addExpenseForm();
			});

			function collectExpenseData() {
				const expenses = [];
				const shiftId = $("#close_shift_id").val();
				const stationId = originalShiftData.station_id;

				console.log("Collecting expenses for shift:", shiftId, "station:", stationId); // Debug

				$(".expense-row").each(function () {
					const amount = parseFloat($(this).find('.expense-amount').val()) || 0;
					const note = $(this).find('.expense-note').val() || '';

					if (amount > 0) {
						expenses.push({
							station_id: parseInt(stationId),
							shift_id: parseInt(shiftId),
							type: 'expense',
							method: 'cash',
							amount: amount,
							debit: amount,
							credit: 0,
							note: note,
							created_by: parseInt(AUTH_USER_ID)
						});
					}
				});

				console.log("Expenses collected:", expenses); // Debug
				return expenses;
			}


			function loadFuelCardData(shiftId, stationId) {
				$.ajax({
					url: `/api/edit-shift/${shiftId}/fuel-card`,
					method: "GET",
					success: function (fuelCard) {
						if (fuelCard && fuelCard.fuelcard > 0) {
							// Enable fuel card checkbox
							$("#fuel_card_checkbox").prop('checked', true);
							$("#fuelcard_section").show();

							// Set values
							$("#fuelcard_amount").val(fuelCard.fuelcard);
							$("#fuel_card").val(fuelCard.fuelcard);

							// Load fuel card accounts and select the one used
							if (fuelCard.faccountid) {
								loadFuelCardAccountsForStation(stationId, fuelCard.faccountid);
							} else {
								loadFuelCardAccountsForStation(stationId);
							}

							originalShiftData.fuel_card_amount = fuelCard.fuelcard;
							originalShiftData.fuel_card_account_id = fuelCard.faccountid;

							// Track changes
							$("#fuelcard_amount").off('input').on('input', function () {
								trackChange('fuelcard_amount', $(this).val(), originalShiftData.fuel_card_amount);
								const totalSales = parseFloat($("#total_cash").val()) || 0;
								autoCalculateDistribution(totalSales);
							});
						}
					},
					error: function (xhr) {
						console.log("No fuel card found for this shift");
					}
				});
			}

			function loadCreditCardData(shiftId, stationId) {
				$.ajax({
					url: `/api/edit-shift/${shiftId}/credit-card`,
					method: "GET",
					success: function (creditCard) {
						if (creditCard && creditCard.creditcard > 0) {
							// Enable credit card checkbox
							$("#credit_card_checkbox").prop('checked', true);
							$("#creditcard_section").show();

							// Set values
							$("#creditcard_amount").val(creditCard.creditcard);
							$("#credit_card").val(creditCard.creditcard);

							// Load credit card accounts and select the one used
							if (creditCard.caccountid) {
								loadCreditCardAccountsForStation(stationId, creditCard.caccountid);
							} else {
								loadCreditCardAccountsForStation(stationId);
							}

							originalShiftData.credit_card_amount = creditCard.creditcard;
							originalShiftData.credit_card_account_id = creditCard.caccountid;

							// Track changes
							$("#creditcard_amount").off('input').on('input', function () {
								trackChange('creditcard_amount', $(this).val(), originalShiftData.credit_card_amount);
								const totalSales = parseFloat($("#total_cash").val()) || 0;
								autoCalculateDistribution(totalSales);
							});
						}
					},
					error: function (xhr) {
						console.log("No credit card found for this shift");
					}
				});
			}
			$("#add_another_driver").on("click", function (e) {
				e.preventDefault();
				addDriverCreditForm();
			});

			// Credit to driver checkbox handler
			$("#credit_to_driver_checkbox").on("change", function () {
				const isChecked = $(this).is(":checked");

				if (isChecked) {
					$("#credit_driver_section").slideDown();
					// Add first form if no forms exist
					if ($(".driver-credit-form").length === 0) {
						addDriverCreditForm();
					}
				} else {
					$("#credit_driver_section").slideUp();
					// Clear all forms and mark existing credits for deletion
					$(".driver-credit-form").each(function () {
						const creditId = $(this).data('credit-id');
						if (creditId) {
							if (!window.creditsToDelete) {
								window.creditsToDelete = [];
							}
							window.creditsToDelete.push(creditId);
						}
					});
					$("#credit_driver_forms_container").empty();
				}

				// Recalculate cash distribution
				const totalSales = parseFloat($("#total_cash").val()) || 0;
				autoCalculateDistribution(totalSales);
			});
			function loadBankAccountsForStation(stationId, selectedAccountId = null) {
				// Use the accounts/station/{stationId}/type/bank API
				$.ajax({
					url: `/api/accounts/station/${stationId}/type/bank`,
					method: "GET",
					success: function (accounts) {
						const bankSelect = $("#bank_account_select");
						bankSelect.empty().append('<option value="">Select Bank Account...</option>');

						if (accounts && accounts.length > 0) {
							accounts.forEach(account => {
								const selected = (selectedAccountId && account.id == selectedAccountId) ? 'selected' : '';
								const displayName = `${account.name} - ${account.account_number || 'N/A'} (${account.bank_name || 'Bank'})`;
								bankSelect.append(`<option value="${account.id}" ${selected}>${displayName}</option>`);
							});
						}

						// Store selected account id for saving
						if (selectedAccountId) {
							bankSelect.data('selected-id', selectedAccountId);
						}
					},
					error: function (err) {
						console.error('Failed to load bank accounts:', err);
					}
				});
			}

			function loadFuelCardAccountsForStation(stationId, selectedAccountId = null) {
				// Use the accounts/station/{stationId}/type/fuelcard API
				$.ajax({
					url: `/api/accounts/station/${stationId}/type/fuelcard`,
					method: "GET",
					success: function (accounts) {
						const fuelCardSelect = $("#fuelcard_account_select");
						fuelCardSelect.empty().append('<option value="">Select Fuel Card Account...</option>');

						if (accounts && accounts.length > 0) {
							accounts.forEach(account => {
								const selected = (selectedAccountId && account.id == selectedAccountId) ? 'selected' : '';
								fuelCardSelect.append(`<option value="${account.id}" ${selected}>${account.name} - ${account.account_number || 'N/A'}</option>`);
							});
						}
					},
					error: function (err) {
						console.error('Failed to load fuel card accounts:', err);
					}
				});
			}

			function loadCreditCardAccountsForStation(stationId, selectedAccountId = null) {
				// Use the accounts/station/{stationId}/type/creditcard API
				$.ajax({
					url: `/api/accounts/station/${stationId}/type/creditcard`,
					method: "GET",
					success: function (accounts) {
						const creditCardSelect = $("#creditcard_account_select");
						creditCardSelect.empty().append('<option value="">Select Credit Card Account...</option>');

						if (accounts && accounts.length > 0) {
							accounts.forEach(account => {
								const selected = (selectedAccountId && account.id == selectedAccountId) ? 'selected' : '';
								creditCardSelect.append(`<option value="${account.id}" ${selected}>${account.name} - ${account.account_number || 'N/A'}</option>`);
							});
						}
					},
					error: function (err) {
						console.error('Failed to load credit card accounts:', err);
					}
				});
			}
			function loadCashFlowData(shiftId) {
				$.ajax({
					url: `/api/shift-cash-flow/shift/${shiftId}`,
					method: "GET",
					success: function (cashFlow) {
						if (cashFlow && cashFlow.length > 0) {
							const cf = cashFlow[0];
							$("#total_cash").val(cf.total_cash || 0);
							$("#in_hand").val(cf.in_hand || 0);
							$("#in_bank").val(cf.in_bank || 0);
							$("#fuel_card").val(cf.fuelcard || 0);
							$("#credit_card").val(cf.creditcard || 0);

							originalShiftData.total_cash = cf.total_cash || 0;
							originalShiftData.in_hand = cf.in_hand || 0;
							originalShiftData.in_bank = cf.in_bank || 0;
							originalShiftData.fuel_card = cf.fuelcard || 0;
							originalShiftData.credit_card = cf.creditcard || 0;

							$("#in_hand").on("input", function () {
								trackChange('in_hand', $(this).val(), originalShiftData.in_hand);
								$("#cash_return").val($(this).val());
								validateCashDistribution();
							});
							$("#in_bank").on("input", function () {
								trackChange('in_bank', $(this).val(), originalShiftData.in_bank);
								validateCashDistribution();
							});
							$("#fuel_card").on("input", function () {
								trackChange('fuel_card', $(this).val(), originalShiftData.fuel_card);
								validateCashDistribution();
							});
							$("#credit_card").on("input", function () {
								trackChange('credit_card', $(this).val(), originalShiftData.credit_card);
								validateCashDistribution();
							});
						}
					},
					error: function (xhr) {
						console.error("Error loading cash flow:", xhr.responseText);
					}
				});
			}

			function loadTanksAndNozzlesForEdit(shiftId, stationId) {
				// Load tanks with existing dip readings
				$.ajax({
					url: `/api/tanks/station/${stationId}`,
					method: "GET",
					success: function (tanks) {
						getShiftTankDips(shiftId, tanks).then(tanksWithDips => {
							renderEditTankDips(tanksWithDips);
						});
					},
					error: function (xhr) {
						console.error("Error loading tanks:", xhr.responseText);
						$("#tank_dips_container").html('<p class="text-danger">Error loading tanks</p>');
					}
				});

				// Load nozzles with existing readings
				$.ajax({
					url: `/api/nozzles/station/${stationId}`,
					method: "GET",
					success: function (nozzles) {
						nozzlesData = nozzles;
						getShiftNozzleReadings(shiftId, nozzles).then(nozzlesWithReadings => {
							renderEditNozzleReadings(nozzlesWithReadings);
							setTimeout(() => calculateCashFlowSummary(), 500);
						});
					},
					error: function (xhr) {
						console.error("Error loading nozzles:", xhr.responseText);
						$("#nozzle_readings_container").html('<p class="text-danger">Error loading nozzles</p>');
					}
				});
			}

			function getShiftTankDips(shiftId, tanks) {
				const promises = tanks.map(tank => {
					return new Promise((resolve) => {
						$.ajax({
							url: `/api/tanks-dip/shift/${shiftId}/tank/${tank.id}`,
							method: "GET",
							success: function (dip) {
								if (dip && dip.length > 0) {
									tank.shift_dip_mm = dip[0]?.dip_mm || tank.current_level_mm || 0;
									tank.shift_dip_liters = dip[0]?.dip_in_liters || tank.current_level || 0;
								} else {
									tank.shift_dip_mm = tank.current_level_mm || 0;
									tank.shift_dip_liters = tank.current_level || 0;
								}
								resolve(tank);
							},
							error: function () {
								tank.shift_dip_mm = tank.current_level_mm || 0;
								tank.shift_dip_liters = tank.current_level || 0;
								resolve(tank);
							}
						});
					});
				});
				return Promise.all(promises);
			}

			function getShiftNozzleReadings(shiftId, nozzles) {
				const promises = nozzles.map(nozzle => {
					return new Promise((resolve) => {
						$.ajax({
							url: `/api/shift-nozzle-readings/shift/${shiftId}/nozzle/${nozzle.id}`,
							method: "GET",
							success: function (reading) {
								if (reading && reading.length > 0) {
									nozzle.closing_reading = reading[0]?.closing_reading || 0;
									nozzle.opening_reading = reading[0]?.opening_reading || nozzle.intial_meter_reading || 0;
									nozzle.testing = reading[0]?.testing_reading || 0;
									nozzle.rate = reading[0]?.rate || 0;
								} else {
									nozzle.closing_reading = 0;
									nozzle.opening_reading = nozzle.intial_meter_reading || 0;
									nozzle.testing = 0;
									nozzle.rate = 0;
								}
								resolve(nozzle);
							},
							error: function () {
								nozzle.closing_reading = 0;
								nozzle.opening_reading = nozzle.intial_meter_reading || 0;
								nozzle.testing = 0;
								nozzle.rate = 0;
								resolve(nozzle);
							}
						});
					});
				});
				return Promise.all(promises);
			}

			function renderEditTankDips(tanks) {
				const container = $("#tank_dips_container");
				container.html("");

				if (!Array.isArray(tanks) || tanks.length === 0) {
					container.html('<p class="text-muted">No tanks found for this station.</p>');
					return;
				}

				tanks.forEach(tank => {
					const currentLevel = tank.current_level || 0;
					const capacity = tank.capacity || 'N/A';

					container.append(`
										<div class="row mb-3 tank-dip-row" data-tank-id="${tank.id}">
											<div class="col-md-2">
												<label class="form-label">Tank Name</label>
												<input type="text" class="form-control" value="${tank.name}" readonly>
												<small class="text-muted">Current Level: ${currentLevel} L</small>
												<small class="text-muted">Capacity: ${capacity} L</small>
											</div>
											<div class="col-md-2">
												<label class="form-label">Product</label>
												<input type="text" class="form-control" value="${tank.product_name || 'N/A'}" readonly>
											</div>
											<div class="col-md-2">
												<label class="form-label">Original Dip (mm)</label>
												<input type="number" class="form-control" value="${tank.shift_dip_mm}" readonly>
											</div>
											<div class="col-md-2">
												<label class="form-label">Original Dip (Liters)</label>
												<input type="number" class="form-control" value="${tank.shift_dip_liters}" readonly>
											</div>
											<div class="col-md-2">
												<label class="form-label required-label">New Dip (mm)</label>
												<input type="number" class="form-control edit-tank-dip-mm" 
													value="${tank.shift_dip_mm}" data-original="${tank.shift_dip_mm}" step="0.01" min="0">
											</div>
											<div class="col-md-2">
												<label class="form-label required-label">New Dip (Liters)</label>
												<input type="number" class="form-control edit-tank-dip-liters" 
													value="${tank.shift_dip_liters}" data-original="${tank.shift_dip_liters}" 
													step="0.01" min="0" max="${capacity}">
												<small class="text-warning" id="tank-warning-${tank.id}"></small>
											</div>
										</div>
									`);

					$(`.edit-tank-dip-mm[data-tank-id="${tank.id}"]`).on('input', function () {
						$(this).toggleClass('field-changed', $(this).val() != $(this).data('original'));
					});
					$(`.edit-tank-dip-liters[data-tank-id="${tank.id}"]`).on('input', function () {
						$(this).toggleClass('field-changed', $(this).val() != $(this).data('original'));
						const newDip = parseFloat($(this).val()) || 0;
						const currentLevel = parseFloat($(this).data('current-level')) || 0;
						if (newDip > currentLevel) {
							$(`#tank-warning-${tank.id}`).html(`⚠️ New dip (${newDip}L) > Current level (${currentLevel}L)`);
						} else {
							$(`#tank-warning-${tank.id}`).html('');
						}
					});
				});
			}

			function renderEditNozzleReadings(nozzles) {
				const container = $("#nozzle_readings_container");
				container.html("");

				if (!Array.isArray(nozzles) || nozzles.length === 0) {
					container.html('<p class="text-muted">No nozzles found for this station.</p>');
					return;
				}

				// Group nozzles by product for rate fetching
				const productsMap = new Map();
				nozzles.forEach(nozzle => {
					if (!productsMap.has(nozzle.product_id)) {
						productsMap.set(nozzle.product_id, {
							product_id: nozzle.product_id,
							product_name: nozzle.product_name,
							nozzles: []
						});
					}
					productsMap.get(nozzle.product_id).nozzles.push(nozzle);
				});

				// Fetch rates for each product
				const shiftStartTime = $("#shift_start_time").val();
				const stationId = originalShiftData.station_id;

				productsMap.forEach((product, productId) => {
					$.ajax({
						url: `/api/product-price/${stationId}/${productId}/${shiftStartTime}`,
						method: "GET",
						success: function (priceData) {
							const rate = parseFloat(priceData?.price) || 0;
							product.rate = rate;

							// Render nozzles for this product
							product.nozzles.forEach(nozzle => {
								nozzle.rate = rate;
								renderNozzleRow(nozzle);
							});

							// After all nozzles rendered, calculate cash flow
							setTimeout(() => calculateCashFlowSummary(), 300);
						},
						error: function () {
							product.nozzles.forEach(nozzle => {
								nozzle.rate = 0;
								renderNozzleRow(nozzle);
							});
						}
					});
				});

				function renderNozzleRow(nozzle) {
					const totalDispensed = (nozzle.closing_reading || 0) - (nozzle.opening_reading || 0);

					container.append(`
										<div class="row mb-3 nozzle-reading-row" data-nozzle-id="${nozzle.id}" data-product-id="${nozzle.product_id}">
											<div class="col-md-2">
												<label class="form-label">Nozzle Name</label>
												<input type="text" class="form-control" value="${nozzle.name}" readonly>
												<small class="text-muted">Dispenser: ${nozzle.dispenser_name || 'N/A'}</small>
											</div>
											<div class="col-md-2">
												<label class="form-label">Product</label>
												<input type="text" class="form-control" value="${nozzle.product_name || 'N/A'}" readonly>
												<small class="text-muted">Tank: ${nozzle.tank_name || 'N/A'}</small>
											</div>
											<div class="col-md-1">
												<label class="form-label">Testing(LTR)</label>
												<input type="number" class="form-control edit-nozzle-testing"
													value="${nozzle.testing || 0}" data-original="${nozzle.testing || 0}" step="0.01" min="0">
											</div>
											<div class="col-md-2">
												<label class="form-label">Opening Reading</label>
												<input type="number" class="form-control nozzle-opening" value="${nozzle.opening_reading}" readonly>
											</div>
											<div class="col-md-1">
												<label class="form-label">Original Closing</label>
												<input type="number" class="form-control" value="${nozzle.closing_reading || 0}" readonly>
											</div>
											<div class="col-md-2">
												<label class="form-label required-label">New Closing</label>
												<input type="number" class="form-control edit-nozzle-closing" 
													value="${nozzle.closing_reading || 0}" data-original="${nozzle.closing_reading || 0}" 
													data-opening="${nozzle.opening_reading}" step="0.01" min="0">
												<small class="text-muted validation-message" style="display:none; color: red;"></small>
											</div>
											<div class="col-md-1">
												<label class="form-label">Total Dispensed</label>
												<input type="number" class="form-control nozzle-total" value="${totalDispensed.toFixed(2)}" readonly>
											</div>
											<div class="col-md-1">
												<label class="form-label">Rate</label>
												<input type="number" class="form-control product-rate" value="${nozzle.rate}" readonly>
											</div>
										</div>
									`);

					// Add event listeners for this nozzle
					const row = container.children().last();
					row.find('.edit-nozzle-closing').on('input', function () {
						$(this).toggleClass('field-changed', $(this).val() != $(this).data('original'));
						const opening = parseFloat($(this).data('opening')) || 0;
						const closing = parseFloat($(this).val()) || 0;
						const validationMsg = $(this).closest('.nozzle-reading-row').find('.validation-message');

						if (closing < opening) {
							validationMsg.text('Closing reading cannot be less than opening reading').show();
							row.find('.nozzle-total').val('');
							$(this).addClass('is-invalid');
						} else {
							validationMsg.hide();
							const totalDispensed = closing - opening;
							row.find('.nozzle-total').val(totalDispensed.toFixed(2));
							$(this).removeClass('is-invalid');
						}
						calculateCashFlowSummary();
					});

					row.find('.edit-nozzle-testing').on('input', function () {
						$(this).toggleClass('field-changed', $(this).val() != $(this).data('original'));
						calculateCashFlowSummary();
					});
				}
			}

			function calculateCashFlowSummary() {
				const shiftId = $("#close_shift_id").val();
				const shiftStartTime = $("#shift_start_time").val();
				const stationId = originalShiftData.station_id;
				const cashHandover = parseFloat(originalShiftData.cash_handover) || 0;
				const manualExpenses = calculateTotalExpenses();


				if (!shiftId || !shiftStartTime || !stationId) {
					$("#cash_flow_loading").html('<p class="text-muted">Shift data not loaded yet</p>');
					return;
				}

				// Group nozzles by product
				const productGroups = new Map();

				$(".nozzle-reading-row").each(function () {
					const productId = $(this).data('product-id');
					const productName = $(this).find('input[type="text"]').eq(1).val();
					const rate = parseFloat($(this).find('.product-rate').val()) || 0;
					const opening = parseFloat($(this).find('.nozzle-opening').val()) || 0;
					const closing = parseFloat($(this).find('.edit-nozzle-closing').val()) || 0;
					const testing = parseFloat($(this).find('.edit-nozzle-testing').val()) || 0;

					if (!productGroups.has(productId)) {
						productGroups.set(productId, {
							product_id: productId,
							product_name: productName,
							rate: rate,
							total_volume: 0,
							testing_liters: 0,
							testing_amount: 0,
							nozzle_amount: 0
						});
					}

					const product = productGroups.get(productId);

					if (closing >= opening) {
						const volume = closing - opening;
						product.total_volume += volume;
						product.testing_liters += testing;
						product.testing_amount += testing * rate;
						product.nozzle_amount += (volume * rate);
					}
				});

				// Fetch additional data for cash flow calculation
				Promise.all([
					calculateLubricantsCashTransactions(shiftId),
					calculateOilPurchases(shiftId),
					calculateShortagesCash(shiftId),
					getCashTransactions(shiftId)
				]).then(([lubricantsData, oilPurchaseData, shortagesData, transactionsData]) => {

					let cashCredits = transactionsData.credits;
					let cashDebits = transactionsData.debits;
					let netCashTransactions = cashCredits - cashDebits;

					let totalNozzleSales = 0;
					let totalTestingAmount = 0;
					let totalTestingLiters = 0;

					const productSummaries = Array.from(productGroups.values());

					productSummaries.forEach(product => {
						totalNozzleSales += product.nozzle_amount;
						totalTestingAmount += product.testing_amount;
						totalTestingLiters += product.testing_liters;
					});

					const grandTotal = cashHandover + totalNozzleSales + netCashTransactions +
						lubricantsData.total - oilPurchaseData.total + shortagesData.total;

					renderCashFlowSummary(productSummaries, cashCredits, cashDebits, netCashTransactions,
						cashHandover, totalNozzleSales, lubricantsData, oilPurchaseData,
						shortagesData, grandTotal, totalTestingAmount, totalTestingLiters, manualExpenses);
				}).catch(error => {
					console.error("Error in cash flow calculation:", error);
					$("#cash_flow_loading").html('<p class="text-danger">Error calculating cash flow</p>');
				});
			}

			function calculateLubricantsCashTransactions(shiftId) {
				return new Promise((resolve) => {
					$.ajax({
						url: `/api/lubes/shift/${shiftId}`,
						method: "GET",
						success: function (lubesData) {
							if (!lubesData || lubesData.length === 0) {
								resolve({ total: 0, purchases: 0, sales: 0, count: 0, cash: 0, bank: 0 });
								return;
							}

							let lubricantsTotal = 0;
							let cashAmount = 0;

							lubesData.forEach(doc => {
								const paymentMethod = doc.payment_method || 'cash';
								const actualCashAmount = parseFloat(doc.payment_amount) || 0;

								if (paymentMethod === 'cash' || paymentMethod === 'Cash') {
									if (doc.doc_type === 'purchase') {
										lubricantsTotal -= actualCashAmount;
										cashAmount -= actualCashAmount;
									} else if (doc.doc_type === 'sale') {
										lubricantsTotal += actualCashAmount;
										cashAmount += actualCashAmount;
									}
								}
							});

							resolve({ total: lubricantsTotal, cash: cashAmount });
						},
						error: function () {
							resolve({ total: 0, cash: 0 });
						}
					});
				});
			}

			function calculateOilPurchases(shiftId) {
				return new Promise((resolve) => {
					$.ajax({
						url: `/api/oil-purchases/shift/${shiftId}`,
						method: "GET",
						success: function (oilPurchases) {
							if (!oilPurchases || oilPurchases.length === 0) {
								resolve({ total: 0, cash: 0 });
								return;
							}

							let oilTotal = 0;
							let cashAmount = 0;

							oilPurchases.forEach(purchase => {
								const totalCashPaid = parseFloat(purchase.total_cash_paid) || 0;
								const hasCashPayment = purchase.has_cash_payment === 'cash';

								if (hasCashPayment && totalCashPaid > 0) {
									oilTotal += totalCashPaid;
									cashAmount += totalCashPaid;
								}
							});

							resolve({ total: oilTotal, cash: cashAmount });
						},
						error: function () {
							resolve({ total: 0, cash: 0 });
						}
					});
				});
			}

			function calculateShortagesCash(shiftId) {
				return new Promise((resolve) => {
					$.ajax({
						url: `/api/shortages/shift/${shiftId}`,
						method: "GET",
						success: function (response) {
							if (!response.success || !response.payments || response.payments.length === 0) {
								resolve({ total: 0, cashTotal: 0 });
								return;
							}

							let totalShortageCash = 0;

							response.payments.forEach(payment => {
								if (payment.payment_method === 'cash') {
									totalShortageCash += parseFloat(payment.total_amount) || 0;
								}
							});

							resolve({ total: totalShortageCash, cashTotal: totalShortageCash });
						},
						error: function () {
							resolve({ total: 0, cashTotal: 0 });
						}
					});
				});
			}

			function getCashTransactions(shiftId) {
				return new Promise((resolve) => {
					$.ajax({
						url: `/api/transactions/shift/${shiftId}`,
						method: "GET",
						success: function (transactions) {
							let credits = 0;
							let debits = 0;

							if (Array.isArray(transactions)) {
								transactions.forEach(t => {
									if (t.method === 'cash') {
										credits += parseFloat(t.credit) || 0;
										debits += parseFloat(t.debit) || 0;
									}
								});
							}

							// ✅ Add manual expenses to debits
							const manualExpenses = calculateTotalExpenses();
							debits += manualExpenses;

							resolve({ credits, debits });
						},
						error: function () {
							resolve({ credits: 0, debits: 0 });
						}
					});
				});
			}


			function renderCashFlowSummary(productSummaries, cashCredits, cashDebits, netCashTransactions,
				cashHandover, totalNozzleSales, lubricantsData, oilPurchaseData,
				shortagesData, grandTotal, totalTestingAmount, totalTestingLiters, manualExpenses) {
				const container = $("#product_summary_body");
				container.empty();

				let hasSales = false;

				// Product Rows
				productSummaries.forEach(product => {
					if (product.nozzle_amount > 0 || product.total_volume > 0) {
						hasSales = true;
						container.append(`
											<tr>
												<td>${product.product_name}</td>
												<td>${product.rate.toFixed(2)}</td>
												<td>${product.total_volume.toFixed(2)} L</td>
												<td>${product.testing_liters.toFixed(2)}</td>
												<td>${product.testing_amount.toFixed(2)}</td>
												<td>${product.nozzle_amount.toFixed(2)}</td>
											</tr>
										`);
					}
				});

				if (!hasSales) {
					container.append(`
										<tr>
											<td colspan="6" class="text-center text-muted">
												<i class="bi bi-info-circle me-2"></i>
												No nozzle sales recorded (fill nozzle closing readings)
												\n
											</td>
										</tr>
									`);
				}

				// Summary Section
				let rowNum = 1;
				container.append(`
									<tr class="table-primary">
										<td colspan="5" class="text-end"><strong>${rowNum++}. Opening Cash Handover:</strong></td>
										<td><strong>${cashHandover.toFixed(2)}</strong></td>
									</tr>
									<tr class="table-secondary">
										<td colspan="5" class="text-end"><strong>${rowNum++}. Total Nozzle Sales:</strong></td>
										<td><strong>${totalNozzleSales.toFixed(2)}</strong></td>
									</tr>
								`);

				if (lubricantsData && lubricantsData.total !== 0) {
					container.append(`
										<tr class="table-info">
											<td colspan="5" class="text-end"><strong>${rowNum++}. Lubricants Cash:</strong></td>
											<td><strong>${lubricantsData.total.toFixed(2)}</strong></td>
										</tr>
									`);
				}

				if (oilPurchaseData && oilPurchaseData.total !== 0) {
					container.append(`
										<tr class="table-danger">
											<td colspan="5" class="text-end"><strong>${rowNum++}. Oil Purchases:</strong></td>
											<td><strong>-${oilPurchaseData.total.toFixed(2)}</strong></td>
										</tr>
									`);
				}

				if (shortagesData && shortagesData.total > 0) {
					container.append(`
										<tr class="table-success">
											<td colspan="5" class="text-end"><strong>${rowNum++}. Shortages Cash Received:</strong></td>
											<td><strong>+${shortagesData.total.toFixed(2)}</strong></td>
										</tr>
									`);
				}

				// ✅ ADD MANUAL EXPENSES ROW
				if (manualExpenses > 0) {
					container.append(`
										<tr class="table-danger">
											<td colspan="5" class="text-end"><strong>${rowNum++}. Manual Expenses (Cash):</strong></td>
											<td><strong class="text-danger">-${manualExpenses.toFixed(2)}</strong></td>
										</tr>
									`);
				}

				if (cashCredits > 0 || cashDebits > 0) {
					if (cashCredits > 0) {
						container.append(`
											<tr class="table-success">
												<td colspan="5" class="text-end"><strong>${rowNum++}. ➕ Cash Income:</strong></td>
												<td><strong>${cashCredits.toFixed(2)}</strong></td>
											</tr>
										`);
					}
					if (cashDebits > 0) {
						container.append(`
											<tr class="table-danger">
												<td colspan="5" class="text-end"><strong>${rowNum++}. ➖ Cash Expenses:</strong></td>
												<td><strong>-${cashDebits.toFixed(2)}</strong></td>
											</tr>
										`);
					}
					container.append(`
										<tr class="table-warning">
											<td colspan="5" class="text-end"><strong>${rowNum++}. Net Cash Transactions:</strong></td>
											<td><strong>${netCashTransactions.toFixed(2)}</strong></td>
										</tr>
									`);
				}

				container.append(`
									<tr class="table-dark">
										<td colspan="5" class="text-end"><strong>💵 TOTAL AVAILABLE CASH:</strong></td>
										<td><strong>${grandTotal.toFixed(2)}</strong></td>
									</tr>
								`);

				$("#grand_total_amount").text(grandTotal.toFixed(2));
				$("#cash_flow_loading").hide();
				$("#cash_flow_summary").show();
				$('#total_cash').val(grandTotal.toFixed(2));

				autoCalculateDistribution(grandTotal);
			}


			function autoCalculateDistribution(totalSales) {

				const isBankTransfer = $("#transfer_to_bank_checkbox").is(":checked");
				const transferAmount = parseFloat($("#transfer_amount").val()) || 0;
				const fuelCardAmount = parseFloat($("#fuelcard_amount").val()) || 0;
				const creditCardAmount = parseFloat($("#creditcard_amount").val()) || 0;

				let driverCreditAmount = 0;
				if ($("#credit_to_driver_checkbox").is(":checked")) {
					$(".driver-credit-form").each(function () {
						driverCreditAmount += parseFloat($(this).find('.credit-amount').val()) || 0;
					});
				}

				const totalPayments = transferAmount + fuelCardAmount + creditCardAmount + driverCreditAmount;
				const inHand = totalSales - totalPayments;

				if (inHand < 0) {
					showToast(`Total payments (${totalPayments.toFixed(2)}) exceed total sales (${totalSales.toFixed(2)})!`, "error");
					$("#in_hand").val(0);
					return;
				}

				$("#in_bank").val(transferAmount.toFixed(2));
				$("#in_hand").val(inHand.toFixed(2));
				$("#cash_return").val(inHand.toFixed(2));
				$("#fuel_card").val(fuelCardAmount.toFixed(2));
				$("#credit_card").val(creditCardAmount.toFixed(2));

				if (isBankTransfer) {
					$("#new_in_bank_total").val(transferAmount.toFixed(2));
				}

				validateCashDistribution(totalSales, inHand, transferAmount, fuelCardAmount, creditCardAmount, driverCreditAmount);
			}

			function validateCashDistribution(totalSales, inHand, inBank, fuelCardAmount, creditCardAmount, driverCreditAmount) {
				const validationMsg = $("#cash_validation_msg");
				const validationText = $("#validation_text");

				const distributed = inHand + inBank + fuelCardAmount + creditCardAmount + driverCreditAmount;
				const difference = Math.abs(distributed - totalSales);

				if (difference > 0.01) {
					validationText.html(`
										⚠️ <strong>Cash Mismatch!</strong><br>
										Total Sales: <strong>${totalSales.toFixed(2)}</strong><br>
										In Hand: <strong>${inHand.toFixed(2)}</strong><br>
										In Bank: <strong>${inBank.toFixed(2)}</strong><br>
										Fuel Card: <strong>${fuelCardAmount.toFixed(2)}</strong><br>
										Credit Card: <strong>${creditCardAmount.toFixed(2)}</strong><br>
										Driver Credit: <strong>${driverCreditAmount.toFixed(2)}</strong><br>
										Total Distributed: <strong>${distributed.toFixed(2)}</strong><br>
										Difference: <strong class="text-danger">${difference.toFixed(2)}</strong>
									`);
					validationMsg.show().removeClass("alert-success").addClass("alert-warning");
					return false;
				} else {
					validationText.html(`
										✅ <strong>Perfect Match!</strong><br>
										Total Sales: <strong>${totalSales.toFixed(2)}</strong><br>
										In Hand: <strong>${inHand.toFixed(2)}</strong><br>
										In Bank: <strong>${inBank.toFixed(2)}</strong><br>
										Fuel Card: <strong>${fuelCardAmount.toFixed(2)}</strong><br>
										Credit Card: <strong>${creditCardAmount.toFixed(2)}</strong><br>
										Driver Credit: <strong>${driverCreditAmount.toFixed(2)}</strong>
									`);
					validationMsg.show().removeClass("alert-warning").addClass("alert-success");
					return true;
				}
			}

			// Event Listeners for distribution
			$("#transfer_to_bank_checkbox").on("change", function () {
				const isChecked = $(this).is(":checked");
				if (isChecked) {
					$("#bank_transfer_section").slideDown();
				} else {
					$("#bank_transfer_section").slideUp();
					$("#transfer_amount").val("");
				}
				const totalSales = parseFloat($("#total_cash").val()) || 0;
				autoCalculateDistribution(totalSales);
			});

			$("#fuel_card_checkbox").on("change", function () {
				const isChecked = $(this).is(":checked");
				if (isChecked) {
					$("#fuelcard_section").slideDown();
				} else {
					$("#fuelcard_section").slideUp();
					$("#fuelcard_amount").val("");
				}
				const totalSales = parseFloat($("#total_cash").val()) || 0;
				autoCalculateDistribution(totalSales);
			});

			$("#credit_card_checkbox").on("change", function () {
				const isChecked = $(this).is(":checked");
				if (isChecked) {
					$("#creditcard_section").slideDown();
				} else {
					$("#creditcard_section").slideUp();
					$("#creditcard_amount").val("");
				}
				const totalSales = parseFloat($("#total_cash").val()) || 0;
				autoCalculateDistribution(totalSales);
			});

			$("#credit_to_driver_checkbox").on("change", function () {
				const isChecked = $(this).is(":checked");
				if (isChecked) {
					$("#credit_driver_section").slideDown();
					if ($(".driver-credit-form").length === 0) {
						addDriverCreditForm();
						loadStationsForCreditDriver();
					}
				} else {
					$("#credit_driver_section").slideUp();
				}
				const totalSales = parseFloat($("#total_cash").val()) || 0;
				autoCalculateDistribution(totalSales);
			});

			$("#transfer_amount, #fuelcard_amount, #creditcard_amount").on("input", function () {
				const totalSales = parseFloat($("#total_cash").val()) || 0;
				autoCalculateDistribution(totalSales);
			});

			$(document).on("input", ".credit-amount", function () {
				const totalSales = parseFloat($("#total_cash").val()) || 0;
				autoCalculateDistribution(totalSales);
			});

			function addDriverCreditForm() {
				const formNumber = $(".driver-credit-form").length + 1;
				const uniqueRadioName = `amount_given_to_${formNumber}`;

				const formHtml = `
									<div class="driver-credit-form mb-4 p-3 border rounded">
										<div class="row">
											<div class="col-md-12 mb-3">
												<h6 class="text-primary">Driver Credit Entry <span class="form-number">#${formNumber}</span>
													<button type="button" class="btn btn-danger btn-sm float-end remove-driver-form">
														<i class="bi bi-trash"></i> Remove
													</button>
												</h6>
											</div>
											<div class="col-md-3">
												<label class="form-label required-label">Station</label>
												<select class="form-control credit-station-select" required>
													<option value="">Select Station...</option>
												</select>
											</div>
											<div class="col-md-3">
												<label class="form-label required-label">Customer</label>
												<select class="form-control credit-customer-select" required>
													<option value="">Select Customer...</option>
												</select>
											</div>
											<div class="col-md-3">
												<label class="form-label required-label">Amount Given To</label>
												<div class="mt-2">
													<div class="form-check form-check-inline">
														<input class="form-check-input amount-given-to" type="radio" 
															name="${uniqueRadioName}" value="Driver" checked>
														<label class="form-check-label">Driver</label>
													</div>
													<div class="form-check form-check-inline">
														<input class="form-check-input amount-given-to" type="radio" 
															name="${uniqueRadioName}" value="Vehicle">
														<label class="form-check-label">Vehicle</label>
													</div>
												</div>
											</div>
											<div class="col-md-3">
												<label class="form-label required-label">Amount</label>
												<input type="number" class="form-control credit-amount" step="0.01" min="0" placeholder="0.00" required>
											</div>
										</div>
										<div class="row mt-2 driver-cnic-section">
											<div class="col-md-4">
												<label class="form-label required-label">Driver CNIC</label>
												<input type="text" class="form-control credit-cnic" placeholder="Enter 13-digit CNIC" maxlength="13">
												<small class="text-muted">13 digits without dashes</small>
											</div>
										</div>
										<div class="row mt-2 vehicle-number-section" style="display: none;">
											<div class="col-md-4">
												<label class="form-label required-label">Vehicle Number</label>
												<input type="text" class="form-control credit-vehicle-number" placeholder="Enter vehicle number">
											</div>
										</div>
									</div>
								`;

				$("#credit_driver_forms_container").append(formHtml);
				const newForm = $("#credit_driver_forms_container .driver-credit-form").last();

				newForm.find('.amount-given-to').on('change', function () {
					const form = $(this).closest('.driver-credit-form');
					if ($(this).val() === 'Vehicle') {
						form.find('.vehicle-number-section').show();
						form.find('.driver-cnic-section').hide();
					} else {
						form.find('.driver-cnic-section').show();
						form.find('.vehicle-number-section').hide();
					}
				});

				loadStationsForNewForm(newForm);
				return newForm;
			}

			function loadStationsForCreditDriver() {
				const shiftId = $("#close_shift_id").val();

				$.ajax({
					url: `/api/shifts/${shiftId}`,
					method: "GET",
					success: function (shift) {
						const currentStationId = shift.station_id;

						$.ajax({
							url: `/api/stations/${AUTH_USER_ID}`,
							method: "GET",
							success: function (stations) {
								$(".credit-station-select").each(function () {
									const $select = $(this);
									$select.empty().append('<option value="">Select Station...</option>');
									stations.forEach(station => {
										const selected = (station.id == currentStationId) ? 'selected' : '';
										$select.append(`<option value="${station.id}" ${selected}>${station.name}</option>`);
									});
								});
							}
						});
					}
				});
			}

			function loadStationsForNewForm(formElement) {
				const shiftId = $("#close_shift_id").val();

				$.ajax({
					url: `/api/shifts/${shiftId}`,
					method: "GET",
					success: function (shift) {
						const currentStationId = shift.station_id;

						$.ajax({
							url: `/api/stations/${AUTH_USER_ID}`,
							method: "GET",
							success: function (stations) {
								const $select = formElement.find('.credit-station-select');
								$select.empty().append('<option value="">Select Station...</option>');
								stations.forEach(station => {
									const selected = (station.id == currentStationId) ? 'selected' : '';
									$select.append(`<option value="${station.id}" ${selected}>${station.name}</option>`);
								});

								$select.on('change', function () {
									const stationId = $(this).val();
									const form = $(this).closest('.driver-credit-form');
									if (stationId) {
										$.ajax({
											url: `/api/accounts/category/customer`,
											method: "GET",
											success: function (customers) {
												const stationCustomers = customers.filter(c => c.station_id == stationId);
												const $customerSelect = form.find('.credit-customer-select');
												$customerSelect.empty().append('<option value="">Select Customer...</option>');
												stationCustomers.forEach(customer => {
													$customerSelect.append(`<option value="${customer.id}">${customer.name} - ${customer.phone}</option>`);
												});
											}
										});
									}
								});
							}
						});
					}
				});
			}

			$(document).on("click", ".remove-driver-form", function (e) {
				e.preventDefault();
				if ($(".driver-credit-form").length > 1) {
					$(this).closest('.driver-credit-form').remove();
					const totalSales = parseFloat($("#total_cash").val()) || 0;
					autoCalculateDistribution(totalSales);
				} else {
					showToast("At least one driver credit entry is required", "warning");
				}
			});

			$("#add_another_driver").on("click", function (e) {
				e.preventDefault();
				addDriverCreditForm();
				loadStationsForCreditDriver();
			});

			$("#edit_all_tanks").on("click", function () {
				$(".edit-tank-dip-mm, .edit-tank-dip-liters").addClass('field-changed');
				showToast("All tank fields are now editable", "warning");
			});

			$("#edit_all_nozzles").on("click", function () {
				$(".edit-nozzle-closing, .edit-nozzle-testing").addClass('field-changed');
				showToast("All nozzle fields are now editable", "warning");
			});


			// Save Edit Shift
			$("#save_edit_shift").on("click", function () {
				const shiftId = $("#close_shift_id").val();
				const saveBtn = $(this);

				saveBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Updating...');
				saveBtn.prop('disabled', true);

				// Collect updated data
				const updateData = {
					shift_data: {
						end_time: $("#close_end_time").val(),
						status: "closed",
						cash_return: parseFloat($("#cash_return").val()) || 0
					},
					cash_flow: {
						total_cash: parseFloat($("#total_cash").val()) || 0,
						in_hand: parseFloat($("#in_hand").val()) || 0,
						in_bank: parseFloat($("#in_bank").val()) || 0,
						fuel_card: parseFloat($("#fuel_card").val()) || 0,
						credit_card: parseFloat($("#credit_card").val()) || 0,
						from_date: $("#shift_start_time").val(),
						to_date: $("#close_end_time").val(),
						// Account IDs for fuel card, credit card, and bank
						faccountid: null,
						caccountid: null,
						baccountid: null
					},
					tank_updates: [],
					nozzle_updates: [],
					driver_credit_updates: [],
					driver_credit_deletes: [],
					expenses: []  // ✅ expenses array pehle se include karo

				};

				// Set account IDs if sections are enabled
				if ($("#fuel_card_checkbox").is(":checked")) {
					updateData.cash_flow.faccountid = $("#fuelcard_account_select").val() || null;
					updateData.cash_flow.fuel_card = parseFloat($("#fuelcard_amount").val()) || 0;
				}

				if ($("#credit_card_checkbox").is(":checked")) {
					updateData.cash_flow.caccountid = $("#creditcard_account_select").val() || null;
					updateData.cash_flow.credit_card = parseFloat($("#creditcard_amount").val()) || 0;
				}

				if ($("#transfer_to_bank_checkbox").is(":checked")) {
					updateData.cash_flow.baccountid = $("#bank_account_select").val() || null;
					updateData.cash_flow.in_bank = parseFloat($("#transfer_amount").val()) || 0;
				}

				// Collect tank updates
				$(".tank-dip-row").each(function () {
					const newDipMm = $(this).find('.edit-tank-dip-mm').val();
					const newDipLiters = $(this).find('.edit-tank-dip-liters').val();
					const origDipMm = $(this).find('.edit-tank-dip-mm').data('original');
					const origDipLiters = $(this).find('.edit-tank-dip-liters').data('original');

					if (newDipMm != origDipMm || newDipLiters != origDipLiters) {
						updateData.tank_updates.push({
							tank_id: parseInt($(this).data('tank-id')),
							dip_mm: parseFloat(newDipMm),
							dip_in_liters: parseFloat(newDipLiters)
						});
					}
				});

				// Collect nozzle updates
				$(".nozzle-reading-row").each(function () {
					const newClosing = $(this).find('.edit-nozzle-closing').val();
					const newTesting = $(this).find('.edit-nozzle-testing').val();
					const origClosing = $(this).find('.edit-nozzle-closing').data('original');
					const origTesting = $(this).find('.edit-nozzle-testing').data('original');

					if (newClosing != origClosing || newTesting != origTesting) {
						updateData.nozzle_updates.push({
							nozzle_id: parseInt($(this).data('nozzle-id')),
							closing_reading: parseFloat(newClosing),
							testing: parseFloat(newTesting) || 0,
							rate: parseFloat($(this).find('.product-rate').val()) || 0,
							opening_reading: parseFloat($(this).find('.nozzle-opening').val()) || 0
						});
					}
				});

				// Collect driver credit updates
				const driverCreditUpdates = [];
				const driverCreditDeletes = window.creditsToDelete || [];

				$(".driver-credit-form").each(function () {
					const creditId = $(this).data('credit-id');
					const stationId = $(this).find('.credit-station-select').val();
					const accountId = $(this).find('.credit-customer-select').val();
					const amountGivenTo = $(this).find('.amount-given-to:checked').val();
					const amount = parseFloat($(this).find('.credit-amount').val()) || 0;
					const cnic = $(this).find('.credit-cnic').val();
					const vehicleNumber = $(this).find('.credit-vehicle-number').val();

					// Only include if amount is greater than 0
					if (amount > 0) {
						if (creditId) {
							// Update existing - check if values changed
							const originalAmount = $(this).data('original-amount');
							if (amount != originalAmount) {
								driverCreditUpdates.push({
									id: parseInt(creditId),
									station_id: parseInt(stationId),
									account_id: parseInt(accountId),
									amount_given_to: amountGivenTo,
									amount: amount,
									cnic: cnic || null,
									vehicle_number: vehicleNumber || null
								});
							}
						} else {
							// Insert new
							driverCreditUpdates.push({
								station_id: parseInt(stationId),
								account_id: parseInt(accountId),
								amount_given_to: amountGivenTo,
								amount: amount,
								cnic: cnic || null,
								vehicle_number: vehicleNumber || null,
								created_by: parseInt(AUTH_USER_ID)
							});
						}
					}
				});

				// Add to updateData
				updateData.driver_credit_updates = driverCreditUpdates;
				updateData.driver_credit_deletes = driverCreditDeletes;

				// ✅ STEP 2: 
				const expenses = collectExpenseData();
				if (expenses.length > 0) {
					updateData.expenses = expenses;
					console.log("Adding expenses to updateData:", expenses);
				}
				$.ajax({
					url: `/api/edit-shift/${shiftId}/update`,
					method: "PUT",
					contentType: "application/json",
					data: JSON.stringify(updateData),
					success: function (response) {
						saveBtn.html('<i class="bi bi-save me-2"></i>Update Shift').prop('disabled', false);
						if (response.success) {
							showToast("Shift updated successfully!", "success");
							setTimeout(() => {
								// window.location.href = "/shifts";
							}, 2000);
						} else {
							showToast(response.message || "Error updating shift!", "error");
						}
					},
					error: function (xhr) {
						console.error("Error updating shift:", xhr.responseText);
						saveBtn.html('<i class="bi bi-save me-2"></i>Update Shift').prop('disabled', false);
						showToast(xhr.responseJSON?.message || "Error updating shift!", "error");
					}
				});

			});
		});

		// Load driver credits for shift
		function loadDriverCredits(shiftId, stationId) {
			$.ajax({
				url: `/api/credit-driver/shift/${shiftId}`,
				method: "GET",
				success: function (credits) {
					if (credits && credits.length > 0) {
						// Enable credit to driver checkbox
						$("#credit_to_driver_checkbox").prop('checked', true);
						$("#credit_driver_section").show();

						// Clear container
						$("#credit_driver_forms_container").empty();

						// Reset form counter
						window.driverFormCounter = 1;

						// Load existing credits
						credits.forEach((credit, index) => {
							addDriverCreditFormWithData(credit, index + 1, stationId);
						});

						// Store original credit IDs for deletion tracking
						originalShiftData.driver_credit_ids = credits.map(c => c.id);
					}
				},
				error: function (xhr) {
					console.log("No driver credits found for this shift");
				}
			});
		}

		// Add driver credit form with existing data
		function addDriverCreditFormWithData(data, formNumber, stationId) {
			const uniqueRadioName = `amount_given_to_${formNumber}`;
			const isVehicle = data.amount_given_to === 'Vehicle';

			const formHtml = `
						<div class="driver-credit-form mb-4 p-3 border rounded" data-credit-id="${data.id || ''}" data-original-amount="${data.amount}">
							<div class="row">
								<div class="col-md-12 mb-3">
									<h6 class="text-primary">Driver Credit Entry <span class="form-number">#${formNumber}</span>
										<button type="button" class="btn btn-danger btn-sm float-end remove-driver-form">
											<i class="bi bi-trash"></i> Remove
										</button>
									</h6>
								</div>
								<div class="col-md-3">
									<label class="form-label required-label">Station</label>
									<select class="form-control credit-station-select" required data-original="${data.station_id}">
										<option value="">Select Station...</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label required-label">Customer</label>
									<select class="form-control credit-customer-select" required data-original="${data.account_id}">
										<option value="">Select Customer...</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label required-label">Amount Given To</label>
									<div class="mt-2">
										<div class="form-check form-check-inline">
											<input class="form-check-input amount-given-to" type="radio" 
												name="${uniqueRadioName}" value="Driver" ${!isVehicle ? 'checked' : ''}>
											<label class="form-check-label">Driver</label>
										</div>
										<div class="form-check form-check-inline">
											<input class="form-check-input amount-given-to" type="radio" 
												name="${uniqueRadioName}" value="Vehicle" ${isVehicle ? 'checked' : ''}>
											<label class="form-check-label">Vehicle</label>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<label class="form-label required-label">Amount</label>
									<input type="number" class="form-control credit-amount" step="0.01" min="0" 
										value="${data.amount}" data-original="${data.amount}" required>
								</div>
							</div>
							<div class="row mt-2 driver-cnic-section" style="${isVehicle ? 'display: none;' : 'display: block;'}">
								<div class="col-md-4">
									<label class="form-label required-label">Driver CNIC</label>
									<input type="text" class="form-control credit-cnic" placeholder="Enter 13-digit CNIC" 
										maxlength="13" value="${data.cnic || ''}" data-original="${data.cnic || ''}">
									<small class="text-muted">13 digits without dashes</small>
								</div>
							</div>
							<div class="row mt-2 vehicle-number-section" style="${isVehicle ? 'display: block;' : 'display: none;'}">
								<div class="col-md-4">
									<label class="form-label required-label">Vehicle Number</label>
									<input type="text" class="form-control credit-vehicle-number" placeholder="Enter vehicle number" 
										value="${data.vehicle_number || ''}" data-original="${data.vehicle_number || ''}">
								</div>
							</div>
						</div>
					`;

			$("#credit_driver_forms_container").append(formHtml);
			const newForm = $("#credit_driver_forms_container .driver-credit-form").last();

			// Load stations for this form
			loadStationsForExistingForm(newForm, data.station_id, stationId);

			// Load customers after station is selected
			setTimeout(() => {
				const stationId = newForm.find('.credit-station-select').val();
				if (stationId) {
					loadCustomersForStation(stationId, newForm, data.account_id);
				}
			}, 500);

			// Add change tracking
			newForm.find('.credit-amount').on('input', function () {
				const newVal = $(this).val();
				const origVal = $(this).data('original');
				$(this).toggleClass('field-changed', newVal != origVal);

				// Recalculate cash distribution
				const totalSales = parseFloat($("#total_cash").val()) || 0;
				autoCalculateDistribution(totalSales);
			});

			newForm.find('.credit-cnic').on('input', function () {
				const newVal = $(this).val();
				const origVal = $(this).data('original');
				$(this).toggleClass('field-changed', newVal != origVal);
			});

			newForm.find('.credit-vehicle-number').on('input', function () {
				const newVal = $(this).val();
				const origVal = $(this).data('original');
				$(this).toggleClass('field-changed', newVal != origVal);
			});

			newForm.find('.credit-station-select').on('change', function () {
				const newVal = $(this).val();
				const origVal = $(this).data('original');
				$(this).toggleClass('field-changed', newVal != origVal);

				if (newVal) {
					loadCustomersForStation(newVal, newForm);
				}
			});

			newForm.find('.credit-customer-select').on('change', function () {
				const newVal = $(this).val();
				const origVal = $(this).data('original');
				$(this).toggleClass('field-changed', newVal != origVal);
			});

			// Handle amount given to toggle
			newForm.find('.amount-given-to').on('change', function () {
				const form = $(this).closest('.driver-credit-form');
				const selectedValue = $(this).val();

				if (selectedValue === 'Vehicle') {
					form.find('.vehicle-number-section').slideDown();
					form.find('.driver-cnic-section').slideUp();
					form.find('.credit-vehicle-number').prop('required', true);
					form.find('.credit-cnic').prop('required', false);
				} else {
					form.find('.driver-cnic-section').slideDown();
					form.find('.vehicle-number-section').slideUp();
					form.find('.credit-cnic').prop('required', true);
					form.find('.credit-vehicle-number').prop('required', false);
				}
			});

			return newForm;
		}

		// Load stations for existing form
		function loadStationsForExistingForm(formElement, selectedStationId, currentStationId) {
			$.ajax({
				url: `/api/stations/${AUTH_USER_ID}`,
				method: "GET",
				success: function (stations) {
					const $select = formElement.find('.credit-station-select');
					$select.empty().append('<option value="">Select Station...</option>');

					stations.forEach(station => {
						const selected = (station.id == selectedStationId) ? 'selected' : '';
						$select.append(`<option value="${station.id}" ${selected}>${station.name}</option>`);
					});

					// Store original value for change tracking
					$select.data('original', selectedStationId);
				},
				error: function (xhr) {
					console.error("Error loading stations:", xhr.responseText);
				}
			});
		}

		// Load customers for station
		function loadCustomersForStation(stationId, formElement, selectedCustomerId = null) {
			$.ajax({
				url: `/api/accounts/category/customer`,
				method: "GET",
				success: function (customers) {
					const stationCustomers = customers.filter(c => c.station_id == stationId);
					const $customerSelect = formElement.find('.credit-customer-select');
					$customerSelect.empty().append('<option value="">Select Customer...</option>');

					stationCustomers.forEach(customer => {
						const selected = (selectedCustomerId && customer.id == selectedCustomerId) ? 'selected' : '';
						$customerSelect.append(`<option value="${customer.id}" ${selected}>${customer.name} - ${customer.phone}</option>`);
					});

					// Store original value for change tracking
					if (selectedCustomerId) {
						$customerSelect.data('original', selectedCustomerId);
					}
				},
				error: function (xhr) {
					console.error("Error loading customers:", xhr.responseText);
				}
			});
		}

		// Add new empty driver credit form
		function addDriverCreditForm() {
			const formNumber = $(".driver-credit-form").length + 1;
			const uniqueRadioName = `amount_given_to_${formNumber}`;

			const formHtml = `
						<div class="driver-credit-form mb-4 p-3 border rounded">
							<div class="row">
								<div class="col-md-12 mb-3">
									<h6 class="text-primary">Driver Credit Entry <span class="form-number">#${formNumber}</span>
										<button type="button" class="btn btn-danger btn-sm float-end remove-driver-form">
											<i class="bi bi-trash"></i> Remove
										</button>
									</h6>
								</div>
								<div class="col-md-3">
									<label class="form-label required-label">Station</label>
									<select class="form-control credit-station-select" required>
										<option value="">Select Station...</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label required-label">Customer</label>
									<select class="form-control credit-customer-select" required>
										<option value="">Select Customer...</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label required-label">Amount Given To</label>
									<div class="mt-2">
										<div class="form-check form-check-inline">
											<input class="form-check-input amount-given-to" type="radio" 
												name="${uniqueRadioName}" value="Driver" checked>
											<label class="form-check-label">Driver</label>
										</div>
										<div class="form-check form-check-inline">
											<input class="form-check-input amount-given-to" type="radio" 
												name="${uniqueRadioName}" value="Vehicle">
											<label class="form-check-label">Vehicle</label>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<label class="form-label required-label">Amount</label>
									<input type="number" class="form-control credit-amount" step="0.01" min="0" placeholder="0.00" required>
								</div>
							</div>
							<div class="row mt-2 driver-cnic-section">
								<div class="col-md-4">
									<label class="form-label required-label">Driver CNIC</label>
									<input type="text" class="form-control credit-cnic" placeholder="Enter 13-digit CNIC" maxlength="13">
									<small class="text-muted">13 digits without dashes</small>
								</div>
							</div>
							<div class="row mt-2 vehicle-number-section" style="display: none;">
								<div class="col-md-4">
									<label class="form-label required-label">Vehicle Number</label>
									<input type="text" class="form-control credit-vehicle-number" placeholder="Enter vehicle number">
								</div>
							</div>
						</div>
					`;

			$("#credit_driver_forms_container").append(formHtml);
			const newForm = $("#credit_driver_forms_container .driver-credit-form").last();

			// Load stations for new form
			loadStationsForNewForm(newForm);

			// Handle amount given to toggle
			newForm.find('.amount-given-to').on('change', function () {
				const form = $(this).closest('.driver-credit-form');
				const selectedValue = $(this).val();

				if (selectedValue === 'Vehicle') {
					form.find('.vehicle-number-section').slideDown();
					form.find('.driver-cnic-section').slideUp();
					form.find('.credit-vehicle-number').prop('required', true);
					form.find('.credit-cnic').prop('required', false);
				} else {
					form.find('.driver-cnic-section').slideDown();
					form.find('.vehicle-number-section').slideUp();
					form.find('.credit-cnic').prop('required', true);
					form.find('.credit-vehicle-number').prop('required', false);
				}
			});

			// Add change tracking for amount
			newForm.find('.credit-amount').on('input', function () {
				const totalSales = parseFloat($("#total_cash").val()) || 0;
				autoCalculateDistribution(totalSales);
			});

			return newForm;
		}

		// Load stations for new form
		function loadStationsForNewForm(formElement) {
			const shiftId = $("#close_shift_id").val();

			$.ajax({
				url: `/api/shifts/${shiftId}`,
				method: "GET",
				success: function (shift) {
					const currentStationId = shift.station_id;

					$.ajax({
						url: `/api/stations/${AUTH_USER_ID}`,
						method: "GET",
						success: function (stations) {
							const $select = formElement.find('.credit-station-select');
							$select.empty().append('<option value="">Select Station...</option>');

							stations.forEach(station => {
								const selected = (station.id == currentStationId) ? 'selected' : '';
								$select.append(`<option value="${station.id}" ${selected}>${station.name}</option>`);
							});

							// Load customers when station changes
							$select.on('change', function () {
								const stationId = $(this).val();
								const form = $(this).closest('.driver-credit-form');
								if (stationId) {
									loadCustomersForStation(stationId, form);
								}
							});

							// Trigger change to load customers for default station
							if (currentStationId) {
								$select.trigger('change');
							}
						}
					});
				}
			});
		}

		// Remove driver form handler
		$(document).on("click", ".remove-driver-form", function (e) {
			e.preventDefault();
			const form = $(this).closest('.driver-credit-form');
			const creditId = form.data('credit-id');

			// If this form has an ID, mark it for deletion
			if (creditId) {
				if (!window.creditsToDelete) {
					window.creditsToDelete = [];
				}
				window.creditsToDelete.push(creditId);
			}

			form.remove();

			// Reindex remaining forms
			reindexDriverForms();

			// Recalculate cash distribution
			const totalSales = parseFloat($("#total_cash").val()) || 0;
			autoCalculateDistribution(totalSales);

			// If no forms left, uncheck the checkbox
			if ($(".driver-credit-form").length === 0) {
				$("#credit_to_driver_checkbox").prop('checked', false);
				$("#credit_driver_section").hide();
			}
		});

		// Reindex driver forms
		function reindexDriverForms() {
			$(".driver-credit-form").each(function (index) {
				const newNumber = index + 1;
				$(this).find('.form-number').text(`#${newNumber}`);

				// Update radio button names
				const newRadioName = `amount_given_to_${newNumber}`;
				$(this).find('.amount-given-to').attr('name', newRadioName);
			});
		}


	</script>
@endsection