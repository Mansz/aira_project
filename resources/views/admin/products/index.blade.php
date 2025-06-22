@extends('admin.layouts.stisla')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2 class="mb-0">Manajemen Produk</h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.products.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Tambah Produk Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>SKU</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="img-thumbnail"
                                             style="max-width: 50px;">
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                </td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->sku }}</td>
                                <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge badge-{{ $product->stock > 0 ? 'success' : 'danger' }}">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.products.edit', $product) }}">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" class="dropdown-item text-danger" 
                                                    onclick="confirmDelete('{{ $product->id }}')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                            <div class="dropdown-divider"></div>
                                            <button type="button" class="dropdown-item" 
                                                    onclick="updateStock('{{ $product->id }}', '{{ $product->stock }}')">
                                                <i class="fas fa-boxes"></i> Update Stok
                                            </button>
                                        </div>
                                    </div>

                                    <form id="delete-form-{{ $product->id }}" 
                                          action="{{ route('admin.products.destroy', $product) }}" 
                                          method="POST" 
                                          style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada produk yang tersedia.</td>
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
</div>

<!-- Stock Update Modal -->
<div class="modal fade" id="stockUpdateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stok</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="number" id="stockInput" class="form-control" min="0">
                <input type="hidden" id="productId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveStock()">Simpan</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmDelete(productId) {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        document.getElementById('delete-form-' + productId).submit();
    }
}

function updateStock(productId, currentStock) {
    document.getElementById('productId').value = productId;
    document.getElementById('stockInput').value = currentStock;
    $('#stockUpdateModal').modal('show');
}

function saveStock() {
    const productId = document.getElementById('productId').value;
    const newStock = document.getElementById('stockInput').value;

    $.ajax({
        url: `/admin/products/${productId}/stock`,
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            stock: newStock
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Failed to update stock');
            }
        },
        error: function() {
            alert('An error occurred while updating stock');
        }
    });
}
</script>
@endpush