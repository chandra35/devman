@extends('adminlte::page')

@section('title', 'User Pusaka')

@section('css')
<link rel="stylesheet" href="{{ asset('css/devman-theme.css') }}">
<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link rel="stylesheet" href="//unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
#mapContainer { height: 300px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 8px; }
.coord-info { font-size: 12px; color: #666; }
.nip-result { font-size: 12px; padding: 8px; border-radius: 6px; display: none; }
.map-search-box { position: relative; }
.map-search-box .search-results { position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; background: #fff; border: 1px solid #ddd; border-top: 0; border-radius: 0 0 6px 6px; max-height: 200px; overflow-y: auto; display: none; }
.map-search-box .search-results .search-item { padding: 6px 10px; cursor: pointer; font-size: 12px; border-bottom: 1px solid #f0f0f0; }
.map-search-box .search-results .search-item:hover { background: #f8f9fa; }
</style>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formPusaka">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="modalPusakaTitle"><i class="fas fa-mosque mr-2"></i>Tambah User Pusaka</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pusakaId">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIP <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="pusakaNip" name="nip" required placeholder="NIP 18 digit" maxlength="18">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-info" id="btnCekNip" onclick="cekNip()">
                                            <i class="fas fa-search"></i> Cek NIP
                                        </button>
                                    </div>
                                </div>
                                <div id="nipResult" class="nip-result mt-1"></div>
                            </div>
                            <div class="form-group">
                                <label>Nama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pusakaName" name="name" required placeholder="Nama lengkap">
                            </div>
                            <div class="form-group">
                                <label>Nama Lengkap (Kemenag)</label>
                                <input type="text" class="form-control form-control-sm" id="pusakaNamaLengkap" name="nama_lengkap" placeholder="Auto-fill dari Cek NIP" readonly>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Jabatan</label>
                                        <input type="text" class="form-control form-control-sm" id="pusakaJabatan" name="jabatan" placeholder="Dari Cek NIP" readonly>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Gol. Ruang</label>
                                        <input type="text" class="form-control form-control-sm" id="pusakaGolongan" name="golongan" placeholder="Dari Cek NIP" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Satuan Kerja</label>
                                <input type="text" class="form-control form-control-sm" id="pusakaSatker" name="satker" placeholder="Dari Cek NIP" readonly>
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt text-danger mr-1"></i>Lokasi Presensi</label>
                                <div class="map-search-box mb-1">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="mapSearch" placeholder="Cari lokasi / alamat..." autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" onclick="searchLocation()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="goToMyLocation()" title="Lokasi saya saat ini">
                                                <i class="fas fa-crosshairs"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="searchResults" class="search-results"></div>
                                </div>
                                <div id="mapContainer"></div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-0">
                                            <label class="small">Latitude</label>
                                            <input type="text" class="form-control form-control-sm" id="pusakaLat" name="latitude" placeholder="-5.120118" readonly>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-0">
                                            <label class="small">Longitude</label>
                                            <input type="text" class="form-control form-control-sm" id="pusakaLng" name="longitude" placeholder="105.328819" readonly>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="resetMapToDefault()">
                                    <i class="fas fa-undo mr-1"></i>Reset ke MAN 1 Metro
                                </button>
                            </div>
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
<script src="//unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var table, map, marker;
var defaultLat = -5.120118, defaultLng = 105.328819;

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

    $('#modalPusaka').on('shown.bs.modal', function() {
        if (!map) {
            initMap();
        } else {
            map.invalidateSize();
        }
    });
});

function initMap() {
    map = L.map('mapContainer').setView([defaultLat, defaultLng], 18);

    // Layer: Satelit (Google Hybrid - sudah ada label jalan/kota)
    var satellite = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        attribution: '&copy; Google',
        maxZoom: 21,
    });

    // Layer: Peta Jalan (OpenStreetMap - detail nama lokal lengkap)
    var street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 19,
    });

    // Default: satelit
    satellite.addTo(map);

    // Layer switcher
    L.control.layers({
        'Satelit': satellite,
        'Peta Jalan': street,
    }, null, { position: 'topright' }).addTo(map);

    marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    marker.on('dragend', function(e) {
        var pos = e.target.getLatLng();
        updateCoordFields(pos.lat, pos.lng);
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateCoordFields(e.latlng.lat, e.latlng.lng);
    });
}

function updateCoordFields(lat, lng) {
    $('#pusakaLat').val(lat.toFixed(7));
    $('#pusakaLng').val(lng.toFixed(7));
}

function setMapPosition(lat, lng) {
    if (map && marker) {
        var latlng = L.latLng(lat, lng);
        map.setView(latlng, 18);
        marker.setLatLng(latlng);
        updateCoordFields(lat, lng);
    }
}

function resetMapToDefault() {
    setMapPosition(defaultLat, defaultLng);
}

// ==================== CEK NIP ====================
function cekNip() {
    var nip = $('#pusakaNip').val().trim();
    if (nip.length !== 18 || !/^\d{18}$/.test(nip)) {
        toastr.warning('NIP harus 18 digit angka');
        return;
    }

    var btn = $('#btnCekNip');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    $('#nipResult').hide();

    $.ajax({
        url: '{{ route("admin.pusaka-users.check-nip") }}',
        method: 'POST',
        data: { nip: nip },
        success: function(res) {
            if (res.success && res.data) {
                var d = res.data;
                $('#pusakaName').val(d.NAMA || d.NAMA_LENGKAP || '');
                $('#pusakaNamaLengkap').val(d.NAMA_LENGKAP || d.NAMA || '');
                $('#pusakaJabatan').val(d.TAMPIL_JABATAN || d.TIPE_JABATAN || '');
                $('#pusakaSatker').val(d.SATKER_1 || '');
                $('#pusakaGolongan').val(d.GOL_RUANG || '');
                $('#nipResult').html('<i class="fas fa-check-circle text-success"></i> <strong>' + (d.NAMA_LENGKAP || d.NAMA) + '</strong> — ' + (d.SATKER_1 || '-'))
                    .removeClass('bg-danger-light').addClass('bg-success-light').css({'background': '#d4edda', 'color': '#155724'}).slideDown();
            } else {
                $('#nipResult').html('<i class="fas fa-times-circle"></i> ' + (res.message || 'NIP tidak ditemukan'))
                    .css({'background': '#f8d7da', 'color': '#721c24'}).slideDown();
            }
        },
        error: function(xhr) {
            $('#nipResult').html('<i class="fas fa-exclamation-triangle"></i> Gagal menghubungi API')
                .css({'background': '#f8d7da', 'color': '#721c24'}).slideDown();
        },
        complete: function() {
            btn.prop('disabled', false).html('<i class="fas fa-search"></i> Cek NIP');
        }
    });
}

// ==================== SEARCH LOCATION (Nominatim) ====================
var searchTimeout;
$('#mapSearch').on('keyup', function(e) {
    clearTimeout(searchTimeout);
    if (e.keyCode === 13) { searchLocation(); return; }
    var q = $(this).val().trim();
    if (q.length < 3) { $('#searchResults').hide(); return; }
    searchTimeout = setTimeout(function() { searchLocation(); }, 500);
});

function searchLocation() {
    var q = $('#mapSearch').val().trim();
    if (q.length < 2) { toastr.warning('Masukkan kata kunci pencarian'); return; }

    $.ajax({
        url: 'https://nominatim.openstreetmap.org/search',
        data: {
            q: q,
            format: 'json',
            limit: 8,
            addressdetails: 1,
            countrycodes: 'id',
            'accept-language': 'id',
        },
        headers: { 'Accept-Language': 'id' },
        success: function(results) {
            var container = $('#searchResults').empty();
            if (results.length === 0) {
                container.html('<div class="search-item text-muted">Tidak ditemukan. Coba kata kunci lain (misal: nama kecamatan/kota)</div>').show();
                return;
            }
            results.forEach(function(r) {
                var label = r.display_name;
                // Tampilkan tipe lokasi jika ada
                var typeLabel = r.type ? '<small class="text-muted ml-1">(' + r.type.replace(/_/g,' ') + ')</small>' : '';
                var item = $('<div class="search-item"></div>')
                    .html(label + typeLabel)
                    .on('click', function() {
                        var zoom = r.type === 'city' || r.type === 'town' ? 14 : (r.type === 'village' || r.type === 'suburb' ? 16 : 18);
                        map.setView([parseFloat(r.lat), parseFloat(r.lon)], zoom);
                        marker.setLatLng([parseFloat(r.lat), parseFloat(r.lon)]);
                        updateCoordFields(parseFloat(r.lat), parseFloat(r.lon));
                        container.hide();
                        $('#mapSearch').val('');
                    });
                container.append(item);
            });
            container.show();
        },
        error: function() {
            toastr.error('Gagal mencari lokasi');
        }
    });
}

// Hide search results when clicking outside
$(document).on('click', function(e) {
    if (!$(e.target).closest('.map-search-box').length) {
        $('#searchResults').hide();
    }
});

// ==================== MY LOCATION ====================
function goToMyLocation() {
    if (!navigator.geolocation) {
        toastr.error('Browser tidak mendukung geolokasi');
        return;
    }
    toastr.info('Mencari lokasi Anda...');
    navigator.geolocation.getCurrentPosition(
        function(pos) {
            setMapPosition(pos.coords.latitude, pos.coords.longitude);
            toastr.success('Lokasi ditemukan!');
        },
        function(err) {
            toastr.error('Gagal mendapatkan lokasi: ' + err.message);
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

function createPusakaUser() {
    $('#formPusaka')[0].reset();
    $('#pusakaId').val('');
    $('#modalPusakaTitle').html('<i class="fas fa-mosque mr-2"></i>Tambah User Pusaka');
    $('#pusakaActive').prop('checked', true);
    $('#pusakaLat').val(defaultLat.toFixed(7));
    $('#pusakaLng').val(defaultLng.toFixed(7));
    $('#nipResult').hide();
    $('#pusakaNamaLengkap, #pusakaJabatan, #pusakaSatker, #pusakaGolongan').val('');
    $('#modalPusaka').modal('show');

    setTimeout(function() {
        setMapPosition(defaultLat, defaultLng);
    }, 300);
}

function editPusakaUser(id) {
    $('#formPusaka')[0].reset();
    $('#pusakaId').val(id);
    $('#modalPusakaTitle').html('<i class="fas fa-mosque mr-2"></i>Edit User Pusaka');
    $('#nipResult').hide();

    $.get('/admin/pusaka-users/' + id, function(res) {
        $('#pusakaNip').val(res.data.nip);
        $('#pusakaName').val(res.data.name);
        $('#pusakaNamaLengkap').val(res.data.nama_lengkap || '');
        $('#pusakaJabatan').val(res.data.jabatan || '');
        $('#pusakaSatker').val(res.data.satker || '');
        $('#pusakaGolongan').val(res.data.golongan || '');
        $('#pusakaNotes').val(res.data.notes);
        $('#pusakaActive').prop('checked', res.data.is_active);

        var lat = res.data.latitude ? parseFloat(res.data.latitude) : defaultLat;
        var lng = res.data.longitude ? parseFloat(res.data.longitude) : defaultLng;
        $('#pusakaLat').val(lat.toFixed(7));
        $('#pusakaLng').val(lng.toFixed(7));
        $('#modalPusaka').modal('show');

        setTimeout(function() {
            setMapPosition(lat, lng);
        }, 300);
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
