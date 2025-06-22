@extends('admin.layouts.stisla')

@section('title', 'WhatsApp Conversation')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    WhatsApp Conversation
                    <small class="text-muted">{{ $phone_number }}</small>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="{{ route('admin.whatsapp.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Chat Window -->
            <div class="col-md-8">
                <div class="card card-primary direct-chat direct-chat-primary">
                    <div class="card-header">
                        <h3 class="card-title">Chat History</h3>
                    </div>
                    <div class="card-body">
                        <div class="direct-chat-messages" id="chat-messages" style="height: 500px;">
                            @foreach($messages->reverse() as $message)
                                <div class="direct-chat-msg {{ $message->isOutbound() ? 'right' : '' }}">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="direct-chat-name float-{{ $message->isOutbound() ? 'right' : 'left' }}">
                                            {{ $message->isOutbound() ? 'Admin' : ($user ? $user->name : $phone_number) }}
                                        </span>
                                        <span class="direct-chat-timestamp float-{{ $message->isOutbound() ? 'left' : 'right' }}">
                                            {{ $message->created_at->format('d M H:i') }}
                                        </span>
                                    </div>
                                    <img class="direct-chat-img" 
                                         src="{{ $message->isOutbound() ? asset('assets/img/admin-avatar.png') : asset('assets/img/user-avatar.png') }}" 
                                         alt="Avatar">
                                    <div class="direct-chat-text">
                                        {{ $message->message }}
                                        <small class="text-muted d-block mt-1">
                                            {!! $message->status_badge !!}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer">
                        <form id="message-form" action="{{ route('admin.whatsapp.send') }}" method="POST">
                            @csrf
                            <input type="hidden" name="phone_number" value="{{ $phone_number }}">
                            <div class="input-group">
                                <input type="text" name="message" 
                                       class="form-control" 
                                       placeholder="Type Message ..."
                                       required>
                                <span class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        Send <i class="fas fa-paper-plane ml-1"></i>
                                    </button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- User/Contact Info -->
            <div class="col-md-4">
                <!-- User Info Card -->
                @if($user)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">User Information</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Name</dt>
                            <dd class="col-sm-8">{{ $user->name }}</dd>
                            
                            <dt class="col-sm-4">Email</dt>
                            <dd class="col-sm-8">{{ $user->email }}</dd>
                            
                            <dt class="col-sm-4">Phone</dt>
                            <dd class="col-sm-8">{{ $user->phone_number }}</dd>
                            
                            <dt class="col-sm-4">Joined</dt>
                            <dd class="col-sm-8">{{ $user->created_at->format('d M Y') }}</dd>
                        </dl>
                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-user mr-1"></i> View Profile
                        </a>
                    </div>
                </div>
                @endif

                <!-- Quick Replies -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Replies</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group">
                            <button type="button" class="list-group-item list-group-item-action quick-reply">
                                Terima kasih atas pesanan Anda. Kami akan segera memprosesnya.
                            </button>
                            <button type="button" class="list-group-item list-group-item-action quick-reply">
                                Mohon tunggu sebentar, kami akan segera membantu Anda.
                            </button>
                            <button type="button" class="list-group-item list-group-item-action quick-reply">
                                Untuk informasi lebih lanjut, silakan kunjungi website kami di airagrosircirebon.store
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Message Templates -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Message Templates</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group">
                            @foreach(config('whatsapp.templates.order_status') as $status => $template)
                                <button type="button" class="list-group-item list-group-item-action quick-reply">
                                    {{ $template }}
                                </button>
                            @endforeach
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
    // Scroll to bottom of chat
    const chatMessages = document.getElementById('chat-messages');
    chatMessages.scrollTop = chatMessages.scrollHeight;

    // Quick reply buttons
    $('.quick-reply').click(function() {
        $('input[name="message"]').val($(this).text().trim());
    });

    // Handle message form submission
    $('#message-form').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        submitBtn.prop('disabled', true);
        
        axios.post(form.attr('action'), form.serialize())
            .then(response => {
                if (response.data.status === 'success') {
                    form.find('input[name="message"]').val('');
                    toastr.success('Message sent successfully');
                    location.reload(); // Reload to show new message
                } else {
                    toastr.error('Failed to send message');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Failed to send message');
            })
            .finally(() => {
                submitBtn.prop('disabled', false);
            });
    });
});
</script>
@endpush
@endsection