@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Webhook Log Details</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a href="{{ route('admin.webhook-logs.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Basic Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 200px">ID</th>
                                            <td>{{ $webhookLog->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Source</th>
                                            <td>{{ $webhookLog->source }}</td>
                                        </tr>
                                        <tr>
                                            <th>Event Type</th>
                                            <td>{{ $webhookLog->event_type }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                <span class="badge badge-{{ $webhookLog->status === 'success' ? 'success' : ($webhookLog->status === 'pending' ? 'warning' : 'danger') }}">
                                                    {{ $webhookLog->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 200px">IP Address</th>
                                            <td>{{ $webhookLog->ip_address }}</td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ $webhookLog->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Processed At</th>
                                            <td>{{ $webhookLog->processed_at ? $webhookLog->processed_at->format('Y-m-d H:i:s') : 'Not processed' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Request Headers</h3>
                        </div>
                        <div class="card-body">
                            <pre class="bg-light p-3"><code>{{ json_encode($webhookLog->headers, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Request Payload</h3>
                        </div>
                        <div class="card-body">
                            <pre class="bg-light p-3"><code>{{ json_encode($webhookLog->payload, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>

                    @if($webhookLog->response)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Response</h3>
                        </div>
                        <div class="card-body">
                            <pre class="bg-light p-3"><code>{{ json_encode($webhookLog->response, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    pre {
        margin: 0;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .table td, .table th {
        vertical-align: middle;
    }
</style>
@endpush
