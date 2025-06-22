@extends('admin.layouts.stisla')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Daftar Pesanan</h1>
    </div>

    <div class="section-body">
        <div class="container-fluid px-4">
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                @foreach(\App\Models\Order::getStatusList() as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ request('date') }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cari</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="ID Pesanan / Nama Pelanggan" value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        @if(request()->hasAny(['status', 'date', 'search']))
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary d-block">
                                    Reset Filter
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pelanggan</th>
                                    <th>Produk</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->user->name }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($firstItem = $order->orderItems->first())
                                                    <img src="{{ $firstItem->product->image_url }}" 
                                                        alt="Product" 
                                                        class="rounded me-2"
                                                        style="width: 40px; height: 40px; object-fit: cover;">
                                                    <div>
                                                        <div class="fw-bold">{{ $firstItem->product->name }}</div>
                                                        @if($order->orderItems->count() > 1)
                                                            <small class="text-muted">
                                                                +{{ $order->orderItems->count() - 1 }} produk lainnya
                                                            </small>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $order->formatted_total }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm dropdown-toggle border-0"
                                                        type="button"
                                                        data-bs-toggle="dropdown"
                                                        style="background-color: {{ $order->status_color }}20; color: {{ $order->status_color }};">
                                                    <span class="me-1">{{ $order->status_icon }}</span>
                                                    {{ $order->status }}
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @foreach(\App\Models\Order::getStatusList() as $status => $label)
                                                        <li>
                                                            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="{{ $status }}">
                                                                <button type="submit" class="dropdown-item">
                                                                    {{ $label }}
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">Tidak ada pesanan</h5>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-3">
                        {{ $orders->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div> <!-- container-fluid -->
    </div> <!-- section-body -->
</section>
@endsection

@push('scripts')
<script>
    // Handle status update success message
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ session('success') }}',
            timer: 2000,
            showConfirmButton: false
        });
    @endif

    // Confirm status update
    document.querySelectorAll('[data-confirm-status]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const status = this.dataset.confirmStatus;
            
            Swal.fire({
                title: 'Konfirmasi',
                text: `Apakah Anda yakin ingin mengubah status pesanan menjadi "${status}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
