@extends('adminlte::page')

@section('title', 'Profile')

@section('css')
<link rel="stylesheet" href="{{ asset('css/devman-theme.css') }}">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('content_header')
    <div>
        <h1 style="margin-bottom:2px"><i class="fas fa-user-circle" style="color:var(--dm-primary)"></i> Profile</h1>
        <small class="text-muted">Kelola informasi akun Anda</small>
    </div>
@stop

@section('content')
{{-- User Info Card --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="card" style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); border: none;">
            <div class="card-body d-flex align-items-center">
                <div style="width:64px;height:64px;background:linear-gradient(135deg,var(--dm-primary),var(--dm-primary-light));border-radius:16px;display:flex;align-items:center;justify-content:center;margin-right:1.25rem;box-shadow:0 4px 12px rgba(99,102,241,0.3)">
                    <i class="fas fa-user" style="font-size:1.5rem;color:#fff"></i>
                </div>
                <div>
                    <h5 style="margin:0;font-weight:700;color:var(--dm-dark)">{{ auth()->user()->name }}</h5>
                    <p style="margin:2px 0 0;color:var(--dm-secondary);font-size:0.875rem">
                        <span class="mr-3"><i class="fas fa-at mr-1"></i>{{ auth()->user()->username }}</span>
                        @foreach(auth()->user()->roles as $role)
                            <span class="badge badge-primary">{{ $role->name }}</span>
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-edit mr-2" style="color:var(--dm-primary)"></i>Update Profile</h3>
            </div>
            <form id="formProfile">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ auth()->user()->name }}" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="{{ auth()->user()->username }}" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ auth()->user()->email }}">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-lock mr-2" style="color:var(--dm-warning)"></i>Ubah Password</h3>
            </div>
            <form id="formPassword">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="current_password">Password Saat Ini</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-key"></i> Ubah Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

    // Update Profile
    $('#formProfile').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('button[type=submit]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: '{{ route("admin.profile.update") }}',
            method: 'PUT',
            data: $(this).serialize(),
            success: function(res) {
                toastr.success(res.message);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, val) {
                        toastr.error(val[0]);
                    });
                } else {
                    toastr.error('Terjadi kesalahan.');
                }
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
            }
        });
    });

    // Change Password
    $('#formPassword').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('button[type=submit]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengubah...');

        $.ajax({
            url: '{{ route("admin.profile.password") }}',
            method: 'PUT',
            data: $(this).serialize(),
            success: function(res) {
                toastr.success(res.message);
                $('#formPassword')[0].reset();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, val) {
                        toastr.error(val[0]);
                    });
                } else {
                    toastr.error('Terjadi kesalahan.');
                }
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-key"></i> Ubah Password');
            }
        });
    });
});
</script>
@stop
