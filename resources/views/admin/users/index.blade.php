@extends('admin.layouts.stisla')

@section('content')
<div class="container">
    <h1>User Management</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Users List</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive"> <!-- Tambahkan div responsif -->
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>WhatsApp</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->wa_number }}</td>
                            <td>
                                @if($user->blocked)
                                    <span class="badge badge-danger">Blocked</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-info">Edit</a>
                                
                                @if($user->blocked)
                                    <form action="{{ route('admin.users.unblock', $user) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to unblock this user?')">
                                            Unblock
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.users.block', $user) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Are you sure you want to block this user?')">
                                            Block
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> <!-- Tutup div responsif -->
        </div>
        <div class="card-footer clearfix">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table td, .table th {
        vertical-align: middle;
    }
</style>
@endpush