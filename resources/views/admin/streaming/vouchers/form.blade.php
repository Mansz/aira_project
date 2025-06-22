@extends('admin.layouts.stisla')

@section('title', isset($voucher) ? 'Edit Voucher' : 'Tambah Voucher')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($voucher) ? 'Edit Voucher' : 'Tambah Voucher Baru' }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ isset($voucher) ? route('admin.streaming.vouchers.update', $voucher) : route('admin.streaming.vouchers.store') }}" 
                          method="POST">
                        @csrf
                        @if(isset($voucher))
                            @method('PUT')
                        @endif

                        <div class="form-group">
                            <label for="live_stream_id">Live Stream</label>
                            <select name="live_stream_id" 
                                    id="live_stream_id" 
                                    class="form-control @error('live_stream_id') is-invalid @enderror" 
                                    required>
                                <option value="">Pilih Live Stream</option>
                                @foreach($liveStreams as $stream)
                                    <option value="{{ $stream->id }}" 
                                            {{ (old('live_stream_id', $voucher->live_stream_id ?? '') == $stream->id) ? 'selected' : '' }}>
                                        {{ $stream->title }} ({{ $stream->scheduled_at->format('d M Y H:i') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('live_stream_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="code">Kode Voucher</label>
                            <input type="text" 
                                   name="code" 
                                   id="code" 
                                   class="form-control @error('code') is-invalid @enderror" 
                                   value="{{ old('code', $voucher->code ?? '') }}" 
                                   required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="discount_type">Tipe Diskon</label>
                            <select name="discount_type" 
                                    id="discount_type" 
                                    class="form-control @error('discount_type') is-invalid @enderror" 
                                    required>
                                <option value="percentage" {{ (old('discount_type', $voucher->discount_type ?? '') == 'percentage') ? 'selected' : '' }}>
                                    Persentase (%)
                                </option>
                                <option value="amount" {{ (old('discount_type', $voucher->discount_type ?? '') == 'amount') ? 'selected' : '' }}>
                                    Nominal (Rp)
                                </option>
                            </select>
                            @error('discount_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="discount_value">Nilai Diskon</label>
                            <div class="input-group">
                                <input type="number" 
                                       name="discount_value" 
                                       id="discount_value" 
                                       class="form-control @error('discount_value') is-invalid @enderror" 
                                       value="{{ old('discount_value', $voucher->discount_value ?? '') }}" 
                                       required
                                       step="0.01"
                                       min="0">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="discount-addon">
                                        <span id="discount-symbol">%</span>
                                    </span>
                                </div>
                                @error('discount_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="start_time">Waktu Mulai</label>
                            <input type="datetime-local" 
                                   name="start_time" 
                                   id="start_time" 
                                   class="form-control @error('start_time') is-invalid @enderror" 
                                   value="{{ old('start_time', isset($voucher) ? $voucher->start_time->format('Y-m-d\TH:i') : '') }}" 
                                   required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="end_time">Waktu Berakhir</label>
                            <input type="datetime-local" 
                                   name="end_time" 
                                   id="end_time" 
                                   class="form-control @error('end_time') is-invalid @enderror" 
                                   value="{{ old('end_time', isset($voucher) ? $voucher->end_time->format('Y-m-d\TH:i') : '') }}" 
                                   required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea name="description" 
                                      id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3">{{ old('description', $voucher->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                {{ isset($voucher) ? 'Update Voucher' : 'Buat Voucher' }}
                            </button>
                            <a href="{{ route('admin.streaming.vouchers.index') }}" class="btn btn-secondary">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('discount_type').addEventListener('change', function() {
    const symbol = document.getElementById('discount-symbol');
    symbol.textContent = this.value === 'percentage' ? '%' : 'Rp';
});
</script>
@endpush