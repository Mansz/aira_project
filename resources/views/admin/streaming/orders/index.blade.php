@extends('admin.layouts.stisla')
@section('title', 'Live Orders')

@section('content_header')
    <h1>Live Orders</h1>
    <a href="{{ route('admin.streaming.orders.create') }}" class="btn btn-primary">Create New Order</a>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive"> <!-- Tambahkan div ini untuk responsivitas -->
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Live Stream</th>
                            <th>Buyer</th>
                            <th>Total Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->order_id }}</td>
                                <td>{{ $order->liveStream->name }}</td>
                                <td>{{ $order->buyer->name }}</td>
                                <td>{{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    <a href="{{ route('admin.streaming.orders.edit', $order) }}" class="btn btn-warning">Edit</a>
                                    <form action="{{ route('admin.streaming.orders.destroy', $order) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> <!-- Tutup div table-responsive -->

            <!-- Pagination -->
            <div class="d-flex justify-content-end">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endsection