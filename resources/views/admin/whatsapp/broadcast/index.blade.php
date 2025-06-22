@extends('admin.layouts.stisla')

@section('title', 'WhatsApp Broadcast')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">WhatsApp Broadcast</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Broadcast Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Send Broadcast Message</h3>
                    </div>
                    <form id="broadcast-form">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Message</label>
                                <textarea name="message" class="form-control" rows="4" required></textarea>
                                <small class="form-text text-muted">
                                    Available variables: {store_name}, {store_phone}, {cs_hours}
                                </small>
                            </div>

                            <div class="form-group">
                                <label>Filter Recipients</label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" 
                                           id="filter-has-orders" name="has_orders">
                                    <label class="custom-control-label" for="filter-has-orders">
                                        Only customers who have made orders
                                    </label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" 
                                           id="filter-recent-orders" name="recent_orders">
                                    <label class="custom-control-label" for="filter-recent-orders">
                                        Only customers with orders in the last 30 days
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Preview Recipients</label>
                                <button type="button" class="btn btn-info btn-sm ml-2" id="preview-recipients">
                                    <i class="fas fa-users mr-1"></i> Show Recipients
                                </button>
                                <div id="recipients-preview" class="mt-2" style="display: none;">
                                    <div class="alert alert-info">
                                        <span id="recipient-count">0</span> recipients will receive this message
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Phone</th>
                                                    <th>Orders</th>
                                                    <th>Last Order</th>
                                                </tr>
                                            </thead>
                                            <tbody id="recipients-list"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane mr-1"></i> Send Broadcast
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Broadcasts -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Broadcasts</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Recipients</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($broadcasts as $broadcast)
                                        <tr>
                                            <td>{{ $broadcast->created_at->format('d M H:i') }}</td>
                                            <td>
                                                {{ $broadcast->metadata['sent'] ?? 0 }}/{{ $broadcast->metadata['total'] ?? 0 }}
                                            </td>
                                            <td>
                                                @if($broadcast->status === 'completed')
                                                    <span class="badge badge-success">Completed</span>
                                                @elseif($broadcast->status === 'processing')
                                                    <span class="badge badge-info">Processing</span>
                                                @else
                                                    <span class="badge badge-danger">Failed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No recent broadcasts</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    const form = $('#broadcast-form');
    const recipientsPreview = $('#recipients-preview');
    const recipientsList = $('#recipients-list');
    const recipientCount = $('#recipient-count');

    // Preview recipients
    $('#preview-recipients').click(function() {
        const filters = {
            has_orders: $('#filter-has-orders').is(':checked'),
            recent_orders: $('#filter-recent-orders').is(':checked'),
        };

        axios.post('/admin/whatsapp/broadcast/preview', filters)
            .then(response => {
                const recipients = response.data.recipients;
                recipientCount.text(recipients.length);
                
                recipientsList.empty();
                recipients.forEach(user => {
                    recipientsList.append(`
                        <tr>
                            <td>${user.name}</td>
                            <td>${user.phone_number}</td>
                            <td>${user.orders_count}</td>
                            <td>${user.last_order_date || '-'}</td>
                        </tr>
                    `);
                });
                
                recipientsPreview.show();
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Failed to load recipients preview');
            });
    });

    // Send broadcast
    form.submit(function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to send this broadcast message?')) {
            return;
        }

        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true);

        const data = {
            message: $('textarea[name="message"]').val(),
            filters: {
                has_orders: $('#filter-has-orders').is(':checked'),
                recent_orders: $('#filter-recent-orders').is(':checked'),
            }
        };

        axios.post('/admin/whatsapp/broadcast/send', data)
            .then(response => {
                toastr.success('Broadcast message queued successfully');
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Failed to send broadcast message');
                submitBtn.prop('disabled', false);
            });
    });
});
</script>
@endpush
@endsection
