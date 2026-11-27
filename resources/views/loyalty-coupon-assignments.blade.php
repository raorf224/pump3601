@extends('partials.layouts.master')

@section('title', 'Assign Loyalty Coupons | ' . Auth::user()->full_name)
@section('title-sub', 'Loyalty')
@section('pagetitle', 'Assign Coupons')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
@endsection

@section('content')
<div id="layout-wrapper">
    <div class="container-fluid mt-4">
        <div id="assignmentMessage" class="alert d-none" role="alert"></div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Assign Specific Coupons</h5>
            </div>
            <div class="card-body">
                <form id="couponAssignmentForm" class="row g-3">
                    <div class="col-lg-5">
                        <label for="assignmentCustomer" class="form-label">Active customers</label>
                        <select id="assignmentCustomer" class="form-select" multiple required></select>
                        <div class="d-flex gap-2 mt-2">
                            <button id="selectAllCustomers" type="button" class="btn btn-sm btn-outline-primary">
                                Select All Customers
                            </button>
                            <button id="clearCustomers" type="button" class="btn btn-sm btn-outline-secondary">
                                Clear
                            </button>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <label for="assignmentCoupon" class="form-label">Specific coupons</label>
                        <select id="assignmentCoupon" class="form-select" multiple required></select>
                        <div class="d-flex gap-2 mt-2">
                            <button id="selectAllCoupons" type="button" class="btn btn-sm btn-outline-primary">
                                Select All Coupons
                            </button>
                            <button id="clearCoupons" type="button" class="btn btn-sm btn-outline-secondary">
                                Clear
                            </button>
                        </div>
                    </div>
                    <div class="col-lg-2 d-flex align-items-end">
                        <button id="assignCouponButton" type="submit" class="btn btn-primary w-100" disabled>
                            Assign Selected
                        </button>
                    </div>
                </form>
                <div class="form-text mt-2">Select one or more customers and one or more coupons.</div>
                <div id="assignmentPermissionNote" class="small text-muted mt-3 d-none">
                    You have read-only access to coupon assignments.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Specific Coupons Assigned to Selected Customers</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Coupon</th>
                            <th>Discount</th>
                            <th>Validity</th>
                            <th>Used</th>
                            <th>Usable</th>
                            <th>Assigned at</th>
                        </tr>
                    </thead>
                    <tbody id="assignedCouponsTable">
                        <tr><td colspan="7" class="text-center text-muted">Select customers to view assignments.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script>
    const assignmentOptionsApiUrl = "{{ url('/api/loyalty/coupons/assign/options') }}";
    const assignmentsApiUrl = "{{ url('/api/loyalty/coupons/assignments') }}";
    const assignCouponsApiUrl = "{{ url('/api/loyalty/coupons/assign') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const assignmentForm = document.getElementById('couponAssignmentForm');
    const customerSelect = document.getElementById('assignmentCustomer');
    const couponSelect = document.getElementById('assignmentCoupon');
    const assignedCouponsTable = document.getElementById('assignedCouponsTable');
    const assignmentMessage = document.getElementById('assignmentMessage');
    const assignButton = document.getElementById('assignCouponButton');
    const selectAllCustomersButton = document.getElementById('selectAllCustomers');
    const clearCustomersButton = document.getElementById('clearCustomers');
    const selectAllCouponsButton = document.getElementById('selectAllCoupons');
    const clearCouponsButton = document.getElementById('clearCoupons');
    let activeCustomers = [];
    let availableCoupons = [];
    let assignedSpecificCoupons = [];
    let canAssignCoupons = false;
    let customerChoices = null;
    let couponChoices = null;
    let suppressCustomerChange = false;
    let suppressCouponChange = false;

    function appendCell(row, value) {
        const cell = document.createElement('td');
        cell.textContent = value ?? '';
        row.appendChild(cell);
        return cell;
    }

    function showAssignmentMessage(message, type) {
        assignmentMessage.textContent = message;
        assignmentMessage.className = `alert alert-${type}`;
    }

    async function readJson(response) {
        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || 'Could not load coupon assignments.');
        }
        return result;
    }

    function renderCustomers() {
        if (customerChoices) {
            customerChoices.destroy();
        }
        customerSelect.replaceChildren();
        activeCustomers.forEach(customer => {
            customerSelect.add(new Option(`${customer.name} (ID: ${customer.id})`, customer.id));
        });
        if (activeCustomers.length === 0) {
            customerSelect.add(new Option('No active customers available', ''));
        }
        customerSelect.disabled = activeCustomers.length === 0;
        customerChoices = new Choices(customerSelect, {
            removeItemButton: true,
            searchEnabled: true,
            shouldSort: false,
            itemSelectText: '',
            placeholderValue: 'Select customers'
        });
    }

    function selectedCustomerIds() {
        return Array.from(customerSelect.selectedOptions).map(option => option.value).filter(Boolean);
    }

    function selectedCouponIds() {
        return Array.from(couponSelect.selectedOptions).map(option => option.value).filter(Boolean);
    }

    function renderAvailableCoupons() {
        const previouslySelectedCoupons = new Set(selectedCouponIds());
        if (couponChoices) {
            couponChoices.destroy();
        }
        couponSelect.replaceChildren();
        const selectedCustomers = new Set(selectedCustomerIds());
        const assignedCustomersByCoupon = new Map();
        assignedSpecificCoupons.forEach(assignment => {
            const couponId = String(assignment.coupon_id);
            if (!selectedCustomers.has(String(assignment.customer_id))) {
                return;
            }
            if (!assignedCustomersByCoupon.has(couponId)) {
                assignedCustomersByCoupon.set(couponId, new Set());
            }
            assignedCustomersByCoupon.get(couponId).add(String(assignment.customer_id));
        });

        availableCoupons.forEach(coupon => {
            const discount = coupon.coupon_type === 'percent'
                ? `${Number(coupon.value)}%`
                : `Rs. ${Number(coupon.value).toFixed(2)}`;
            const alreadyAssigned = assignedCustomersByCoupon.get(String(coupon.id)) || new Set();
            const option = new Option(
                `${coupon.name} (${discount})${alreadyAssigned.size ? ` - assigned to ${alreadyAssigned.size}/${selectedCustomers.size}` : ''}`,
                coupon.id
            );
            option.disabled = selectedCustomers.size > 0 && alreadyAssigned.size === selectedCustomers.size;
            option.selected = previouslySelectedCoupons.has(String(coupon.id)) && !option.disabled;
            couponSelect.add(option);
        });

        if (availableCoupons.length === 0) {
            couponSelect.add(new Option('No active specific coupons available', ''));
        }

        couponSelect.disabled = !canAssignCoupons || availableCoupons.length === 0;
        couponChoices = new Choices(couponSelect, {
            removeItemButton: true,
            searchEnabled: true,
            shouldSort: false,
            itemSelectText: '',
            placeholderValue: 'Select coupons'
        });
        const selectableCoupons = Array.from(couponSelect.options)
            .filter(option => option.value && !option.disabled);
        selectAllCouponsButton.disabled = !canAssignCoupons || selectableCoupons.length === 0;
        clearCouponsButton.disabled = selectedCouponIds().length === 0;
        clearCustomersButton.disabled = selectedCustomerIds().length === 0;
        assignButton.disabled = !canAssignCoupons
            || selectedCustomers.size === 0
            || selectedCouponIds().length === 0;
    }

    function renderAssignedCoupons() {
        assignedCouponsTable.replaceChildren();
        if (assignedSpecificCoupons.length === 0) {
            const row = document.createElement('tr');
            const cell = appendCell(row, 'No specific coupons assigned to the selected customers.');
            cell.colSpan = 7;
            cell.className = 'text-center text-muted';
            assignedCouponsTable.appendChild(row);
            return;
        }

        assignedSpecificCoupons.forEach(coupon => {
            const row = document.createElement('tr');
            appendCell(row, `${coupon.customer_name} (ID: ${coupon.customer_id})`);
            appendCell(row, coupon.name);
            appendCell(row, coupon.coupon_type === 'percent'
                ? `${Number(coupon.value)}%`
                : `Rs. ${Number(coupon.value).toFixed(2)}`);
            appendCell(row, `${coupon.valid_from} – ${coupon.valid_to}`);
            appendCell(row, String(coupon.is_used) === '1' ? 'Yes' : 'No');
            appendCell(row, coupon.usable ? 'Yes' : 'No');
            appendCell(row, coupon.assigned_at);
            assignedCouponsTable.appendChild(row);
        });
    }

    async function loadAssignedCoupons() {
        const customerIds = selectedCustomerIds();
        assignedSpecificCoupons = [];
        if (customerIds.length === 0) {
            assignedCouponsTable.innerHTML =
                '<tr><td colspan="7" class="text-center text-muted">Select customers to view assignments.</td></tr>';
            renderAvailableCoupons();
            return;
        }

        const result = await readJson(await fetch(assignmentsApiUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ customer_ids: customerIds }),
            credentials: 'same-origin'
        }));
        assignedSpecificCoupons = result.data;
        renderAssignedCoupons();
        renderAvailableCoupons();
    }

    async function loadAssignmentData() {
        try {
            const result = await readJson(await fetch(assignmentOptionsApiUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            }));

            activeCustomers = result.data.customers;
            availableCoupons = result.data.coupons;
            canAssignCoupons = result.can_assign === true;
            assignButton.classList.toggle('d-none', !canAssignCoupons);
            document.getElementById('assignmentPermissionNote').classList.toggle('d-none', canAssignCoupons);
            selectAllCustomersButton.disabled = activeCustomers.length === 0;
            clearCustomersButton.disabled = true;

            renderCustomers();
            renderAvailableCoupons();
        } catch (error) {
            showAssignmentMessage(error.message, 'danger');
        }
    }

    customerSelect.addEventListener('change', async () => {
        if (suppressCustomerChange) {
            return;
        }
        assignmentMessage.className = 'alert d-none';
        assignButton.disabled = true;
        try {
            await loadAssignedCoupons();
        } catch (error) {
            showAssignmentMessage(error.message, 'danger');
        }
    });

    selectAllCustomersButton.addEventListener('click', async () => {
        suppressCustomerChange = true;
        customerChoices.setChoiceByValue(activeCustomers.map(customer => String(customer.id)));
        suppressCustomerChange = false;
        customerSelect.dispatchEvent(new Event('change', { bubbles: true }));
    });

    clearCustomersButton.addEventListener('click', () => {
        suppressCustomerChange = true;
        customerChoices.removeActiveItems();
        suppressCustomerChange = false;
        customerSelect.dispatchEvent(new Event('change', { bubbles: true }));
    });

    selectAllCouponsButton.addEventListener('click', () => {
        const couponIds = Array.from(couponSelect.options)
            .filter(option => option.value && !option.disabled)
            .map(option => option.value);
        suppressCouponChange = true;
        couponChoices.setChoiceByValue(couponIds);
        suppressCouponChange = false;
        couponSelect.dispatchEvent(new Event('change', { bubbles: true }));
    });

    clearCouponsButton.addEventListener('click', () => {
        suppressCouponChange = true;
        couponChoices.removeActiveItems();
        suppressCouponChange = false;
        couponSelect.dispatchEvent(new Event('change', { bubbles: true }));
    });

    couponSelect.addEventListener('change', () => {
        if (suppressCouponChange) {
            return;
        }
        assignButton.disabled = !canAssignCoupons
            || selectedCustomerIds().length === 0
            || selectedCouponIds().length === 0;
        clearCouponsButton.disabled = selectedCouponIds().length === 0;
    });

    assignmentForm.addEventListener('submit', async event => {
        event.preventDefault();
        const customerIds = selectedCustomerIds();
        const couponIds = selectedCouponIds();
        assignButton.disabled = true;
        assignmentMessage.className = 'alert d-none';

        try {
            const response = await fetch(assignCouponsApiUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin',
                body: JSON.stringify({ customer_ids: customerIds, coupon_ids: couponIds })
            });
            const result = await readJson(response);
            showAssignmentMessage(result.message, 'success');
            await loadAssignedCoupons();
        } catch (error) {
            showAssignmentMessage(error.message, 'danger');
        } finally {
            renderAvailableCoupons();
        }
    });

    loadAssignmentData();
</script>
@endsection
