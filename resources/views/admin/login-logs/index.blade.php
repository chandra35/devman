@extends('adminlte::page')

@section('title', 'Login Logs')

@section('css')
<link rel="stylesheet" href="{{ asset('css/devman-theme.css') }}">
<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
    .filter-bar { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 12px; }
    .btn-date-filter { font-size: 0.8rem; }
    .btn-date-filter.active { font-weight: 600; }
    .btn-xs { padding: 2px 6px; font-size: 0.75rem; }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 style="margin-bottom:2px"><i class="fas fa-history" style="color:var(--dm-primary)"></i> Login Logs</h1>
            <small class="text-muted">Riwayat login dari aplikasi mobile — waktu dalam WIB</small>
        </div>
    </div>
@stop

@section('content')
<div class="filter-bar">
    {{-- Filter Status --}}
    <select class="form-control form-control-sm" id="filterStatus" style="width:auto">
        <option value="">Semua Status</option>
        <option value="success">Berhasil</option>
        <option value="failed">Gagal</option>
    </select>

    {{-- Filter Aplikasi --}}
    <select class="form-control form-control-sm" id="filterApp" style="width:auto">
        <option value="">Semua Aplikasi</option>
        <option value="pusakav3">PusakaV3</option>
    </select>

    {{-- Filter Rentang Tanggal --}}
    <div class="btn-group btn-group-sm" id="dateFilterGroup">
        <button class="btn btn-outline-secondary btn-date-filter active" data-range="">Semua</button>
        <button class="btn btn-outline-secondary btn-date-filter" data-range="7days">7 Hari</button>
        <button class="btn btn-outline-secondary btn-date-filter" data-range="1month">1 Bulan</button>
        <button class="btn btn-outline-secondary btn-date-filter" data-range="3months">3 Bulan</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm" id="logsTable" width="100%">
                <thead>
                    <tr>
                        <th>Username / NIP</th>
                        <th>Nama</th>
                        <th>Aplikasi</th>
                        <th>IP Address</th>
                        <th>Device</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Waktu (WIB)</th>
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
var activeDateRange = '';

$(function() {
    table = $('#logsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.login-logs.data") }}',
            data: function(d) {
                d.status     = $('#filterStatus').val();
                d.app_name   = $('#filterApp').val();
                d.date_range = activeDateRange;
            }
        },
        columns: [
            { data: 'username' },
            { data: 'user_name', orderable: false },
            { data: 'app_name', searchable: false },
            { data: 'ip_address' },
            { data: 'device_info' },
            { data: 'status', searchable: false },
            { data: 'notes', orderable: false },
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

    // Tombol filter tanggal
    $('#dateFilterGroup').on('click', '.btn-date-filter', function() {
        $('#dateFilterGroup .btn-date-filter').removeClass('active btn-secondary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('active btn-secondary');
        activeDateRange = $(this).data('range');
        table.ajax.reload();
    });
});
</script>
@stop
