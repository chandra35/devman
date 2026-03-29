@extends('adminlte::page')

@section('title', 'Login Logs')

@section('css')
<link rel="stylesheet" href="{{ asset('css/devman-theme.css') }}">
<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
    .filter-card { background:#fff; border:1px solid #dee2e6; border-radius:8px; padding:16px 20px; margin-bottom:16px; box-shadow:0 1px 3px rgba(0,0,0,.06); }
    .filter-card .filter-label { font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; margin-bottom:4px; }
    .quick-range-btn { border-radius:20px !important; font-size:0.78rem; padding:3px 12px; transition:all .15s; }
    .quick-range-btn.active { background:var(--dm-primary, #007bff) !important; border-color:var(--dm-primary, #007bff) !important; color:#fff !important; font-weight:600; }
    .filter-divider { width:1px; background:#dee2e6; margin:0 8px; align-self:stretch; }
    #filterReset { display:none; }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 style="margin-bottom:2px"><i class="fas fa-history" style="color:var(--dm-primary)"></i> Login Logs</h1>
            <small class="text-muted">Riwayat login dari aplikasi mobile &mdash; waktu dalam <strong>WIB</strong></small>
        </div>
    </div>
@stop

@section('content')
<div class="filter-card">
    <div class="row align-items-end" style="gap:0">
        {{-- Status --}}
        <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
            <div class="filter-label"><i class="fas fa-check-circle mr-1"></i>Status</div>
            <select class="form-control form-control-sm" id="filterStatus">
                <option value="">Semua Status</option>
                <option value="success">&#10003; Berhasil</option>
                <option value="failed">&#10007; Gagal</option>
            </select>
        </div>

        {{-- Aplikasi --}}
        <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
            <div class="filter-label"><i class="fas fa-mobile-alt mr-1"></i>Aplikasi</div>
            <select class="form-control form-control-sm" id="filterApp">
                <option value="">Semua Aplikasi</option>
                <option value="pusakav3">PusakaV3</option>
            </select>
        </div>

        <div class="filter-divider d-none d-md-block"></div>

        {{-- Quick range --}}
        <div class="col-md-auto mb-2 mb-md-0">
            <div class="filter-label"><i class="fas fa-bolt mr-1"></i>Rentang Cepat</div>
            <div class="btn-group btn-group-sm" id="quickRangeGroup">
                <button class="btn btn-outline-secondary quick-range-btn active" data-range="">Semua</button>
                <button class="btn btn-outline-secondary quick-range-btn" data-range="7days">7 Hari</button>
                <button class="btn btn-outline-secondary quick-range-btn" data-range="1month">1 Bulan</button>
                <button class="btn btn-outline-secondary quick-range-btn" data-range="3months">3 Bulan</button>
            </div>
        </div>

        <div class="filter-divider d-none d-md-block"></div>

        {{-- Custom date range --}}
        <div class="col-md mb-2 mb-md-0">
            <div class="filter-label"><i class="fas fa-calendar-alt mr-1"></i>Tanggal Kustom</div>
            <div class="input-group input-group-sm">
                <div class="input-group-prepend"><span class="input-group-text">Dari</span></div>
                <input type="date" class="form-control" id="filterDateFrom" placeholder="Dari">
                <div class="input-group-prepend input-group-append"><span class="input-group-text">s/d</span></div>
                <input type="date" class="form-control" id="filterDateTo" placeholder="Sampai">
                <div class="input-group-append">
                    <button class="btn btn-primary" id="filterApply" title="Terapkan"><i class="fas fa-search"></i></button>
                    <button class="btn btn-outline-secondary" id="filterReset" title="Reset"><i class="fas fa-times"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0 pt-3 px-3">
        <div class="table-responsive">
            <table class="table table-hover table-sm" id="logsTable" width="100%">
                <thead class="thead-light">
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
var activeQuickRange = '';
var customDateFrom = '';
var customDateTo = '';
var usingCustomDate = false;

$(function() {
    table = $('#logsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.login-logs.data") }}',
            data: function(d) {
                d.status     = $('#filterStatus').val();
                d.app_name   = $('#filterApp').val();
                if (usingCustomDate) {
                    d.date_range = '';
                    d.date_from  = customDateFrom;
                    d.date_to    = customDateTo;
                } else {
                    d.date_range = activeQuickRange;
                    d.date_from  = '';
                    d.date_to    = '';
                }
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

    // Filter status & app
    $('#filterStatus, #filterApp').on('change', function() {
        table.ajax.reload();
    });

    // Quick range buttons
    $('#quickRangeGroup').on('click', '.quick-range-btn', function() {
        $('#quickRangeGroup .quick-range-btn').removeClass('active');
        $(this).addClass('active');
        activeQuickRange = $(this).data('range');
        // Reset custom date
        usingCustomDate = false;
        customDateFrom = '';
        customDateTo = '';
        $('#filterDateFrom, #filterDateTo').val('');
        $('#filterReset').hide();
        table.ajax.reload();
    });

    // Terapkan custom date
    $('#filterApply').on('click', function() {
        customDateFrom = $('#filterDateFrom').val();
        customDateTo   = $('#filterDateTo').val();
        if (!customDateFrom && !customDateTo) return;
        // Deactivate quick range
        usingCustomDate = true;
        activeQuickRange = '';
        $('#quickRangeGroup .quick-range-btn').removeClass('active');
        $('#filterReset').show();
        table.ajax.reload();
    });

    // Reset custom date
    $('#filterReset').on('click', function() {
        usingCustomDate = false;
        customDateFrom = '';
        customDateTo = '';
        $('#filterDateFrom, #filterDateTo').val('');
        $(this).hide();
        // Re-activate "Semua"
        $('#quickRangeGroup .quick-range-btn').first().addClass('active');
        activeQuickRange = '';
        table.ajax.reload();
    });

    // Enter on date fields = apply
    $('#filterDateFrom, #filterDateTo').on('keydown', function(e) {
        if (e.key === 'Enter') $('#filterApply').trigger('click');
    });
});
</script>
@stop
