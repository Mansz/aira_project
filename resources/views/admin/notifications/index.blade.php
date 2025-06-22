@extends('admin.layouts.stisla')

@section('title', 'Notifikasi')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Notifikasi</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    @if($notifications->isNotEmpty())
                        <button class="btn btn-secondary btn-sm mr-2" onclick="markAllAsRead()">
                            <i class="fas fa-check-double mr-1"></i> Tandai Semua Dibaca
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="clearAll()">
                            <i class="fas fa-trash mr-1"></i> Hapus Semua
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($notifications as $notification)
                        <div class="list-group-item {{ $notification->read_at ? '' : 'bg-light' }}" 
                             id="notification-{{ $notification->id }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $notification->data['type'] ?? 'Notification' }}</h6>
                                    <p class="mb-1">{{ $notification->data['message'] ?? '' }}</p>
                                    <small class="text-muted">
                                        <i class="far fa-clock mr-1"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                        @if($notification->read_at)
                                            · Dibaca {{ $notification->read_at->diffForHumans() }}
                                        @endif
                                    </small>
                                </div>
                                <div class="ml-3">
                                    @if(!$notification->read_at)
                                        <button class="btn btn-sm btn-link text-secondary" 
                                                onclick="markAsRead('{{ $notification->id }}')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    <button class="btn btn-sm btn-link text-danger" 
                                            onclick="deleteNotification('{{ $notification->id }}')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">
                            <i class="fas fa-bell fa-2x mb-2"></i>
                            <p class="mb-0">Tidak ada notifikasi</p>
                        </div>
                    @endforelse
                </div>
            </div>
            @if($notifications->hasPages())
                <div class="card-footer">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function markAsRead(id) {
    axios.post(`/admin/notifications/${id}/read`)
        .then(() => {
            $(`#notification-${id}`).removeClass('bg-light');
            toastr.success('Notifikasi ditandai sebagai dibaca');
        })
        .catch(error => {
            toastr.error('Gagal menandai notifikasi sebagai dibaca');
        });
}

function markAllAsRead() {
    if (confirm('Tandai semua notifikasi sebagai dibaca?')) {
        axios.post('/admin/notifications/mark-all-read')
            .then(() => {
                location.reload();
            })
            .catch(error => {
                toastr.error('Gagal menandai semua notifikasi sebagai dibaca');
            });
    }
}

function deleteNotification(id) {
    if (confirm('Hapus notifikasi ini?')) {
        axios.delete(`/admin/notifications/${id}`)
            .then(() => {
                $(`#notification-${id}`).fadeOut();
                toastr.success('Notifikasi dihapus');
            })
            .catch(error => {
                toastr.error('Gagal menghapus notifikasi');
            });
    }
}

function clearAll() {
    if (confirm('Hapus semua notifikasi?')) {
        axios.post('/admin/notifications/clear-all')
            .then(() => {
                location.reload();
            })
            .catch(error => {
                toastr.error('Gagal menghapus semua notifikasi');
            });
    }
}
</script>
@endpush
@endsection
