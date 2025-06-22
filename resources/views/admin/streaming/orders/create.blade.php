@extends('admin.layouts.stisla')

@section('title', 'Create Live Order')

@section('content_header')
    <h1>Create Live Order</h1>
@endsection

@section('content')
    <form action="{{ route('admin.streaming.orders.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="order_id">Order ID</label>
            <input type="text" name="order_id" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="live_stream_id">Live Stream</label>
            <select name="live_stream_id" class="form-control" required>
                @foreach ($liveStreams as $stream)
                    <option value="{{ $stream->id }}">{{ $stream->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="buyer_id">Buyer</label>
            <select name="buyer_id" class="form-control" required>
                @foreach ($buyers as $buyer)
                    <option value="{{ $buyer->id }}">{{ $buyer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="total_amount">Total Amount</label>
            <input type="number" name="total_amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="voucher_id">Voucher</label>
            <select name="voucher_id" class="form-control">
                <option value="">Select Voucher</option>
                @foreach ($vouchers as $voucher)
                    <option value="{{ $voucher->id }}">{{ $voucher->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="discount_amount">Discount Amount</label>
            <input type="number" name="discount_amount" class="form-control">
        </div>
        <div class="form-group">
            <label for="order_details">Order Details (JSON)</label>
            <textarea name="order_details" class="form-control" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Create Order</button>
    </form>
@endsection