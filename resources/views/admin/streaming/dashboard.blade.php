@extends('admin.layouts.stisla')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Live Streaming Dashboard</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($activeStream)
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">{{ $activeStream->title }}</h3>
                                <div class="card-tools">
                                    <span class="badge badge-success">Live</span>
                                    <span class="badge badge-info ml-2">
                                        <i class="fas fa-users"></i> {{ $activeStream->viewer_count }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="zego-container" class="mb-3" style="width: 100%; height: 400px; background: #000;">
                                    <div id="local-video" style="width: 100%; height: 100%;"></div>
                                </div>
                                <div class="stream-controls mt-3">
                                    <button class="btn btn-danger" onclick="endStream()">
                                        <i class="fas fa-stop-circle"></i> End Stream
                                    </button>
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">
                                        <i class="fas fa-box"></i> Manage Products
                                    </button>
                                    <button class="btn btn-success" onclick="exportComments()">
                                        <i class="fas fa-file-export"></i> Export Comments
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Live Comments</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="direct-chat-messages" id="comments-container" style="height: 400px; overflow-y: auto;">
                                    @foreach($activeStream->comments as $comment)
                                        <div class="direct-chat-msg">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name float-left">{{ $comment->user->name }}</span>
                                                <span class="direct-chat-timestamp float-right">
                                                    {{ $comment->created_at->format('H:i') }}
                                                </span>
                                            </div>
                                            <div class="direct-chat-text @if($comment->is_order) bg-warning @endif">
                                                {{ $comment->content }}
                                                @if($comment->is_order)
                                                    <br>
                                                    <small>Order: {{ $comment->order_code }} ({{ $comment->order_quantity }}pcs)</small>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <p class="text-center">No active stream. Start a new stream to begin.</p>
                                <div class="text-center">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#startStreamModal">
                                        <i class="fas fa-play-circle"></i> Start New Stream
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

<!-- Modal Tambah Produk -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.streaming.addProduct') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Tambah Produk ke Live Streaming</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="product_id">Pilih Produk</label>
                        <select name="product_id" class="form-control" required>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="stream_id" value="{{ $activeStream->id }}">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Tambah Produk</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Start Stream Modal -->
<div class="modal fade" id="startStreamModal" tabindex="-1" role="dialog" aria-labelledby="startStreamModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.streaming.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="startStreamModalLabel">Start New Stream</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="stream_title">Judul Stream</label>
                        <input type="text" class="form-control" id="stream_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="stream_description">Deskripsi</label>
                        <textarea class="form-control" id="stream_description" name="description" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Mulai Stream</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inisialisasi fungsi-fungsi yang diperlukan

        $('#addProductForm').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    alert("Produk berhasil ditambahkan!");
                    $('#addProductModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            alert(value[0]); // Menampilkan pesan kesalahan
                        });
                    }
                }
            });
        });

        $('#startStreamForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    alert("Stream berhasil dimulai!");
                    $('#startStreamModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            alert(value[0]); // Menampilkan pesan kesalahan
                        });
                    }
                }
            });
        });

        // Fungsi untuk mengakhiri stream
        window.endStream = function() {
            // Implementasi logika untuk mengakhiri stream
            alert("Stream diakhiri!");
            // Tambahkan logika pengakhiran stream di sini
        };

        // Fungsi untuk mengekspor komentar
        window.exportComments = function() {
            // Implementasi logika untuk mengekspor komentar
            alert("Mengunduh komentar...");
            // Tambahkan logika ekspor komentar di sini
        };
    });
</script>
@endpush