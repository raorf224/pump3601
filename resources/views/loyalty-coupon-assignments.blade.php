@extends('partials.layouts.master')

@section('title', 'Assign Loyalty Coupons | ' . Auth::user()->full_name)
@section('title-sub', 'Loyalty')
@section('pagetitle', 'Assign Coupons')

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
                        <select id="assignmentCustomer" class="form-select" multiple size="8" required>
                        </select>
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
                        <select id="assignmentCoupon" class="form-select" multiple size="8" required disabled>
                        </select>
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
                <h5 class="mb-0">Specific Coupons Assigned to Selected Customer</h5>
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
<script>
    const couponsApiUrl = "{{ url('/api/loyalty/coupons') }}";
    const customersApiUrl = "{{ url('/api/loyalty/customers') }}";
    const assignmentsApiUrl = "{{ url('/api/loyalty/coupons/assignments') }}";
    const assignCouponsApiUrl = "{{ url('/api/loyalty/coupons/assign') }}";
    const today = @json(now()->toDateString());
    const assignmentForm = document.getElementById('couponAssignmentForm');
    const customerSelect = document.getElementById('assignmentCustomer');
    const couponSelect = document.getElementById('assignmentCoupon');
    const assignedCouponsTable = document.getElementById('assignedCouponsTable');
    const assignmentMessage = document.getElementById('assignmentMessage');
    const assignButton = document.getElementById('assignCouponButton');
    const selectAllCustomersButton = document.getElementById('selectAllCustomers');
    const clearCustomersButton = document.getElementById('clearCustomers');
    let activeCustomers = [];
    let availableCoupons = [];
    let assignedSpecificCoupons = [];
    let canAssignCoupons = false;

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
        customerSelect.replaceChildren();
        activeCustomers.forEach(customer => {
            customerSelect.add(new Option(`${customer.name} (ID: ${customer.id})`, customer.id));
        });
        if (activeCustomers.length === 0) {
            customerSelect.add(new Option('No active customers available', ''));
        }
    }

    function selectedCustomerIds() {
        return Array.from(customerSelect.selectedOptions).map(option => option.value).filter(Boolean);
    }

    function selectedCouponIds() {
        return Array.from(couponSelect.selectedOptions).map(option => option.value).filter(Boolean);
    }

    function renderAvailableCoupons() {
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
            couponSelect.add(option);
        });

        if (availableCoupons.length === 0) {
            couponSelect.add(new Option('No active specific coupons available', ''));
        }

        couponSelect.disabled = !canAssignCoupons || selectedCustomers.size === 0 || availableCoupons.length === 0;
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
                'Content-Type': 'application/json'
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
            const [customerResponse, couponResponse] = await Promise.all([
                fetch(customersApiUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }),
                fetch(couponsApiUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            ]);
            const [customerResult, couponResult] = await Promise.all([
                readJson(customerResponse),
                readJson(couponResponse)
            ]);

            activeCustomers = customerResult.data.filter(customer => customer.status === 'active');
            availableCoupons = couponResult.data.filter(coupon =>
                coupon.coupon_for === 'specific'
                && coupon.status === 'active'
                && coupon.valid_to >= today
            );
            canAssignCoupons = customerResult.can_update === true && couponResult.can_update === true;
            assignButton.classList.toggle('d-none', !canAssignCoupons);
            document.getElementById('assignmentPermissionNote').classList.toggle('d-none', canAssignCoupons);
            selectAllCustomersButton.disabled = activeCustomers.length === 0;
            clearCustomersButton.disabled = activeCustomers.length === 0;

            renderCustomers();
            renderAvailableCoupons();
        } catch (error) {
            showAssignmentMessage(error.message, 'danger');
        }
    }

    customerSelect.addEventListener('change', async () => {
        assignmentMessage.className = 'alert d-none';
        try {
            await loadAssignedCoupons();
        } catch (error) {
            showAssignmentMessage(error.message, 'danger');
        }
    });

    selectAllCustomersButton.addEventListener('click', async () => {
        Array.from(customerSelect.options).forEach(option => {
            option.selected = Boolean(option.value);
        });
        customerSelect.dispatchEvent(new Event('change'));
    });

    clearCustomersButton.addEventListener('click', () => {
        Array.from(customerSelect.options).forEach(option => {
            option.selected = false;
        });
        customerSelect.dispatchEvent(new Event('change'));
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
                    'Content-Type': 'application/json'
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
