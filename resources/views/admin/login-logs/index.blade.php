@extends('adminlte::page')

@section('title', 'Login Logs')

@section('css')
<link rel="stylesheet" href="{{ asset('css/devman-theme.css') }}">
<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 style="margin-bottom:2px"><i class="fas fa-history" style="color:var(--dm-primary)"></i> Login Logs</h1>
            <small class="text-muted">Riwayat login dari aplikasi mobile</small>
        </div>
    </div>
@stop

@section('content')
<div class="row mb-3">
    <div class="col-md-3">
        <select class="form-control" id="filterStatus">
            <option value="">Semua Status</option>
            <option value="success">Berhasil</option>
            <option value="failed">Gagal</option>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-control" id="filterApp">
            <option value="">Semua Aplikasi</option>
            <option value="pusakav3">PusakaV3</option>
        </select>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="logsTable" width="100%">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nama</th>
                        <th>Aplikasi</th>
                        <th>IP Address</th>
                        <th>Device</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
var table;

$(function() {
    table = $('#logsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.login-logs.data") }}',
            data: function(d) {
                d.status = $('#filterStatus').val();
                d.app_name = $('#filterApp').val();
            }
        },
        columns: [
            { data: 'username' },
            { data: 'user_name' },
            { data: 'app_name', searchable: false },
            { data: 'ip_address' },
            { data: 'device_info' },
            { data: 'status', searchable: false },
            { data: 'notes' },
            { data: 'created_at' },
        ],
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
            emptyTable: 'Tidak ada data.',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            lengthMenu: 'Tampilkan _MENU_ data',
            search: 'Cari:',
            paginate: { previous: '<i class="fas fa-chevron-left"></i>', next: '<i class="fas fa-chevron-right"></i>' }
        },
        order: [[7, 'desc']],
    });

    $('#filterStatus, #filterApp').on('change', function() {
        table.ajax.reload();
    });
});
</script>
@stop
