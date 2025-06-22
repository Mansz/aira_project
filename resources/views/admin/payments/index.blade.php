@extends('admin.layouts.stisla')
@section('title', 'Payments')

@section('content')
<div class="content-header">
    <h1>Payments</h1>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive"> <!-- Tambahkan div ini untuk responsivitas -->
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->id }}</td>
                        <td>{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst($payment->status) }}</td>
                        <td>
                            <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-info">Edit</a>
                            <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No payments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div> <!-- Tutup div table-responsive -->

    </div>
</section>
@endsection