@extends('admin.layouts.stisla')

@section('title', 'Tambah Voucher Live')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah Voucher Live Streaming</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.streaming.vouchers.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="live_stream_id">Live Stream</label>
                            <select name="live_stream_id" id="live_stream_id" class="form-control @error('live_stream_id') is-invalid @enderror" required>
                                <option value="">Pilih Live Stream</option>
                                @foreach($liveStreams as $stream)
                                    <option value="{{ $stream->id }}">{{ $stream->title }} ({{ $stream->scheduled_at->format('d M Y H:i') }})</option>
                                @endforeach
                            </select>
                            @error('live_stream_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="code">Kode Voucher</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="discount_type">Tipe Diskon</label>
                            <select name="discount_type" class="form-control @error('discount_type') is-invalid @enderror" required>
                                <option value="percentage">Persentase</option>
                                <option value="amount">Nominal</option>
                            </select>
                            @error('discount_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="discount_value">Nilai Diskon</label>
                            <input type="number" name="discount_value" class="form-control @error('discount_value') is-invalid @enderror" required>
                            @error('discount_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="start_time">Waktu Mulai</label>
                            <input type="datetime-local" name="start_time" class="form-control @error('start_time') is-invalid @enderror" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="end_time">Waktu Selesai</label>
                            <input type="datetime-local" name="end_time" class="form-control @error('end_time') is-invalid @enderror" required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection