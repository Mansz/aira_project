@extends('admin.layouts.stisla')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2 class="mb-0">Tambah Kategori Produk</h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.products.categories.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.products.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="name">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="icon">Icon</label>
                    <div class="custom-file">
                        <input type="file" 
                               class="custom-file-input @error('icon') is-invalid @enderror" 
                               id="icon" 
                               name="icon"
                               accept="image/*">
                        <label class="custom-file-label" for="icon">Pilih file...</label>
                        @error('icon')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <small class="form-text text-muted">
                        Format yang diizinkan: JPG, JPEG, PNG, SVG. Maksimal ukuran: 1MB
                    </small>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" 
                               class="custom-control-input" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Aktif</label>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Update file input label with selected filename
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
</script>
@endpush
@endsection
