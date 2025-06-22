@extends('admin.layouts.stisla')

@section('title', 'Edit Live Order')

@section('content_header')
    <h1>Edit Live Order</h1>
@endsection

@section('content')
    <form action="{{ route('admin.streaming.orders.update', $order) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="order_id">Order ID</label>
            <input type="text" name="order_id" class="form-control" value="{{ $order->order_id }}" required>
        </div>
        <div class="form-group">
            <label for="live_stream_id">Live Stream</label>
            <select name="live_stream_id" class="form-control" required>
                @foreach ($liveStreams as $stream)
                    <option value="{{ $stream->id }}" {{ $order->live_stream_id == $stream->id ? 'selected' : '' }}>{{ $stream->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="buyer_id">Buyer</label>
            <select name="buyer_id" class="form-control" required>
                @foreach ($buyers as $buyer)
                    <option value="{{ $buyer->id }}" {{ $order->buyer_id == $buyer->id ? 'selected' : '' }}>{{ $buyer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="total_amount">Total Amount</label>
            <input type="number" name="total_amount" class="form-control" value="{{ $order->total_amount }}" required>
        </div>
        <div class="form-group">
            <label for="voucher_id">Voucher</label>
            <select name="voucher_id" class="form-control">
                <option value="">Select Voucher</option>
                @foreach ($vouchers as $voucher)
                    <option value="{{ $voucher->id }}" {{ $order->voucher_id == $voucher->id ? 'selected' : '' }}>{{ $voucher->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="discount_amount">Discount Amount</label>
            <input type="number" name="discount_amount" class="form-control" value="{{ $order->discount_amount }}">
        </div>
        <div class="form-group">
            <label for="order_details">Order Details (JSON)</label>
            <textarea name="order_details" class="form-control" required>{{ $order->order_details }}</textarea>
        </div>
        <button type="submit" class="btn btn-success">Update Order</button>
    </form>
@endsection