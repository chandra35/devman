@extends('adminlte::page')

@section('title', 'Kelola Roles')

@section('css')
<link rel="stylesheet" href="{{ asset('css/devman-theme.css') }}">
<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 style="margin-bottom:2px"><i class="fas fa-user-tag" style="color:var(--dm-warning)"></i> Kelola Roles</h1>
            <small class="text-muted">Manajemen role dan hak akses</small>
        </div>
        @can('create-roles')
        <button class="btn btn-primary" onclick="createRole()">
            <i class="fas fa-plus mr-1"></i> Tambah Role
        </button>
        @endcan
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="rolesTable" width="100%">
                <thead>
                    <tr>
                        <th>Nama Role</th>
                        <th>Jumlah Permissions</th>
                        <th>Dibuat</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal Role -->
<div class="modal fade" id="modalRole" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formRole">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="modalRoleTitle"><i class="fas fa-user-tag mr-2"></i>Tambah Role</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="roleId">
                    <div class="form-group">
                        <label>Nama Role <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="roleName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Permissions</label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-xs btn-outline-primary" onclick="checkAll(true)">Pilih Semua</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="checkAll(false)">Hapus Semua</button>
                        </div>
                        <div class="row" id="permissionsCheckboxes">
                            @foreach($permissions->groupBy(function($item) { return explode('-', $item->name)[1] ?? 'other'; }) as $group => $perms)
                            <div class="col-md-4 mb-3">
                                <div class="card card-outline card-info mb-0">
                                    <div class="card-header py-1">
                                        <strong class="text-capitalize">{{ $group }}</strong>
                                    </div>
                                    <div class="card-body py-2">
                                        @foreach($perms as $perm)
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input perm-check"
                                                   id="perm_{{ $perm->id }}" name="permissions[]" value="{{ $perm->name }}">
                                            <label class="custom-control-label" for="perm_{{ $perm->id }}">{{ $perm->name }}</label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveRole">
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

    table = $('#rolesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("admin.roles.data") }}',
        columns: [
            { data: 'name' },
            { data: 'permissions_count', orderable: false, searchable: false },
            { data: 'created_at' },
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

    $('#formRole').on('submit', function(e) {
        e.preventDefault();
        var id = $('#roleId').val();
        var url = id ? '/admin/roles/' + id : '/admin/roles';
        var method = id ? 'PUT' : 'POST';

        var btn = $('#btnSaveRole');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            success: function(res) {
                $('#modalRole').modal('hide');
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

function checkAll(checked) {
    $('.perm-check').prop('checked', checked);
}

function createRole() {
    $('#formRole')[0].reset();
    $('#roleId').val('');
    $('#modalRoleTitle').text('Tambah Role');
    $('.perm-check').prop('checked', false);
    $('#modalRole').modal('show');
}

function editRole(id) {
    $('#formRole')[0].reset();
    $('#roleId').val(id);
    $('#modalRoleTitle').text('Edit Role');
    $('.perm-check').prop('checked', false);

    $.get('/admin/roles/' + id, function(res) {
        $('#roleName').val(res.data.name);
        res.data.permissions.forEach(function(perm) {
            $('input[name="permissions[]"][value="' + perm + '"]').prop('checked', true);
        });
        $('#modalRole').modal('show');
    });
}

function deleteRole(id) {
    Swal.fire({
        title: 'Yakin hapus role ini?',
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
                url: '/admin/roles/' + id,
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
