@extends('admin.layouts.stisla')

@section('content')
<div class="container">
    <h2>Tambah Produk Baru</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="name">Nama Produk</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea name="description" id="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="price">Harga</label>
                <input type="number" name="price" id="price" class="form-control" value="{{ old('price') }}" required min="0" step="0.01">
            </div>
            <div class="form-group col-md-4">
                <label for="stock">Stok</label>
                <input type="number" name="stock" id="stock" class="form-control" value="{{ old('stock') }}" required min="0">
            </div>
            <div class="form-group col-md-4">
                <label for="weight">Berat (kg)</label>
                <input type="number" name="weight" id="weight" class="form-control" value="{{ old('weight') }}" required min="0" step="0.01">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="color">Warna</label>
                <input type="text" name="color" id="color" class="form-control" value="{{ old('color') }}">
            </div>
            <div class="form-group col-md-4">
                <label for="size">Ukuran</label>
                <input type="text" name="size" id="size" class="form-control" value="{{ old('size') }}">
            </div>
            <div class="form-group col-md-4">
                <label for="sku">SKU</label>
                <input type="text" name="sku" id="sku" class="form-control" value="{{ old('sku') }}" required>
            </div>
        </div>

        <div class="form-group">
            <label for="image">Gambar Produk</label>
            <input type="file" name="image" id="image" class="form-control-file" required>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Simpan Produk
        </button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
