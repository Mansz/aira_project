@extends('admin.layouts.stisla')

@section('title', 'Live Streaming Management')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Live Streaming Management</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Start New Stream Card -->
        
        <!-- Stream History -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Stream History</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Duration</th>
                                <th>Products</th>
                                <th>Total Viewers</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($streams as $stream)
                                <tr>
                                    <td>{{ $stream->title }}</td>
                                    <td>{{ $stream->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @if($stream->ended_at)
                                            {{ $stream->created_at->diffForHumans($stream->ended_at, true) }}
                                        @elseif($stream->status === 'active')
                                            <span class="badge badge-success">Live Now</span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $stream->products->count() }}</td>
                                    <td>{{ $stream->viewer_count }}</td>
                                    <td>
                                        @if($stream->status === 'active')
                                            <a href="{{ route('admin.streaming.dashboard') }}" class="btn btn-sm btn-primary">Go to Stream</a>
                                            <form action="{{ route('admin.streaming.end') }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to end this stream?')">End Stream</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.streaming.export-comments') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="stream_id" value="{{ $stream->id }}">
                                            <button type="submit" class="btn btn-sm btn-info">Export Comments</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No streams found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                {{ $streams->links() }}
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/adminlte/plugins/select2/css/select2.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/adminlte/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(function () {
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Select products to promote'
    });
});
</script>
@endpush