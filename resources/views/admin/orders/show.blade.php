@extends('admin.layouts.stisla')

@section('content')
<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4">Detail Pesanan #{{ $order->id }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Status Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Status Pesanan</h5>
                            <p class="text-muted mb-0">{{ $order->created_at->format('d F Y H:i') }}</p>
                        </div>
                        <div class="dropdown">
                            <button class="btn dropdown-toggle border-0"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    style="background-color: {{ $order->status_color }}20; color: {{ $order->status_color }};">
                                <span class="me-1">{{ $order->status_icon }}</span>
                                {{ $order->status }}
                            </button>
                            <ul class="dropdown-menu">
                                @foreach(\App\Models\Order::getStatusList() as $status => $label)
                                    <li>
                                        <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $status }}">
                                            <button type="submit" class="dropdown-item" data-confirm-status="{{ $label }}">
                                                {{ $label }}
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Customer & Shipping Info -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Informasi Pelanggan</h5>
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted mb-1">Nama</label>
                        <p class="mb-0">{{ $order->user->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted mb-1">Email</label>
                        <p class="mb-0">{{ $order->user->email }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted mb-1">No. Telepon</label>
                        <p class="mb-0">{{ $order->user->phone ?? '-' }}</p>
                    </div>
                    <h5 class="card-title mt-4">Alamat Pengiriman</h5>
                    <hr>
                    <p class="mb-0">{{ $order->shipping_address }}</p>
                    @if($order->tracking_number)
                        <div class="mt-3">
                            <label class="text-muted mb-1">No. Resi</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $order->tracking_number }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ $order->tracking_number }}')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Detail Produk</h5>
                    <hr>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $item->product->image_url }}" 
                                                     alt="{{ $item->product->name }}"
                                                     class="rounded me-3"
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0">{{ $item->product->name }}</h6>
                                                    @if($item->notes)
                                                        <small class="text-muted">{{ $item->notes }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ $item->formatted_price }}</td>
                                        <td class="text-end">{{ $item->formatted_subtotal }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                                    <td class="text-end"><strong>{{ $order->formatted_total }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Informasi Pembayaran</h5>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted mb-1">Metode Pembayaran</label>
                            <p class="mb-0">{{ $order->payment_method }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <label class="text-muted mb-1">Status</label>
                            <p class="mb-0">
                                @if($order->paymentProof)
                                    @if($order->paymentProof->is_verified)
                                        <span class="text-success">
                                            <i class="fas fa-check-circle me-1"></i>Terverifikasi
                                        </span>
                                    @else
                                        <span class="text-warning">
                                            <i class="fas fa-clock me-1"></i>Menunggu Verifikasi
                                        </span>
                                    @endif
                                @else
                                    <span class="text-danger">
                                        <i class="fas fa-times-circle me-1"></i>Belum Dibayar
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($order->paymentProof)
                        <div class="text-center">
                            <img src="{{ $order->paymentProof->image_url }}" 
                                 alt="Bukti Pembayaran"
                                 class="img-fluid rounded"
                                 style="max-height: 400px;">
                            
                            @if(!$order->paymentProof->is_verified)
                                <div class="mt-3">
                                    <form action="{{ route('admin.orders.verifyPayment', $order) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success me-2">
                                            <i class="fas fa-check me-2"></i>Verifikasi Pembayaran
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectPaymentModal">
                                        <i class="fas fa-times me-2"></i>Tolak Pembayaran
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Shipping Proof Upload -->
            @if(in_array($order->status, ['Diproses', 'Dikirim']))
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Upload Bukti Pengiriman</h5>
                        <hr>
                        <form action="{{ route('admin.orders.uploadShippingProof', $order) }}" 
                              method="POST" 
                              enctype="multipart/form-data"
                              class="row g-3">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label">No. Resi</label>
                                <input type="text" 
                                       name="tracking_number" 
                                       class="form-control @error('tracking_number') is-invalid @enderror"
                                       value="{{ old('tracking_number', $order->tracking_number) }}"
                                       required>
                                @error('tracking_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kurir</label>
                                <input type="text" 
                                       name="shipping_courier" 
                                       class="form-control @error('shipping_courier') is-invalid @enderror"
                                       value="{{ old('shipping_courier', $order->shipping_courier) }}"
                                       required>
                                @error('shipping_courier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Bukti Pengiriman</label>
                                <input type="file" 
                                       name="shipping_proof" 
                                       class="form-control @error('shipping_proof') is-invalid @enderror"
                                       accept="image/*"
                                       {{ !$order->shipping_proof_path ? 'required' : '' }}>
                                @error('shipping_proof')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @if($order->shipping_proof_path)
                                <div class="col-12">
                                    <img src="{{ $order->shipping_proof_url }}" 
                                         alt="Bukti Pengiriman"
                                         class="img-fluid rounded"
                                         style="max-height: 200px;">
                                </div>
                            @endif
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>Upload
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Reject Payment Modal -->
<div class="modal fade" id="rejectPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.orders.rejectPayment', $order) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan</label>
                        <textarea name="notes" 
                                  class="form-control @error('notes') is-invalid @enderror" 
                                  rows="3" 
                                  required></textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'No. Resi berhasil disalin',
                timer: 1500,
                showConfirmButton: false
            });
        });
    }

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
