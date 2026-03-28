@extends('adminlte::page')

@section('title', 'Kelola Permissions')

@section('css')
<link rel="stylesheet" href="{{ asset('css/devman-theme.css') }}">
<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 style="margin-bottom:2px"><i class="fas fa-key" style="color:var(--dm-danger)"></i> Kelola Permissions</h1>
            <small class="text-muted">Manajemen permission sistem</small>
        </div>
        <div>
            @can('scan-permissions')
            <button class="btn btn-info mr-1" onclick="scanPermissions()">
                <i class="fas fa-radar mr-1"></i> Scan
            </button>
            @endcan
            @can('create-permissions')
            <button class="btn btn-primary" onclick="createPermission()">
                <i class="fas fa-plus mr-1"></i> Tambah
            </button>
            @endcan
        </div>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="permissionsTable" width="100%">
                <thead>
                    <tr>
                        <th>Nama Permission</th>
                        <th>Guard</th>
                        <th>Dibuat</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal Permission -->
<div class="modal fade" id="modalPermission" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formPermission">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="modalPermissionTitle"><i class="fas fa-key mr-2"></i>Tambah Permission</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="permissionId">
                    <div class="form-group">
                        <label>Nama Permission <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="permissionName" name="name" required
                               placeholder="contoh: view-reports">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSavePermission">
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

    table = $('#permissionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("admin.permissions.data") }}',
        columns: [
            { data: 'name' },
            { data: 'guard_name' },
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

    $('#formPermission').on('submit', function(e) {
        e.preventDefault();
        var id = $('#permissionId').val();
        var url = id ? '/admin/permissions/' + id : '/admin/permissions';
        var method = id ? 'PUT' : 'POST';

        var btn = $('#btnSavePermission');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            success: function(res) {
                $('#modalPermission').modal('hide');
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

function createPermission() {
    $('#formPermission')[0].reset();
    $('#permissionId').val('');
    $('#modalPermissionTitle').text('Tambah Permission');
    $('#modalPermission').modal('show');
}

function editPermission(id) {
    $('#formPermission')[0].reset();
    $('#permissionId').val(id);
    $('#modalPermissionTitle').text('Edit Permission');

    $.get('/admin/permissions/' + id, function(res) {
        $('#permissionName').val(res.data.name);
        $('#modalPermission').modal('show');
    });
}

function deletePermission(id) {
    Swal.fire({
        title: 'Yakin hapus permission ini?',
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
                url: '/admin/permissions/' + id,
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

function scanPermissions() {
    Swal.fire({
        title: 'Scan Permissions?',
        text: 'Akan mendeteksi permission baru dari routes.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-search"></i> Scan',
        cancelButtonText: 'Batal',
    }).then(function(result) {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Scanning...',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            $.post('/admin/permissions/scan', function(res) {
                Swal.close();
                table.ajax.reload(null, false);
                toastr.success(res.message);

                if (res.new_permissions.length > 0) {
                    Swal.fire({
                        title: 'Scan Selesai',
                        html: '<strong>Permission baru:</strong><br>' + res.new_permissions.join('<br>'),
                        icon: 'success',
                    });
                }
            }).fail(function(xhr) {
                Swal.close();
                toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan.');
            });
        }
    });
}
</script>
@stop
