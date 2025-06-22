@extends('admin.layouts.stisla')

@section('title', 'Payment Settings')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Payment Settings</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                {{ session('error') }}
            </div>
        @endif

        <!-- Bank Accounts -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Bank Accounts</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addBankModal">
                        <i class="fas fa-plus"></i> Add Bank Account
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Bank Name</th>
                                <th>Account Name</th>
                                <th>Account Number</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bankAccounts as $bank)
                            <tr>
                                <td>{{ $bank->name }}</td>
                                <td>{{ $bank->account_name }}</td>
                                <td>{{ $bank->account_number }}</td>
                                <td>
                                    <span class="badge badge-{{ $bank->is_active ? 'success' : 'danger' }}">
                                        {{ $bank->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#editBankModal{{ $bank->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.settings.payment.destroy', $bank) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No bank accounts found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- E-Wallets -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">E-Wallets</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addWalletModal">
                        <i class="fas fa-plus"></i> Add E-Wallet
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>E-Wallet Name</th>
                                <th>Account Name</th>
                                <th>Account Number</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($eWallets as $wallet)
                            <tr>
                                <td>{{ $wallet->name }}</td>
                                <td>{{ $wallet->account_name }}</td>
                                <td>{{ $wallet->account_number }}</td>
                                <td>
                                    <span class="badge badge-{{ $wallet->is_active ? 'success' : 'danger' }}">
                                        {{ $wallet->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#editWalletModal{{ $wallet->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.settings.payment.destroy', $wallet) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No e-wallets found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

<!-- Add Bank Account Modal -->
<div class="modal fade" id="addBankModal" tabindex="-1" role="dialog" aria-labelledby="addBankModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">>
        <div class="modal-content">
            <form id="formAddBank" action="{{ route('admin.settings.payment.store') }}" method="POST">
                @csrf
                <input type="hidden" name="payment_type" value="bank">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBankModalLabel">Add Bank Account</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Bank Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Account Name</label>
                        <input type="text" name="account_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" name="account_number" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="bankActiveSwitch" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="bankActiveSwitch">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add E-Wallet Modal -->
<div class="modal fade" id="addWalletModal" tabindex="-1" role="dialog" aria-labelledby="addWalletModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.settings.payment.store') }}" method="POST">
                @csrf
                <input type="hidden" name="payment_type" value="e-wallet">
                <div class="modal-header">
                    <h5 class="modal-title" id="addWalletModalLabel">Add E-Wallet</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>E-Wallet Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Account Name</label>
                        <input type="text" name="account_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" name="account_number" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="walletActiveSwitch" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="walletActiveSwitch">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals -->
@foreach($bankAccounts as $bank)
<div class="modal fade" id="editBankModal{{ $bank->id }}" tabindex="-1" role="dialog" aria-labelledby="editBankModalLabel{{ $bank->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.settings.payment.update', $bank) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editBankModalLabel{{ $bank->id }}">Edit Bank Account</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Bank Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $bank->name }}" required>
                    </div>
                    <div class="form-group">
                        <label>Account Name</label>
                        <input type="text" name="account_name" class="form-control" value="{{ $bank->account_name }}" required>
                    </div>
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" name="account_number" class="form-control" value="{{ $bank->account_number }}" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ $bank->description }}</textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="bankActiveSwitch{{ $bank->id }}" name="is_active" value="1" {{ $bank->is_active ? 'checked' : '' }}>
                            <label class="custom-control-label" for="bankActiveSwitch{{ $bank->id }}">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@foreach($eWallets as $wallet)
<div class="modal fade" id="editWalletModal{{ $wallet->id }}" tabindex="-1" role="dialog" aria-labelledby="editWalletModalLabel{{ $wallet->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.settings.payment.update', $wallet) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editWalletModalLabel{{ $wallet->id }}">Edit E-Wallet</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>E-Wallet Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $wallet->name }}" required>
                    </div>
                    <div class="form-group">
                        <label>Account Name</label>
                        <input type="text" name="account_name" class="form-control" value="{{ $wallet->account_name }}" required>
                    </div>
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" name="account_number" class="form-control" value="{{ $wallet->account_number }}" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ $wallet->description }}</textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="walletActiveSwitch{{ $wallet->id }}" name="is_active" value="1" {{ $wallet->is_active ? 'checked' : '' }}>
                            <label class="custom-control-label" for="walletActiveSwitch{{ $wallet->id }}">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endforeach
@section('scripts')
<script>
$(document).ready(function () {
    $('#formAddBank').on('submit', function (e) {
        e.preventDefault(); // mencegah reload halaman

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#addBankModal').modal('hide'); // tutup modal
                location.reload(); // reload halaman supaya data muncul
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert('Gagal menyimpan data. Cek konsol untuk detail.');
            }
        });
    });
});
</script>
@endsection




