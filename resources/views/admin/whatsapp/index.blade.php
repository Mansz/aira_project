@extends('admin.layouts.stisla')
@section('title', 'WhatsApp Messages')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">WhatsApp Messages</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="card card-info">
                    <div class="card-body">
                        <h3 id="total-messages">0</h3>
                        <p>Total Messages</p>
                    </div>
                    <div class="card-footer">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="card card-success">
                    <div class="card-body">
                        <h3 id="total-sent">0</h3>
                        <p>Messages Sent</p>
                    </div>
                    <div class="card-footer">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="card card-warning">
                    <div class="card-body">
                        <h3 id="total-received">0</h3>
                        <p>Messages Received</p>
                    </div>
                    <div class="card-footer">
                        <i class="fas fa-inbox"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="card card-danger">
                    <div class="card-body">
                        <h3 id="total-failed">0</h3>
                        <p>Failed Messages</p>
                    </div>
                    <div class="card-footer">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filters</h3>
            </div>
            <div class="card-body">
                <form id="filter-form" method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="{{ request('phone') }}" placeholder="Enter phone number">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Direction</label>
                                <select name="direction" class="form-control">
                                    <option value="">All</option>
                                    <option value="inbound" {{ request('direction') == 'inbound' ? 'selected' : '' }}>Inbound</option>
                                    <option value="outbound" {{ request('direction') == 'outbound' ? 'selected' : '' }}>Outbound</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Messages Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Messages</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.whatsapp.export', request()->all()) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-download mr-1"></i> Export
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Phone</th>
                            <th>User</th>
                            <th>Message</th>
                            <th>Direction</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                            <tr>
                                <td>{{ $message->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <a href="{{ route('admin.whatsapp.conversation', $message->phone_number) }}">
                                        {{ $message->formatted_phone }}
                                    </a>
                                </td>
                                <td>
                                    @if($message->user)
                                        <a href="{{ route('admin.users.show', $message->user->id) }}">
                                            {{ $message->user->name }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ Str::limit($message->message, 50) }}</td>
                                <td>{!! $message->direction_badge !!}</td>
                                <td>{!! $message->status_badge !!}</td>
                                <td>
                                    @if($message->order)
                                        <a href="{{ route('admin.orders.show', $message->order->id) }}">
                                            #{{ $message->order->order_number }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" 
                                            onclick="viewMessage('{{ $message->id }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No messages found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $messages->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Message Detail Modal -->
<div class="modal fade" id="message-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message Detail</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="message-detail"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function loadStatistics() {
    axios.get('{{ route('admin.whatsapp.statistics') }}')
        .then(response => {
            const stats = response.data;
            document.getElementById('total-messages').textContent = stats.total;
            document.getElementById('total-sent').textContent = stats.sent;
            document.getElementById('total-received').textContent = stats.received;
            document.getElementById('total-failed').textContent = stats.failed;
        })
        .catch(error => {
            console.error('Failed to load statistics:', error);
        });
}

function viewMessage(id) {
    axios.get(`/admin/whatsapp/messages/${id}`)
        .then(response => {
            const message = response.data;
            let html = `
                <dl class="row">
                    <dt class="col-sm-4">Phone Number</dt>
                    <dd class="col-sm-8">${message.formatted_phone}</dd>
                    
                    <dt class="col-sm-4">Date</dt>
                    <dd class="col-sm-8">${message.created_at}</dd>
                    
                    <dt class="col-sm-4">Direction</dt>
                    <dd class="col-sm-8">${message.direction_badge}</dd>
                    
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">${message.status_badge}</dd>
                    
                    <dt class="col-sm-4">Message</dt>
                    <dd class="col-sm-8">${message.message}</dd>
                </dl>
            `;
            
            if (message.metadata) {
                html += `
                    <hr>
                    <h6>Metadata</h6>
                    <pre>${JSON.stringify(message.metadata, null, 2)}</pre>
                `;
            }
            
            $('.message-detail').html(html);
            $('#message-modal').modal('show');
        })
        .catch(error => {
            console.error('Failed to load message:', error);
            toastr.error('Failed to load message details');
        });
}

$(function() {
    loadStatistics();
});
</script>
@endpush
@endsection