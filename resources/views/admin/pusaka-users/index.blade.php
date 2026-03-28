@extends('adminlte::page')

@section('title', 'User Pusaka')

@section('css')
<link rel="stylesheet" href="{{ asset('css/devman-theme.css') }}">
<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 style="margin-bottom:2px"><i class="fas fa-mosque" style="color:var(--dm-primary)"></i> User Pusaka</h1>
            <small class="text-muted">Daftar NIP yang diizinkan menggunakan aplikasi PusakaV3</small>
        </div>
        <button class="btn btn-primary" onclick="createPusakaUser()">
            <i class="fas fa-plus mr-1"></i> Tambah User
        </button>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="pusakaTable" width="100%">
                <thead>
                    <tr>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Terdaftar</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalPusaka" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formPusaka">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="modalPusakaTitle"><i class="fas fa-mosque mr-2"></i>Tambah User Pusaka</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pusakaId">
                    <div class="form-group">
                        <label>NIP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pusakaNip" name="nip" required placeholder="Masukkan NIP / Username Kemenag">
                    </div>
                    <div class="form-group">
                        <label>Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pusakaName" name="name" required placeholder="Nama lengkap">
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea class="form-control" id="pusakaNotes" name="notes" rows="2" placeholder="Opsional"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="pusakaActive" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="pusakaActive">Aktif (diizinkan login)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSavePusaka">
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

    table = $('#pusakaTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("admin.pusaka-users.data") }}',
        columns: [
            { data: 'nip' },
            { data: 'name' },
            { data: 'is_active', orderable: false, searchable: false },
            { data: 'notes' },
            { data: 'created_at' },
            { data: 'actions', orderable: false, searchable: false },
        ],
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
            emptyTable: 'Tidak ada data. Tambahkan NIP yang diizinkan.',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            lengthMenu: 'Tampilkan _MENU_ data',
            search: 'Cari:',
            paginate: { previous: '<i class="fas fa-chevron-left"></i>', next: '<i class="fas fa-chevron-right"></i>' }
        },
        order: [[0, 'asc']],
    });

    $('#formPusaka').on('submit', function(e) {
        e.preventDefault();
        var id = $('#pusakaId').val();
        var url = id ? '/admin/pusaka-users/' + id : '/admin/pusaka-users';
        var method = id ? 'PUT' : 'POST';

        var btn = $('#btnSavePusaka');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            success: function(res) {
                $('#modalPusaka').modal('hide');
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

function createPusakaUser() {
    $('#formPusaka')[0].reset();
    $('#pusakaId').val('');
    $('#modalPusakaTitle').html('<i class="fas fa-mosque mr-2"></i>Tambah User Pusaka');
    $('#pusakaActive').prop('checked', true);
    $('#modalPusaka').modal('show');
}

function editPusakaUser(id) {
    $('#formPusaka')[0].reset();
    $('#pusakaId').val(id);
    $('#modalPusakaTitle').html('<i class="fas fa-mosque mr-2"></i>Edit User Pusaka');

    $.get('/admin/pusaka-users/' + id, function(res) {
        $('#pusakaNip').val(res.data.nip);
        $('#pusakaName').val(res.data.name);
        $('#pusakaNotes').val(res.data.notes);
        $('#pusakaActive').prop('checked', res.data.is_active);
        $('#modalPusaka').modal('show');
    });
}

function deletePusakaUser(id) {
    Swal.fire({
        title: 'Yakin hapus user ini?',
        text: 'NIP ini tidak akan bisa login ke PusakaV3!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: 'Batal',
    }).then(function(result) {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/pusaka-users/' + id,
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
