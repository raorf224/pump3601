@extends('partials.layouts.master')

@section('title', 'Product Setup | ' . Auth::user()->full_name)
@section('title-sub', 'Inventory')
@section('pagetitle', 'Product Management')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

<style>
/* ✅ Remove all scrollbars */
.table-responsive {
    overflow-x: hidden !important;
    overflow-y: hidden !important;
}

/* ✅ Keep table content in single line and clean borderless look */
#productTable {
    width: 100% !important;
    border-collapse: collapse !important;
}

#productTable td,
#productTable th {
    white-space: nowrap !important;
    border: none !important;
}

#productTable tr {
    border-bottom: 1px solid #eee !important;
}
</style>
@endsection

@section('content')
<div id="layout-wrapper">
    <div class="container-fluid mt-4">
        <div class="card shadow-sm">
            <div class="card-body">

                <!-- ✅ Toast -->
                <div class="position-fixed top-0 end-0 p-3" style="z-index:1055;">
                    <div id="toastContainer"></div>
                </div>

                <!-- ✅ Accordion -->
                <div class="accordion accordion-primary accordion-border-box mb-4" id="productAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingProductForm">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#productFormCollapse" aria-expanded="true"
                                aria-controls="productFormCollapse">
                                <i class="bi bi-box me-2"></i> Add / Edit Product
                            </button>
                        </h2>

                        <div id="productFormCollapse" class="accordion-collapse collapse show"
                            data-bs-parent="#productAccordion">
                            <div class="accordion-body">
                                <form id="product_form" enctype="multipart/form-data">

                                    <div class="row mb-3">
                                        <!-- ✅ Store -->
                                        <div class="col-md-3">
                                            <label class="form-label required-label">Store</label>
                                            <select class="form-select" id="store_select" name="store_id"
                                                required></select>
                                        </div>

                                        <!-- ✅ Category -->
                                        <div class="col-md-3">
                                            <label class="form-label required-label">Category</label>
                                            <select class="form-select" id="category_select" name="category_id"
                                                required></select>
                                            <button type="button" id="addCategoryBtn"
                                                class="btn btn-sm btn-outline-primary mt-2 w-100">+ Add New
                                                Category</button>
                                        </div>

                                        <!-- ✅ Product Name -->
                                        <div class="col-md-3">
                                            <label class="form-label required-label">Product Name</label>
                                            <input type="text" class="form-control" name="product_name"
                                                id="product_name" placeholder="Enter product name" required>
                                        </div>

                                        <!-- ✅ Status -->
                                        <div class="col-md-3">
                                            <label class="form-label required-label">Status</label>
                                            <select class="form-select" name="status" id="status" required>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label required-label">Quantity</label>
                                            <input type="number" class="form-control" name="quantity" id="quantity"
                                                placeholder="Enter quantity" min="0">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label required-label">Price (PKR)</label>
                                            <input type="number" class="form-control" name="unit_price" id="unit_price"
                                                placeholder="Enter price" min="0" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Product Image</label>
                                            <input type="file" class="form-control" name="product_image"
                                                id="product_image" accept=".jpg,.jpeg,.png,.webp">
                                            <img id="imagePreview" src="#" alt="Preview" class="mt-2 rounded"
                                                style="display:none;width:80px;height:80px;object-fit:cover;">
                                        </div>
                                    </div>

                                    <input type="hidden" name="product_id" id="product_id">

                                    <div class="d-flex justify-content-end">
                                        <button type="button" id="resetBtn" class="btn btn-light me-2">Reset</button>
                                        <button type="submit" class="btn btn-primary">Save Product</button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ✅ Product Table -->
                <h5 class="card-title mb-3">Product Records</h5>
                <div class="table-responsive">
                    <table id="productTable" class="table align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Store</th>
                                <th>Category</th>
                                <th>Product</th>
                                <th>Image</th>
                                <th>Quantity</th>
                                <th>Price (PKR)</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
</main>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
let productTable, storeSelect, categorySelect, productFormCollapse;
const AUTH_USER_ID = "{{ Auth::id() }}";
let userPermissions = [];

$(document).ready(function() {

    $.get(`/api/getpermissionbyuserid/{{Auth::user()->id}}/{{Auth::user()->role}}`, function(permissions) {
        userPermissions = permissions;
        console.log("Loaded permissions:", userPermissions);



        // Hide Add button if not allowed
        if (!hasPermission('product_setup', 'create')) {
            $('#productAccordion').hide();
        }


    });
    const collapseEl = document.getElementById('productFormCollapse');
    productFormCollapse = collapseEl ? new bootstrap.Collapse(collapseEl, {
        toggle: false
    }) : null;

    storeSelect = new Choices('#store_select', {
        searchPlaceholderValue: 'Search store...',
        itemSelectText: ''
    });
    categorySelect = new Choices('#category_select', {
        searchPlaceholderValue: 'Search category...',
        itemSelectText: ''
    });

    loadStores();
    loadCategories();
    initDataTable();

    $('#product_image').on('change', previewImage);
    $('#addCategoryBtn').on('click', createCategory);
    $('#product_form').on('submit', submitProductForm);
    $('#resetBtn').on('click', resetForm);
});

// ✅ Preview image
function previewImage() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => $('#imagePreview').attr('src', e.target.result).show();
        reader.readAsDataURL(file);
    } else $('#imagePreview').hide();
}

// ✅ Load stores
function loadStores(selected = null) {
    $.get(`/api/user-store/${AUTH_USER_ID}`, function(res) {
        storeSelect.clearChoices();
        const options = res.map(s => ({
            value: s.id,
            label: `${s.store_name} (${s.station_name})`,
            selected: s.id === selected
        }));
        storeSelect.setChoices(options, 'value', 'label', true);
    });
}

// ✅ Load categories
function loadCategories(selected = null) {
    $.get('/api/category', function(res) {
        categorySelect.clearChoices();
        const options = res.map(c => ({
            value: c.id,
            label: c.name,
            selected: c.id === selected
        }));
        categorySelect.setChoices(options, 'value', 'label', true);
    });
}

// ✅ Create category (no duplicates) and auto-select new one
function createCategory() {
    const newCat = prompt("Enter new category name:");
    if (!newCat) return;

    $.get('/api/category', function(res) {
        const exists = res.some(c => c.name.toLowerCase() === newCat.toLowerCase());
        if (exists) {
            showToast("Category already exists!", "warning");
            const existing = res.find(c => c.name.toLowerCase() === newCat.toLowerCase());
            categorySelect.setChoiceByValue(existing.id);
            return;
        }

        $.ajax({
            url: '/api/category',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                name: newCat
            }),
            success: (res) => {
                showToast("Category added successfully!", "success");
                loadCategories(res.id);
                setTimeout(() => categorySelect.setChoiceByValue(res.id), 600);
            },
            error: () => showToast("Error adding category!", "danger")
        });
    });
}

// ✅ Initialize DataTable
function initDataTable() {
    productTable = $('#productTable').DataTable({
            ajax: {
                url: `/api/store-product/${AUTH_USER_ID}`,
                dataSrc: ""
            },
            destroy: true,
            columns: [{
                    data: 'id'
                },
                {
                    data: 'store_name'
                },
                {
                    data: 'category_name'
                },
                {
                    data: 'product_name'
                },
                {
                    data: 'product_image',
                    render: img => img ?
                        `<img src="/${img}" width="60" height="60" class="rounded border">` : '—'
                },
                {
                    data: 'quantity'
                },
                {
                    data: 'unit_price'
                },
                {
                    data: 'status',
                    render: s =>
                        `<span class="badge bg-${s === 'active' ? 'success' : 'secondary'}">${s}</span>`
                },
                {
                    data: null,
                    render: function(row) {
                        let buttons = '';

                        if (hasPermission('product_setup', 'update')) {
                            buttons += `
            <button class="btn btn-sm btn-primary me-1" onclick="editProduct(${row.id})" title="Edit">
                <i class="bi bi-pencil-square"></i>
            </button>`;
                        }

                        if (hasPermission('product_setup', 'delete')) {
                            buttons += `
            <button class="btn btn-sm btn-danger" onclick="deleteProduct(${row.id})" title="Delete">
                <i class="bi bi-trash"></i>
            </button>`;
                        }

                        return buttons ?
                            `<div class="d-flex justify-content-center align-items-center gap-2">${buttons}</div>` :
                            `<span class="text-muted small">No actions</span>`;
                    }
                }



                ]
            });
    }

    // ✅ Save or Update Product
    function submitProductForm(e) {
        e.preventDefault();
        const id = $('#product_id').val();
        const formData = new FormData($('#product_form')[0]);
        const method = id ? 'POST' : 'POST';
        if (id) formData.append('_method', 'PUT');
        const url = id ? `/api/store-product/${id}` : '/api/store-product';

        $.ajax({
            url,
            method,
            data: formData,
            processData: false,
            contentType: false,
            success: () => {
                showToast(id ? "Product updated!" : "Product added!", "success");
                resetForm();
                productTable.ajax.reload();
            },
            error: (xhr) => {
                console.log(xhr.responseText);
                showToast("Error saving product!", "danger");
            }
        });
    }

    // ✅ Edit product
    function editProduct(id) {
        $.get(`/api/get-product/${id}`, function(res) {
            $('#product_id').val(res.id);
            $('#product_name').val(res.product_name);
            $('#quantity').val(res.quantity);
            $('#unit_price').val(res.unit_price);
            $('#status').val(res.status);
            loadStores(res.store_id);
            loadCategories(res.category_id);

            if (res.product_image)
                $('#imagePreview').attr('src', '/' + res.product_image).show();
            else
                $('#imagePreview').hide();

            if (productFormCollapse) productFormCollapse.show();
            $('html, body').animate({
                scrollTop: $('#product_form').offset().top - 100
            }, 300);
        });
    }

    // ✅ Delete product
    function deleteProduct(id) {
        if (!confirm("Are you sure you want to delete this product?")) return;
        $.ajax({
            url: `/api/store-product/${id}`,
            method: 'DELETE',
            success: () => {
                showToast("Product deleted successfully!", "success");
                productTable.ajax.reload();
            },
            error: () => showToast("Error deleting product!", "danger")
        });
    }

    // ✅ Reset form
    function resetForm() {
        $('#product_form')[0].reset();
        $('#product_id').val('');
        $('#imagePreview').hide();
        loadStores();
        loadCategories();
        if (productFormCollapse) productFormCollapse.show();
    }

    // ✅ Toast notification
    function showToast(msg, type = 'info') {
        $('.toast').remove();
        const toast = $(`<div class="toast align-items-center text-white bg-${type} border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>`);
        $('#toastContainer').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();
        setTimeout(() => toast.remove(), 3000);
    }

    function hasPermission(moduleName, action) {
        const module = userPermissions.find(p => p.name === moduleName);
        if (!module) return false;
        return module[action] == 1;
    }
</script>
@endsection