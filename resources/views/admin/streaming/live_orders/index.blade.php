@extends('admin.layouts.stisla')

@section('title', 'Riwayat Pesanan Live')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Pesanan Live Streaming</h3>
                    <div class="card-tools">
                        <form action="{{ route('admin.streaming.orders.export') }}" method="GET" class="d-inline">
                            @if(request()->has('live_stream_id'))
                                <input type="hidden" name="live_stream_id" value="{{ request('live_stream_id') }}">
                            @endif
                            @if(request()->has('start_date'))
                                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                            @endif
                            @if(request()->has('end_date'))
                                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                            @endif
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form action="{{ route('admin.streaming.orders.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="live_stream_id">Live Stream</label>
                                    <select name="live_stream_id" id="live_stream_id" class="form-control">
                                        <option value="">Semua Live Stream</option>
                                        @foreach($liveStreams as $stream)
                                            <option value="{{ $stream->id }}" 
                                                {{ request('live_stream_id') == $stream->id ? 'selected' : '' }}>
                                                {{ $stream->title }} ({{ $stream->scheduled_at->format('d M Y H:i') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Tanggal Mulai</label>
                                    <input type="date" 
                                           name="start_date" 
                                           id="start_date" 
                                           class="form-control"
                                           value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">Tanggal Akhir</label>
                                    <input type="date" 
                                           name="end_date" 
                                           id="end_date" 
                                           class="form-control"
                                           value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                        <a href="{{ route('admin.streaming.orders.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-sync"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Orders Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Live Stream</th>
                                    <th>Pembeli</th>
                                    <th>Total</th>
                                    <th>Voucher</th>
                                    <th>Diskon</th>
                                    <th>Total Akhir</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($liveOrders as $order)
                                <tr>
                                    <td>{{ $order->order->order_number }}</td>
                                    <td>{{ $order->liveStream->title }}</td>
                                    <td>{{ $order->buyer->name }}</td>
                                    <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if($order->voucher)
                                            <span class="badge badge-info">{{ $order->voucher->code }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($order->getFinalAmount(), 0, ',', '.') }}</td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order->order_id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data pesanan</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $liveOrders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table td {
        vertical-align: middle;
    }
</style>
@endpush