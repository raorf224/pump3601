@extends('partials.layouts.master')

@section('title', 'Station')
@section('pagetitle', 'Station')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<style>
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .price-input:invalid {
        border-color: #dc3545;
    }

    #productTable .form-select,
    #productTable .form-control {
        min-width: 150px;
    }
    
    /* 👇 DateTime input ke liye style */
    input[type="datetime-local"] {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    
    input[type="datetime-local"]::-webkit-calendar-picker-indicator {
        cursor: pointer;
        opacity: 0.6;
        filter: invert(0.5);
    }
    
    input[type="datetime-local"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }
</style>
@endsection

@section('content')
    <div id="layout-wrapper">
        <div class="container-fluid mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="stationForm">
                                @csrf
                                <div class="row g-4">
                                    <!-- Station Info -->
                                    <div class="col-md-6">
                                        <label class="form-label">Station Name</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" id="phone" name="phone" class="form-control">
                                    </div>

                                    <!-- Location Fields -->
                                    <div class="col-md-6">
                                        <label class="form-label">Coordinates</label>
                                        <input type="text" id="coordinates" name="coordinates" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Location</label>
                                        <input type="text" id="location" name="location" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">City</label>
                                        <input type="text" id="city" name="city" class="form-control">
                                    </div>
									<div class="col-md-4">
                                        <label class="form-label">Working Capital</label>
                                        <input type="text" id="working_capital" name="working_capital" class="form-control">
                                    </div>
								<div class="col-md-4">
    <label class="form-label d-block">Is Local</label>
    
    <div class="d-flex align-items-center" style="height: 38px;">
        <div class="form-check m-0">
            <input type="checkbox" id="local" name="local" class="form-check-input">
        </div>
    </div>
</div>

                                    <!-- Products Section -->
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <label class="form-label mb-0">Products</label>
                                            <button type="button" class="btn btn-sm btn-primary" id="addProduct">
                                                <i class="fas fa-plus"></i> Add Product
                                            </button>
                                        </div>
                                        <table class="table table-bordered" id="productTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="35%">Product</th>
                                                    <th width="25%">Price</th>
                                                    <th width="25%">Effective From</th>
                                                    <th width="25%">Effective To</th>

                                                    <th width="15%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Rows will be added dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="mt-4 d-flex justify-content-end gap-3">
                                    <a href="{{ url('station-sites') }}" class="btn btn-light-primary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <span id="submitBtnText">Create Station</span>
                                        <div id="submitSpinner" class="spinner-border spinner-border-sm d-none"
                                            role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </main>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    let productIndex = 0;
    let productsData = [];
    let stationId = null;
    const AUTH_USER_ID = "{{ Auth::id() }}";

    // ✅ Get query parameter
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    // ✅ Load all products from API
    function loadProducts() {
        $.get("api/products", function (res) {
            productsData = res;
            console.log('Products loaded:', productsData);
            
            // ✅ Create mode mein initial row add nahi karenge
            // Sirf edit mode mein ya jab user manually add kare
        }).fail(function (xhr) {
            console.error("Failed to load products:", xhr.responseText);
            toastr.error("Failed to load products. Please refresh the page.");
        });
    }

    // ✅ Load station data for editing
    function loadStationForEdit(id) {
        console.log('Loading station for edit:', id);

        $.get(`api/stationss/${id}`, function (res) {
            console.log('Station data:', res);
            const s = res;
            if (!s) {
                toastr.error("Station not found!");
                return;
            }

            // Fill form fields
            $("input[name='name']").val(s.name);
            $("#phone").val(s.phone || '');
            $("#location").val(s.location || '');
            $("#city").val(s.city || '');
            $("#working_capital").val(s.working_capital || '');
			$("#local").prop('checked', s.local ? true : false);
            $("#coordinates").val(s.coordinates || '');
			

            // Load existing products (if any)
            $.get(`api/stations/${id}/products`, function (products) {
                console.log('Station products with prices:', products);
                $("#productTable tbody").empty();
                productIndex = 0;

                if (products && products.length > 0) {
                    // Remove duplicates
                    const uniqueProducts = [];
                    const seenIds = new Set();

                    products.forEach(p => {
                        if (!seenIds.has(p.station_product_id)) {
                            seenIds.add(p.station_product_id);
                            uniqueProducts.push(p);
                        }
                    });

                    console.log('Unique products:', uniqueProducts);

                    setTimeout(() => {
                        uniqueProducts.forEach((p, i) => {
                            addProductRowWithSelection(p.id, p.price, p.effective_from, p.effective_to);
                        });
                    }, 100);

                } else {
                    // Agar products nahi hain to empty row bhi nahi add karenge
                    // User khud add karega agar chahe to
                }
            }).fail(() => {
                console.error('Failed to load station products');
                toastr.error("Failed to load station products.");
            });

        }).fail((xhr) => {
            console.error('Failed to load station:', xhr.responseText);
            toastr.error("Failed to load station data for editing.");
        });
    }

    function addProductRowWithSelection(productId, price, effectiveFrom, effectiveTo) {
        let options = `<option value="">Select Product</option>`;
        productsData.forEach(p => {
            const selected = p.id == productId ? 'selected' : '';
            options += `<option value="${p.id}" ${selected}>${p.name}</option>`;
        });

        const today = new Date().toISOString().slice(0, 16);
        const effectiveDateTime = effectiveFrom ?
            new Date(effectiveFrom).toISOString().slice(0, 16) : today;

        const effectiveToDateTime = effectiveTo ?
            new Date(effectiveTo).toISOString().slice(0, 16) : '';

        const row = `
                <tr>
                    <td>
                        <select name="products[${productIndex}][productid]" class="form-select product-select">
                            ${options}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="products[${productIndex}][price]" 
                               class="form-control price-input" step="0.01" min="0" 
                               placeholder="0.00" value="${price || ''}">
                    </td>
                    <td>
                        <input type="datetime-local" name="products[${productIndex}][effective_from]" 
                               class="form-control datetime-input" 
                               value="${effectiveDateTime}">
                    </td>
                    <td>
                        <input type="datetime-local" name="products[${productIndex}][effective_to]" 
                               class="form-control datetime-input" 
                               value="${effectiveToDateTime}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm removeRow">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

        $("#productTable tbody").append(row);

        // Initialize Choices.js
        const newSelect = $("#productTable tbody tr:last select")[0];
        if (newSelect && typeof Choices !== 'undefined') {
            setTimeout(() => {
                const choices = new Choices(newSelect, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false,
                    removeItemButton: true,
                    allowHTML: false
                });

                if (productId) {
                    setTimeout(() => {
                        choices.setChoiceByValue(productId.toString());
                    }, 50);
                }
            }, 50);
        }

        productIndex++;
    }

    // ✅ Add a new product row (Optional)
    function addProductRow() {
        let options = `<option value="">Select Product</option>`;
        productsData.forEach(p => {
            options += `<option value="${p.id}">${p.name}</option>`;
        });

        const today = new Date().toISOString().slice(0, 16);

        const row = `
                <tr>
                    <td>
                        <select name="products[${productIndex}][productid]" class="form-select product-select">
                            ${options}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="products[${productIndex}][price]" 
                               class="form-control price-input" step="0.01" min="0" 
                               placeholder="0.00">
                    </td>
                    <td>
                        <input type="datetime-local" name="products[${productIndex}][effective_from]" 
                               class="form-control datetime-input" 
                               value="${today}">
                    </td>
                    <td>
                        <input type="datetime-local" name="products[${productIndex}][effective_to]" 
                               class="form-control datetime-input">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm removeRow">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

        $("#productTable tbody").append(row);

        // Initialize Choices.js
        const newSelect = $("#productTable tbody tr:last select")[0];
        if (newSelect && typeof Choices !== 'undefined') {
            setTimeout(() => {
                new Choices(newSelect, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false,
                    removeItemButton: true,
                    allowHTML: false
                });
            }, 50);
        }

        productIndex++;
    }

    // ✅ Remove a product row
    $(document).on("click", ".removeRow", function () {
        $(this).closest("tr").remove();
        // Empty table check - agar koi row nahi hai to koi issue nahi
    });

    // ✅ Handle Create / Update Form Submit
    $("#stationForm").submit(function (e) {
        e.preventDefault();
        console.log('Form submitted');

        const $btn = $('button[type="submit"]');
        const $btnText = $('#submitBtnText');
        const $spinner = $('#submitSpinner');

        $btn.prop('disabled', true);
        $btnText.text(stationId ? 'Updating...' : 'Creating...');
        $spinner.removeClass('d-none');

        const coords = $("#coordinates").val().split(',');
        const formData = {
            user_id: AUTH_USER_ID,
            name: $("input[name='name']").val(),
            phone: $("#phone").val(),
            location: $("#location").val(),
            city: $("#city").val(),
            lat: coords[0] || null,
            lng: coords[1] || null,
			    working_capital : $("#working_capital").val(),
            products: [] // ✅ Empty array - products optional
        };

        console.log('Form data collected:', formData);

        // ✅ Validate required fields (products nahi)
        if (!formData.name) {
            toastr.error("Please enter Station Name");
            resetSubmitButton($btn, $btnText, $spinner, stationId);
            return;
        }

        // ✅ Collect products (if any)
        $("#productTable tbody tr").each(function () {
            const productid = $(this).find("select").val();
            const price = $(this).find(".price-input").val();
            const effective_from = $(this).find("input[name*='effective_from']").val();
            const effective_to = $(this).find("input[name*='effective_to']").val();

            console.log('📦 Product Data:', {
                productid,
                price,
                effective_from,
                effective_to
            });

            // ✅ Only add if both product and price are provided
            if (productid && price) {
                formData.products.push({
                    product_id: parseInt(productid),
                    price: parseFloat(price),
                    effective_from: effective_from ?
                        effective_from.replace('T', ' ') + ':00' :
                        new Date().toISOString().slice(0, 19).replace('T', ' '),
                    effective_to: effective_to ?
                        effective_to.replace('T', ' ') + ':00' :
                        null
                });
            }
        });

        // ✅ Products are optional, so no validation needed
        // Station create ho jayega chahe products ho ya na ho

        const url = stationId ? `api/stations/${stationId}` : "api/stations";
        const method = stationId ? "PUT" : "POST";

        console.log('Sending request:', { url, method, data: formData });

        $.ajax({
            url: url,
            method: method,
            contentType: "application/json",
            data: JSON.stringify(formData),
            success: function (response) {
                console.log('Success response:', response);
                toastr.success(stationId ? "Station updated successfully!" : "Station created successfully!");

                resetSubmitButton($btn, $btnText, $spinner, stationId);

                setTimeout(() => {
                    window.location.href = "{{ url('station-sites') }}";
                }, 1500);
            },
            error: function (xhr, status, error) {
                console.error('Error response:', {
                    status: xhr.status,
                    responseText: xhr.responseText,
                    error: error
                });

                const errorMsg = xhr.responseJSON?.message || xhr.responseText || "Error saving station";
                toastr.error("Error: " + errorMsg);

                resetSubmitButton($btn, $btnText, $spinner, stationId);
            }
        });
    });

    // ✅ Reset submit button state
    function resetSubmitButton($btn, $btnText, $spinner, stationId) {
        $btn.prop('disabled', false);
        $btnText.text(stationId ? 'Update Station' : 'Create Station');
        $spinner.addClass('d-none');
    }

    // ✅ Init Everything
    $(document).ready(function () {
        // Toastr configuration
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: false,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            timeOut: "5000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut"
        };

        stationId = getQueryParam('id');
        console.log('Station ID:', stationId);

        // Update page titles safely
        if (stationId) {
            const pageTitle = document.querySelector('.pagetitle');
            const title = document.querySelector('title');
            const submitBtn = document.querySelector('#submitBtnText');

            if (pageTitle) pageTitle.textContent = 'Edit Station';
            if (title) title.textContent = 'Edit Station';
            if (submitBtn) submitBtn.textContent = 'Update Station';
        }

        loadProducts();
        $("#addProduct").click(addProductRow);

        if (stationId) {
            setTimeout(() => loadStationForEdit(stationId), 500);
        }
        
        // ✅ Table ko empty rakhen create mode mein
        if (!stationId) {
            $("#productTable tbody").empty();
        }
    });
</script>
@endsection