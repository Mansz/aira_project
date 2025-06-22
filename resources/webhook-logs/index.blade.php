@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Webhook Logs</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a href="{{ route('admin.webhook-logs.export', request()->all()) }}" class="btn btn-success">
                            <i class="fas fa-download"></i> Export to Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <form action="{{ route('admin.webhook-logs.index') }}" method="GET" class="row">
                        <div class="col-md-2">
                            <select name="source" class="form-control">
                                <option value="">All Sources</option>
                                <option value="whatsapp_gateway" {{ request('source') === 'whatsapp_gateway' ? 'selected' : '' }}>WhatsApp Gateway</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To Date">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search event type or payload...">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Source</th>
                                <th>Event Type</th>
                                <th>Status</th>
                                <th>IP Address</th>
                                <th>Created At</th>
                                <th>Processed At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($webhookLogs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->source }}</td>
                                <td>{{ $log->event_type }}</td>
                                <td>
                                    <span class="badge badge-{{ $log->status === 'success' ? 'success' : ($log->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td>{{ $log->ip_address }}</td>
                                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $log->processed_at ? $log->processed_at->format('Y-m-d H:i:s') : 'Not processed' }}</td>
                                <td>
                                    <a href="{{ route('admin.webhook-logs.show', $log) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No webhook logs found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($webhookLogs->hasPages())
                <div class="card-footer clearfix">
                    {{ $webhookLogs->appends(request()->all())->links() }}
                </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .table td, .table th {
        vertical-align: middle;
    }
</style>
@endpush
