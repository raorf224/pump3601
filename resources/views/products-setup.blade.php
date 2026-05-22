@extends('partials.layouts.master')

@section('title', 'Products | ' . Auth::user()->full_name)

@section('title-sub', 'Admin')
@section('pagetitle', 'Products')
@section('css')
<link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
<style>
#productsTable tbody td:last-child,
#productsTable thead th:last-child {
    text-align: center;
}
</style>
@endsection

@section('content')
<div id="layout-wrapper">
    <div class="container-fluid mt-4">

        <!-- Products Table -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0 fw-bold">Manage Products</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal"
                        id="add_product">
                        <i class="bi bi-plus-circle"></i> Add Product
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-box table-responsive">
                        <table id="productsTable" class="table text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">S.No</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Product Name</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Unit</th>
                                    <th scope="col">Created_at</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Rows will be injected via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- End container-fluid -->
</div><!-- End layout-wrapper -->

<!-- Add Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="productForm" class="needs-validation" novalidate>
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Create Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category</label>
                        <select name="category" id="category" class="form-select" required>
                            <option value="">Select Category</option>
                            <option value="fuel">Fuel</option>
                            <option value="lubricants">Lubricant</option>
                        </select>
                        <div class="invalid-feedback">Please select category</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                        <div class="invalid-feedback">Please enter product name</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Description</label>
                        <input type="text" name="description" id="description" class="form-control" >
                        <div class="invalid-feedback">Please enter description</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Unit</label>
                        <input type="text" name="unit" id="unit" class="form-control"
                            placeholder="e.g. litre, kg, bottle" required>
                        <div class="invalid-feedback">Please enter unit</div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </div>
        </form>
    </div>
</div>
</main>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


<script>
let userPermissions;
$(document).ready(function() {
    $.get(`/api/getpermissionbyuserid/{{Auth::user()->id}}/{{Auth::user()->role}}`, function(permissions) {
        userPermissions = permissions;
        console.log("Loaded permissions:", userPermissions);

        // ✅ Hide Add button if no create permission
        if (!hasPermission('product_setup', 'create')) {
            $('#add_product').hide();
        }

        // ✅ Load table data after permissions
        loadProducts();

    });

    // ✅ Toast notification function (Fixed)
    function showToast(message, type = 'success') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        if (type === 'success') toastr.success(message);
        else if (type === 'error') toastr.error(message);
        else if (type === 'warning') toastr.warning(message);
        else toastr.info(message);
    }

    function loadProducts() {
        $.ajax({
            url: "api/products",
            method: "GET",
            success: function(response) {

                if ($.fn.DataTable.isDataTable('#productsTable')) {
                    $('#productsTable').DataTable().destroy();
                }
                $('#productsTable tbody').empty();

                response.forEach((product, index) => {

                    // ✅ Permission-based buttons
                    let editBtn = hasPermission('product_setup', 'update') ?
                        `<button type="button" class="btn btn-light-primary btn-sm editProduct" data-id="${product.id}">
                            <i class="bi bi-pencil-square"></i>
                       </button>` :
                        '';

                    let deleteBtn = hasPermission('product_setup', 'delete') ?
                        `<button type="button" class="btn btn-light-danger btn-sm deleteProduct" data-id="${product.id}">
                            <i class="ri-delete-bin-line"></i>
                       </button>` :
                        '';

                    // ✅ Append product row
                    $('#productsTable tbody').append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${product.category}</td>
                        <td>${product.name}</td>
                        <td>${product.description || 'N/A'}</td>
                        <td>${product.unit}</td>
                        <td>${product.created_at}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                          
                                ${deleteBtn}
                            </div>
                        </td>
                    </tr>
                `);
                });


                // ✅ Initialize DataTable again
                $('#productsTable').DataTable({
                    responsive: true,
                    dom: 'rtip',
                    lengthChange: false,
                    ordering: false
                });
            },
            error: function() {
                $('#productsTable tbody').html(
                    '<tr><td colspan="7" class="text-center">Failed to load products</td></tr>');
            }
        });
    }


    // Load products on page load

    // Create product
    $('#productForm').submit(function(e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        let formData = $(this).serialize();

        $.ajax({
            url: "api/createproducts",
            method: "POST",
            data: formData,
            success: function() {
                $('#productModal').modal('hide');
                $('#productForm')[0].reset();
                $('#productForm').removeClass('was-validated');
                loadProducts();

                // ✅ Toast instead of alert
                showToast("Product created successfully!", "success");
            },
            error: function(xhr) {
                showToast("Error: " + xhr.responseText, "error");
            }
        });
    });

    // Delete product
    $(document).on('click', '.deleteProduct', function() {
        let productId = $(this).data('id');

        if (confirm("Are you sure you want to delete this product?")) {
            $.ajax({
                url: `api/products/${productId}`,
                method: "DELETE",
                success: function() {
                    loadProducts();
                    // ✅ Toast instead of alert
                    showToast("Product deleted successfully!", "success");
                },
                error: function() {
                    showToast("Failed to delete product.", "error");
                }
            });
        }
    });
});

function hasPermission(moduleName, action) {
    const module = userPermissions.find(p => p.name === moduleName);
    if (!module) return false;
    return module[action] == 1;
}
</script>

@endsection