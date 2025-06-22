@extends('admin.layouts.stisla')

@section('content')
<div class="container-fluid">
    <h1>Create Live Streaming</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Live Streaming Form</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.streaming.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label for="products">Select Products</label>
                    <select name="products[]" class="form-control select2" multiple required>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Start Live Streaming</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        // Initialize Select2 for product selection
        $('.select2').select2();
    });
</script>
@endpush