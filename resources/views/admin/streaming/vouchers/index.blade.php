@extends('admin.layouts.stisla')

@section('title', 'Kelola Voucher Live')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h3 class="card-title">Daftar Voucher Live Streaming</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.streaming.vouchers.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Voucher
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Live Stream</th>
                                    <th>Tipe Diskon</th>
                                    <th>Nilai Diskon</th>
                                    <th>Periode</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($vouchers as $voucher)
                                    <tr>
                                        <td>{{ $voucher->code }}</td>
                                        <td>{{ $voucher->liveStream->title ?? '-' }}</td>
                                        <td>{{ $voucher->discount_type === 'percentage' ? 'Persentase' : 'Nominal' }}</td>
                                        <td>
                                            {{ $voucher->discount_type === 'percentage' ? $voucher->discount_value . '%' : 'Rp ' . number_format($voucher->discount_value, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            {{ $voucher->start_time->format('d M Y H:i') }} -
                                            {{ $voucher->end_time->format('d M Y H:i') }}
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $voucher->active ? 'success' : 'danger' }}">
                                                {{ $voucher->active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.streaming.vouchers.toggle-status', $voucher) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-{{ $voucher->active ? 'warning' : 'success' }}" onclick="return confirm('Yakin ingin {{ $voucher->active ? 'menonaktifkan' : 'mengaktifkan' }} voucher ini?')">
                                                    <i class="fas fa-{{ $voucher->active ? 'times' : 'check' }}"></i>
                                                    {{ $voucher->active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.streaming.vouchers.edit', $voucher) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.streaming.vouchers.destroy', $voucher) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus voucher ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data voucher</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $vouchers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection