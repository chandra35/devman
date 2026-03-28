@extends('adminlte::page')

@section('title', 'Profile')

@section('css')
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('content_header')
    <h1><i class="fas fa-user-circle"></i> Profile</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-edit"></i> Update Profile</h3>
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
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-lock"></i> Ubah Password</h3>
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
