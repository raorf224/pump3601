@extends('partials.layouts.master')

@section('title', 'Loyalty Coupons | ' . Auth::user()->full_name)
@section('title-sub', 'Loyalty')
@section('pagetitle', 'Coupons')

@section('content')
<div id="layout-wrapper">
    <div class="container-fluid mt-4">
        <div id="couponMessage" class="alert d-none" role="alert"></div>

        <div class="row g-4">
            <div id="couponCreateSection" class="col-lg-4 d-none">
                <div class="card">
                    <div class="card-header">
                        <h5 id="couponFormTitle" class="mb-0">Create Coupon</h5>
                    </div>
                    <div class="card-body">
                        <form id="couponForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Coupon name</label>
                                <input id="name" name="name" type="text" maxlength="150" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" rows="3" class="form-control"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="coupon_for" class="form-label">Coupon for</label>
                                <select id="coupon_for" name="coupon_for" class="form-select" required>
                                    <option value="">Select coupon audience</option>
                                    <option value="welcome">Welcome</option>
                                    <option value="all">All customers</option>
                                    <option value="specific">Specific customers</option>
                                    <option value="referall">Referral</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="coupon_type" class="form-label">Discount type</label>
                                <select id="coupon_type" name="coupon_type" class="form-select" required>
                                    <option value="">Select type</option>
                                    <option value="percent">Percentage (%)</option>
                                    <option value="flat">Flat amount (Rs.)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="value" class="form-label">Discount value</label>
                                <input id="value" name="value" type="number" min="0.01" step="0.01" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="valid_from" class="form-label">Valid from</label>
                                <input id="valid_from" name="valid_from" type="date" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="valid_to" class="form-label">Valid to</label>
                                <input id="valid_to" name="valid_to" type="date" class="form-control" required>
                            </div>

                            <button id="couponSubmitButton" type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Create Coupon
                            </button>
                            <button id="cancelCouponEdit" type="button" class="btn btn-outline-secondary d-none">
                                Cancel
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div id="couponListSection" class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Coupons</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Actions</th>
                                    <th>Name</th>
                                    <th>Coupon for</th>
                                    <th>Discount</th>
                                    <th>Validity</th>
                                    <th>Status</th>
                                    <th>Created by</th>
                                </tr>
                            </thead>
                            <tbody id="couponsTableBody">
                                <tr><td colspan="7" class="text-center text-muted">Loading coupons...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('js')
<script>
    const couponsApiUrl = "{{ url('/api/loyalty/coupons') }}";
    const couponsTableBody = document.getElementById('couponsTableBody');
    const couponMessage = document.getElementById('couponMessage');
    const couponForm = document.getElementById('couponForm');
    const couponCreateSection = document.getElementById('couponCreateSection');
    const couponListSection = document.getElementById('couponListSection');
    const couponPermissions = { canCreate: false, canUpdate: false };

    function setCouponFormVisible(visible) {
        couponCreateSection.classList.toggle('d-none', !visible);
        couponListSection.className = visible ? 'col-lg-8' : 'col-lg-12';
    }

    function resetCouponForm() {
        couponForm.reset();
        delete couponForm.dataset.couponId;
        document.getElementById('couponFormTitle').textContent = 'Create Coupon';
        document.getElementById('couponSubmitButton').innerHTML =
            '<i class="bi bi-plus-circle me-1"></i>Create Coupon';
        document.getElementById('cancelCouponEdit').classList.add('d-none');
    }

    function editCoupon(coupon) {
        couponForm.dataset.couponId = coupon.id;
        couponForm.elements.name.value = coupon.name;
        couponForm.elements.description.value = coupon.description || '';
        couponForm.elements.coupon_for.value = coupon.coupon_for;
        couponForm.elements.coupon_type.value = coupon.coupon_type;
        couponForm.elements.value.value = coupon.value;
        couponForm.elements.status.value = coupon.status;
        couponForm.elements.valid_from.value = coupon.valid_from.slice(0, 10);
        couponForm.elements.valid_to.value = coupon.valid_to.slice(0, 10);
        document.getElementById('couponFormTitle').textContent = 'Edit Coupon';
        document.getElementById('couponSubmitButton').innerHTML =
            '<i class="bi bi-check-circle me-1"></i>Save Changes';
        document.getElementById('cancelCouponEdit').classList.remove('d-none');
        setCouponFormVisible(true);
        couponCreateSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function showCouponMessage(message, type) {
        couponMessage.textContent = message;
        couponMessage.className = `alert alert-${type}`;
    }

    function appendCell(row, text) {
        const cell = document.createElement('td');
        cell.textContent = text;
        row.appendChild(cell);
        return cell;
    }

    async function loadCoupons() {
        try {
            const response = await fetch(couponsApiUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Could not load coupons.');
            }

            const coupons = result.data;
            couponPermissions.canCreate = result.can_create === true;
            couponPermissions.canUpdate = result.can_update === true;
            if (!couponForm.dataset.couponId) {
                setCouponFormVisible(couponPermissions.canCreate);
            }

            couponsTableBody.replaceChildren();
            if (!coupons.length) {
                const row = document.createElement('tr');
                const cell = appendCell(row, 'No coupons created yet.');
                cell.colSpan = 7;
                cell.className = 'text-center text-muted';
                couponsTableBody.appendChild(row);
                return;
            }

            coupons.forEach(coupon => {
                const row = document.createElement('tr');
                const actionsCell = document.createElement('td');
                if (couponPermissions.canUpdate) {
                    const editButton = document.createElement('button');
                    editButton.type = 'button';
                    editButton.className = 'btn btn-sm btn-outline-primary';
                    editButton.innerHTML = '<i class="bi bi-pencil-square me-1"></i>Edit';
                    editButton.addEventListener('click', () => editCoupon(coupon));
                    actionsCell.appendChild(editButton);
                } else {
                    actionsCell.textContent = '—';
                }
                row.appendChild(actionsCell);

                const nameCell = appendCell(row, coupon.name);
                if (coupon.description) {
                    const description = document.createElement('div');
                    description.className = 'small text-muted';
                    description.textContent = coupon.description;
                    nameCell.appendChild(description);
                }

                const couponForLabels = {
                    welcome: 'Welcome',
                    all: 'All customers',
                    specific: 'Specific customers',
                    referall: 'Referral'
                };
                appendCell(row, couponForLabels[coupon.coupon_for] || coupon.coupon_for);

                const discount = coupon.coupon_type === 'percent'
                    ? `${Number(coupon.value).toFixed(2).replace(/\.?0+$/, '')}%`
                    : `Rs. ${Number(coupon.value).toFixed(2)}`;
                appendCell(row, discount);
                appendCell(row, `${coupon.valid_from} – ${coupon.valid_to}`);

                const statusCell = document.createElement('td');
                if (couponPermissions.canUpdate) {
                    const statusControl = document.createElement('div');
                    statusControl.className = 'form-check form-switch d-flex align-items-center gap-2 mb-0';

                    const toggle = document.createElement('input');
                    toggle.type = 'checkbox';
                    toggle.className = 'form-check-input m-0';
                    toggle.role = 'switch';
                    toggle.checked = coupon.status === 'active';
                    toggle.setAttribute('aria-label', `Set ${coupon.name} status`);

                    const statusLabel = document.createElement('span');
                    statusLabel.textContent = coupon.status.charAt(0).toUpperCase() + coupon.status.slice(1);

                    toggle.addEventListener('change', async () => {
                        const oldStatus = coupon.status;
                        const newStatus = toggle.checked ? 'active' : 'inactive';
                        toggle.disabled = true;

                        try {
                            const response = await fetch(`${couponsApiUrl}/${coupon.id}/status`, {
                                method: 'PATCH',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({ status: newStatus })
                            });
                            const result = await response.json();
                            if (!response.ok) {
                                throw new Error(result.message || 'Could not update coupon status.');
                            }

                            coupon.status = newStatus;
                            statusLabel.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                            showCouponMessage(result.message, 'success');
                        } catch (error) {
                            toggle.checked = oldStatus === 'active';
                            showCouponMessage(error.message, 'danger');
                        } finally {
                            toggle.disabled = false;
                        }
                    });

                    statusControl.append(toggle, statusLabel);
                    statusCell.appendChild(statusControl);
                } else {
                    const badge = document.createElement('span');
                    badge.className = `badge bg-${coupon.status === 'active' ? 'success' : 'secondary'}`;
                    badge.textContent = coupon.status.charAt(0).toUpperCase() + coupon.status.slice(1);
                    statusCell.appendChild(badge);
                }
                row.appendChild(statusCell);

                appendCell(row, coupon.creator_name || 'Unknown');
                couponsTableBody.appendChild(row);
            });
        } catch (error) {
            couponsTableBody.replaceChildren();
            const row = document.createElement('tr');
            const cell = appendCell(row, error.message);
            cell.colSpan = 6;
            cell.className = 'text-center text-danger';
            couponsTableBody.appendChild(row);
        }
    }

    if (couponForm) {
        couponForm.addEventListener('submit', async event => {
            event.preventDefault();
            const form = event.currentTarget;
            const submitButton = document.getElementById('couponSubmitButton');
            const data = Object.fromEntries(new FormData(form).entries());
            const couponId = form.dataset.couponId;

            submitButton.disabled = true;
            couponMessage.className = 'alert d-none';

            try {
                const response = await fetch(couponId ? `${couponsApiUrl}/${couponId}` : couponsApiUrl, {
                    method: couponId ? 'PUT' : 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (!response.ok) {
                    const firstValidationError = result.errors
                        ? Object.values(result.errors).flat()[0]
                        : null;
                    throw new Error(firstValidationError || result.message || 'Could not save coupon.');
                }

                resetCouponForm();
                setCouponFormVisible(couponPermissions.canCreate);
                showCouponMessage(result.message, 'success');
                await loadCoupons();
            } catch (error) {
                showCouponMessage(error.message, 'danger');
            } finally {
                submitButton.disabled = false;
            }
        });
    }

    document.getElementById('cancelCouponEdit').addEventListener('click', () => {
        resetCouponForm();
        setCouponFormVisible(couponPermissions.canCreate);
    });

    loadCoupons();
</script>
@endsection
