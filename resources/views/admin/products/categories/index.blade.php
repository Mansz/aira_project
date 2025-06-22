@extends('admin.layouts.stisla')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2 class="mb-0">Kategori Produk</h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.products.categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Kategori
            </a>
        </div>
    </div>

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

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Icon</th>
                            <th>Nama</th>
                            <th>Slug</th>
                            <th>Jumlah Produk</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td>
                                    @if($category->icon)
                                        <img src="{{ Storage::url($category->icon) }}" 
                                             alt="{{ $category->name }}" 
                                             class="img-thumbnail"
                                             style="max-width: 50px;">
                                    @else
                                        <span class="text-muted">No icon</span>
                                    @endif
                                </td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->slug }}</td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $category->products_count }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $category->is_active ? 'success' : 'danger' }}">
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.products.categories.edit', $category) }}">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.products.categories.toggle-status', $category) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="dropdown-item"
                                                        onclick="return confirm('Apakah Anda yakin ingin {{ $category->is_active ? 'menonaktifkan' : 'mengaktifkan' }} kategori ini?')">
                                                    <i class="fas fa-toggle-on"></i>
                                                    {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                            @if($category->products_count === 0)
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('admin.products.categories.destroy', $category) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="dropdown-item text-danger"
                                                            onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada kategori yang tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
