@extends('layout')
@section('title', 'Product Catalog - eseCRM')

@section('content')
<section class="task__section">
    @include('inc.header', ['title' => 'Product Catalog'])

    <div class="dash-container">
        <div class="leads-toolbar mb-3">
            <div class="leads-toolbar-left">
                <form action="/products" method="GET" class="d-flex align-items-center gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Search products, SKU..." value="{{ request('search') }}" style="width: 250px;">
                    <button type="submit" class="lb-btn lb-btn-secondary">Search</button>
                </form>
            </div>
            <div class="leads-toolbar-right">
                <button class="lb-btn lb-btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bx bx-plus"></i> Add Product
                </button>
            </div>
        </div>

        <div class="dash-card">
            <div class="table-responsive">
                <table class="leads-table align-middle">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="fw-bold text-primary">{{ $product->sku ?? 'N/A' }}</td>
                            <td>
                                <div class="fw-600">{{ $product->name }}</div>
                                <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $product->category ?? 'General' }}</span></td>
                            <td class="fw-bold">₹{{ number_format($product->price, 2) }}</td>
                            <td>
                                @if($product->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn kb-action-btn edit-product" data-id="{{ $product->id }}" title="Edit">
                                        <i class="bx bx-pencil"></i>
                                    </button>
                                    <button class="btn kb-action-btn kb-action-del delete-product" data-id="{{ $product->id }}" title="Delete">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bx bx-package fs-1 text-muted d-block mb-2"></i>
                                No products found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</section>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-600">Product Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Website Development">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">SKU</label>
                            <input type="text" name="sku" class="form-control" placeholder="WEB-001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="price" class="form-control" step="0.01" required placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Category</label>
                        <select name="category" class="form-select">
                            <option value="Services">Services</option>
                            <option value="Software">Software</option>
                            <option value="Hardware">Hardware</option>
                            <option value="Consulting">Consulting</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-600">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Details about the product..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="lb-btn lb-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="lb-btn lb-btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editProductForm" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label fw-600">Product Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">SKU</label>
                            <input type="text" name="sku" id="edit_sku" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="price" id="edit_price" class="form-control" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Category</label>
                        <select name="category" id="edit_category" class="form-select">
                            <option value="Services">Services</option>
                            <option value="Software">Software</option>
                            <option value="Hardware">Hardware</option>
                            <option value="Consulting">Consulting</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-600">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="lb-btn lb-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="lb-btn lb-btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.edit-product').on('click', function() {
        var id = $(this).data('id');
        $.get('/products/get/' + id, function(data) {
            $('#edit_id').val(data.id);
            $('#edit_name').val(data.name);
            $('#edit_sku').val(data.sku);
            $('#edit_price').val(data.price);
            $('#edit_category').val(data.category);
            $('#edit_description').val(data.description);
            $('#editProductForm').attr('action', '/products/update/' + id);
            $('#editProductModal').modal('show');
        });
    });

    $('.delete-product').on('click', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This product will be deleted permanently!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#163f7a',
            cancelButtonColor: '#fe0201',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/products/delete/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        location.reload();
                    }
                });
            }
        });
    });
});
</script>
@endsection
