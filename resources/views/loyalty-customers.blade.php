@extends('partials.layouts.master')

@section('title', 'Loyalty Customers | ' . Auth::user()->full_name)
@section('title-sub', 'Loyalty')
@section('pagetitle', 'Customers')

@section('content')
<div id="layout-wrapper">
    <div class="container-fluid mt-4">
        <div id="customerMessage" class="alert d-none" role="alert"></div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Loyalty Customers</h5>
                <button id="createCustomerButton" type="button" class="btn btn-primary d-none"
                    data-bs-toggle="modal" data-bs-target="#createCustomerModal">
                    <i class="bi bi-person-plus me-1"></i>Create Customer
                </button>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>ID</th>
                            <th>Player ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Device ID</th>
                            <th>Type</th>
                            <th>Referral number</th>
                            <th>Status</th>
                            <th>Created at</th>
                            <th>Updated at</th>
                        </tr>
                    </thead>
                    <tbody id="customersTableBody">
                        <tr><td colspan="12" class="text-center text-muted">Loading customers...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="customerModalTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="customerForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalTitle">Create Loyalty Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="customerFormMessage" class="alert d-none" role="alert"></div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customerPlayerId" class="form-label">Player ID</label>
                            <input id="customerPlayerId" name="player_id" type="text" maxlength="191"
                                class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customerName" class="form-label">Name</label>
                            <input id="customerName" name="name" type="text" maxlength="150"
                                class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customerPhone" class="form-label">Phone</label>
                            <input id="customerPhone" name="phone" type="tel" maxlength="30"
                                class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customerEmail" class="form-label">Email</label>
                            <input id="customerEmail" name="email" type="email" maxlength="191"
                                class="form-control" required>
                        </div>
                        <div id="customerPasswordGroup" class="col-md-6 mb-3">
                            <label for="customerPassword" class="form-label">Password</label>
                            <input id="customerPassword" name="password" type="password" minlength="6"
                                maxlength="255" autocomplete="new-password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customerDeviceId" class="form-label">Device ID</label>
                            <input id="customerDeviceId" name="device_id" type="text" maxlength="191"
                                class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customerType" class="form-label">Type</label>
                            <select id="customerType" name="type" class="form-select" required>
                                <option value="new">New</option>
                                <option value="referral">Referral</option>
                            </select>
                        </div>
                        <div id="referralNumberGroup" class="col-md-6 mb-3 d-none">
                            <label for="customerReferralNumber" class="form-label">Referral number (optional)</label>
                            <input id="customerReferralNumber" name="referral_number" type="text" maxlength="100"
                                class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customerStatus" class="form-label">Status</label>
                            <select id="customerStatus" name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="small text-muted">Password is stored securely as a one-way hash.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button id="saveCustomerButton" type="submit" class="btn btn-primary">Create Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    const customersApiUrl = "{{ url('/api/loyalty/customers') }}";
    const customersTableBody = document.getElementById('customersTableBody');
    const customerMessage = document.getElementById('customerMessage');
    const customerCreateButton = document.getElementById('createCustomerButton');
    const customerModal = document.getElementById('createCustomerModal');
    const customerForm = document.getElementById('customerForm');
    const customerFormMessage = document.getElementById('customerFormMessage');
    let canUpdateCustomers = false;

    function appendCustomerCell(row, text) {
        const cell = document.createElement('td');
        cell.textContent = text ?? '';
        row.appendChild(cell);
        return cell;
    }

    function showCustomerMessage(message, type) {
        customerMessage.textContent = message;
        customerMessage.className = `alert alert-${type}`;
    }

    function setCustomerFormMode(customer = null) {
        customerForm.reset();
        customerFormMessage.className = 'alert d-none';
        delete customerForm.dataset.customerId;

        const passwordInput = document.getElementById('customerPassword');
        const passwordGroup = document.getElementById('customerPasswordGroup');
        const referralNumberGroup = document.getElementById('referralNumberGroup');
        const submitButton = document.getElementById('saveCustomerButton');

        passwordInput.disabled = false;
        passwordInput.required = true;
        passwordGroup.classList.remove('d-none');
        referralNumberGroup.classList.add('d-none');

        document.getElementById('customerModalTitle').textContent = 'Create Loyalty Customer';
        submitButton.textContent = 'Create Customer';

        if (!customer) {
            return;
        }

        customerForm.dataset.customerId = customer.id;
        customerForm.elements.player_id.value = customer.player_id;
        customerForm.elements.name.value = customer.name;
        customerForm.elements.phone.value = customer.phone;
        customerForm.elements.email.value = customer.email;
        customerForm.elements.device_id.value = customer.device_id;
        customerForm.elements.type.value = customer.type;
        customerForm.elements.referral_number.value = customer.referral_number || '';
        customerForm.elements.status.value = customer.status;
        passwordInput.value = '';
        passwordInput.required = false;
        passwordInput.disabled = true;
        passwordGroup.classList.add('d-none');
        referralNumberGroup.classList.toggle('d-none', customer.type !== 'referral');

        document.getElementById('customerModalTitle').textContent = 'Edit Loyalty Customer';
        submitButton.textContent = 'Save Changes';
    }

    async function editCustomer(id) {
        try {
            const response = await fetch(`${customersApiUrl}/${id}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Could not load customer details.');
            }

            setCustomerFormMode(result.data);
            bootstrap.Modal.getOrCreateInstance(customerModal).show();
        } catch (error) {
            showCustomerMessage(error.message, 'danger');
        }
    }

    async function loadCustomers() {
        try {
            const response = await fetch(customersApiUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Could not load loyalty customers.');
            }

            customerCreateButton.classList.toggle('d-none', result.can_create !== true);
            canUpdateCustomers = result.can_update === true;
            customersTableBody.replaceChildren();

            if (!result.data.length) {
                const row = document.createElement('tr');
                const cell = appendCustomerCell(row, 'No loyalty customers found.');
                cell.colSpan = 12;
                cell.className = 'text-center text-muted';
                customersTableBody.appendChild(row);
                return;
            }

            result.data.forEach(customer => {
                const row = document.createElement('tr');
                const actionsCell = document.createElement('td');
                if (canUpdateCustomers) {
                    const editButton = document.createElement('button');
                    editButton.type = 'button';
                    editButton.className = 'btn btn-sm btn-outline-primary';
                    editButton.innerHTML = '<i class="bi bi-pencil-square me-1"></i>Edit';
                    editButton.addEventListener('click', () => editCustomer(customer.id));
                    actionsCell.appendChild(editButton);
                } else {
                    actionsCell.textContent = '—';
                }
                row.appendChild(actionsCell);

                appendCustomerCell(row, customer.id);
                appendCustomerCell(row, customer.player_id);
                appendCustomerCell(row, customer.name);
                appendCustomerCell(row, customer.phone);
                appendCustomerCell(row, customer.email);
                appendCustomerCell(row, customer.device_id);
                appendCustomerCell(row, customer.type.charAt(0).toUpperCase() + customer.type.slice(1));
                appendCustomerCell(row, customer.referral_number || '—');

                const statusCell = document.createElement('td');
                if (canUpdateCustomers) {
                    const statusControl = document.createElement('div');
                    statusControl.className = 'form-check form-switch d-flex align-items-center gap-2 mb-0';

                    const toggle = document.createElement('input');
                    toggle.type = 'checkbox';
                    toggle.className = 'form-check-input m-0';
                    toggle.setAttribute('role', 'switch');
                    toggle.checked = customer.status === 'active';
                    toggle.setAttribute('aria-label', `Set ${customer.name} status`);

                    const statusLabel = document.createElement('span');
                    statusLabel.textContent = customer.status.charAt(0).toUpperCase()
                        + customer.status.slice(1);

                    toggle.addEventListener('change', async () => {
                        const oldStatus = customer.status;
                        const newStatus = toggle.checked ? 'active' : 'inactive';
                        toggle.disabled = true;

                        try {
                            const response = await fetch(`${customersApiUrl}/${customer.id}/status`, {
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
                                throw new Error(result.message || 'Could not update customer status.');
                            }

                            customer.status = newStatus;
                            statusLabel.textContent = newStatus.charAt(0).toUpperCase()
                                + newStatus.slice(1);
                            showCustomerMessage(result.message, 'success');
                        } catch (error) {
                            toggle.checked = oldStatus === 'active';
                            showCustomerMessage(error.message, 'danger');
                        } finally {
                            toggle.disabled = false;
                        }
                    });

                    statusControl.append(toggle, statusLabel);
                    statusCell.appendChild(statusControl);
                } else {
                    const badge = document.createElement('span');
                    badge.className = `badge bg-${customer.status === 'active' ? 'success' : 'secondary'}`;
                    badge.textContent = customer.status.charAt(0).toUpperCase() + customer.status.slice(1);
                    statusCell.appendChild(badge);
                }
                row.appendChild(statusCell);

                appendCustomerCell(row, customer.created_at);
                appendCustomerCell(row, customer.updated_at);
                customersTableBody.appendChild(row);
            });
        } catch (error) {
            customerCreateButton.classList.add('d-none');
            customersTableBody.replaceChildren();
            const row = document.createElement('tr');
            const cell = appendCustomerCell(row, error.message);
            cell.colSpan = 12;
            cell.className = 'text-center text-danger';
            customersTableBody.appendChild(row);
        }
    }

    document.getElementById('customerType').addEventListener('change', event => {
        document.getElementById('referralNumberGroup').classList.toggle(
            'd-none',
            event.currentTarget.value !== 'referral'
        );
    });

    customerCreateButton.addEventListener('click', () => setCustomerFormMode());

    customerModal.addEventListener('hidden.bs.modal', () => {
        customerForm.reset();
        customerFormMessage.className = 'alert d-none';
        document.getElementById('referralNumberGroup').classList.add('d-none');
    });

    customerForm.addEventListener('submit', async event => {
        event.preventDefault();
        const submitButton = document.getElementById('saveCustomerButton');
        const data = Object.fromEntries(new FormData(customerForm).entries());
        const customerId = customerForm.dataset.customerId;
        submitButton.disabled = true;
        customerFormMessage.className = 'alert d-none';

        try {
            const response = await fetch(
                customerId ? `${customersApiUrl}/${customerId}` : customersApiUrl,
                {
                method: customerId ? 'PUT' : 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
                }
            );
            const result = await response.json();
            if (!response.ok) {
                const firstValidationError = result.errors
                    ? Object.values(result.errors).flat()[0]
                    : null;
                throw new Error(firstValidationError || result.message || 'Could not save customer.');
            }

            bootstrap.Modal.getOrCreateInstance(customerModal).hide();
            showCustomerMessage(result.message, 'success');
            await loadCustomers();
        } catch (error) {
            customerFormMessage.textContent = error.message;
            customerFormMessage.className = 'alert alert-danger';
        } finally {
            submitButton.disabled = false;
        }
    });

    loadCustomers();
</script>
@endsection
