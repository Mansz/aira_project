@extends('admin.layouts.stisla')

@section('title', 'WhatsApp Auto Replies')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">WhatsApp Auto Replies</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#auto-reply-modal">
                        <i class="fas fa-plus mr-1"></i> Add Auto Reply
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Keyword</th>
                            <th>Response</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($autoReplies as $reply)
                            <tr>
                                <td>{{ $reply->keyword }}</td>
                                <td>{{ Str::limit($reply->response, 50) }}</td>
                                <td>
                                    @if($reply->is_regex)
                                        <span class="badge badge-info">Regex</span>
                                    @else
                                        <span class="badge badge-secondary">Simple</span>
                                    @endif
                                </td>
                                <td>
                                    @if($reply->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info edit-reply" 
                                            data-reply="{{ json_encode($reply) }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-reply"
                                            data-id="{{ $reply->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No auto replies configured</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($autoReplies->hasPages())
                <div class="card-footer clearfix">
                    {{ $autoReplies->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Auto Reply Modal -->
<div class="modal fade" id="auto-reply-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Auto Reply</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="auto-reply-form">
                <div class="modal-body">
                    <input type="hidden" name="id" id="reply-id">
                    
                    <div class="form-group">
                        <label>Keyword</label>
                        <input type="text" name="keyword" id="reply-keyword" 
                               class="form-control" required>
                        <small class="form-text text-muted">
                            For regex pattern, start and end with forward slashes (e.g., /pattern/)
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Response</label>
                        <textarea name="response" id="reply-response" 
                                  class="form-control" rows="4" required></textarea>
                        <small class="form-text text-muted">
                            Available variables: {store_name}, {store_phone}, {cs_hours}, {order_number}, {order_status}, {tracking_number}
                        </small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   name="is_regex" id="reply-is-regex">
                            <label class="custom-control-label" for="reply-is-regex">
                                Use Regex Pattern
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   name="is_active" id="reply-is-active" checked>
                            <label class="custom-control-label" for="reply-is-active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    const modal = $('#auto-reply-modal');
    const form = $('#auto-reply-form');

    // Edit auto reply
    $('.edit-reply').click(function() {
        const reply = $(this).data('reply');
        $('#reply-id').val(reply.id);
        $('#reply-keyword').val(reply.keyword);
        $('#reply-response').val(reply.response);
        $('#reply-is-regex').prop('checked', reply.is_regex);
        $('#reply-is-active').prop('checked', reply.is_active);
        modal.modal('show');
    });

    // Save auto reply
    form.submit(function(e) {
        e.preventDefault();
        const id = $('#reply-id').val();
        const url = id ? `/admin/whatsapp/auto-replies/${id}` : '/admin/whatsapp/auto-replies';
        const method = id ? 'PUT' : 'POST';

        axios({
            method: method,
            url: url,
            data: {
                keyword: $('#reply-keyword').val(),
                response: $('#reply-response').val(),
                is_regex: $('#reply-is-regex').is(':checked'),
                is_active: $('#reply-is-active').is(':checked'),
            }
        })
        .then(response => {
            toastr.success('Auto reply saved successfully');
            modal.modal('hide');
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Failed to save auto reply');
        });
    });

    // Delete auto reply
    $('.delete-reply').click(function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this auto reply?')) {
            axios.delete(`/admin/whatsapp/auto-replies/${id}`)
                .then(response => {
                    toastr.success('Auto reply deleted successfully');
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Failed to delete auto reply');
                });
        }
    });

    // Reset form on modal close
    modal.on('hidden.bs.modal', function() {
        form[0].reset();
        $('#reply-id').val('');
    });
});
</script>
@endpush
@endsection
