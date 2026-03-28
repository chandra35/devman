@extends('adminlte::page')

@section('title', 'Kelola Users')

@section('css')
<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('content_header')
    <h1><i class="fas fa-users"></i> Kelola Users</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Users</h3>
        <div class="card-tools">
            @can('create-users')
            <button class="btn btn-sm btn-primary" onclick="createUser()">
                <i class="fas fa-plus"></i> Tambah User
            </button>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm" id="usersTable" width="100%">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal User -->
<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formUser">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="modalUserTitle">Tambah User</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="userId">
                    <div class="form-group">
                        <label>Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="userName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="userUsername" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" id="userEmail" name="email">
                    </div>
                    <div class="form-group">
                        <label>Password <span class="text-danger password-required">*</span></label>
                        <input type="password" class="form-control" id="userPassword" name="password">
                        <small class="text-muted password-hint" style="display:none">Kosongkan jika tidak ingin mengubah password.</small>
                    </div>
                    <div class="form-group">
                        <label>Roles <span class="text-danger">*</span></label>
                        <div id="rolesCheckboxes">
                            @foreach($roles as $role)
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="role_{{ $role->id }}" name="roles[]" value="{{ $role->name }}">
                                <label class="custom-control-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="userActive" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="userActive">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveUser">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
var table;

$(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

    table = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("admin.users.data") }}',
        columns: [
            { data: 'name' },
            { data: 'username' },
            { data: 'email' },
            { data: 'roles', orderable: false, searchable: false },
            { data: 'is_active', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false },
        ],
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
            emptyTable: 'Tidak ada data.',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            lengthMenu: 'Tampilkan _MENU_ data',
            search: 'Cari:',
            paginate: { previous: '<i class="fas fa-chevron-left"></i>', next: '<i class="fas fa-chevron-right"></i>' }
        },
        order: [[0, 'asc']],
    });

    // Submit form
    $('#formUser').on('submit', function(e) {
        e.preventDefault();
        var id = $('#userId').val();
        var url = id ? '/admin/users/' + id : '/admin/users';
        var method = id ? 'PUT' : 'POST';

        var btn = $('#btnSaveUser');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            success: function(res) {
                $('#modalUser').modal('hide');
                table.ajax.reload(null, false);
                toastr.success(res.message);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, val) { toastr.error(val[0]); });
                } else {
                    toastr.error('Terjadi kesalahan.');
                }
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
            }
        });
    });
});

function createUser() {
    $('#formUser')[0].reset();
    $('#userId').val('');
    $('#modalUserTitle').text('Tambah User');
    $('#userPassword').attr('required', true);
    $('.password-required').show();
    $('.password-hint').hide();
    $('input[name="roles[]"]').prop('checked', false);
    $('#userActive').prop('checked', true);
    $('#modalUser').modal('show');
}

function editUser(id) {
    $('#formUser')[0].reset();
    $('#userId').val(id);
    $('#modalUserTitle').text('Edit User');
    $('#userPassword').attr('required', false);
    $('.password-required').hide();
    $('.password-hint').show();
    $('input[name="roles[]"]').prop('checked', false);

    $.get('/admin/users/' + id, function(res) {
        $('#userName').val(res.data.name);
        $('#userUsername').val(res.data.username);
        $('#userEmail').val(res.data.email);
        $('#userActive').prop('checked', res.data.is_active);
        res.data.roles.forEach(function(role) {
            $('input[name="roles[]"][value="' + role.name + '"]').prop('checked', true);
        });
        $('#modalUser').modal('show');
    });
}

function deleteUser(id) {
    Swal.fire({
        title: 'Yakin hapus user ini?',
        text: 'Data yang dihapus tidak bisa dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: 'Batal',
    }).then(function(result) {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/users/' + id,
                method: 'DELETE',
                success: function(res) {
                    table.ajax.reload(null, false);
                    toastr.success(res.message);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan.');
                }
            });
        }
    });
}
</script>
@stop
