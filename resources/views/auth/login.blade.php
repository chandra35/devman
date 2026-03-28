@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
    <style>
        .login-page { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
@stop

@section('auth_header', 'Login DevMan')

@section('auth_body')
    <form action="{{ route('login') }}" method="POST" id="loginForm">
        @csrf

        <div class="input-group mb-3">
            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                   value="{{ old('username') }}" placeholder="Username" autofocus required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-user"></span>
                </div>
            </div>
            @error('username')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="Password" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
            @error('password')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="row">
            <div class="col-7">
                <div class="icheck-primary">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Ingat Saya</label>
                </div>
            </div>
            <div class="col-5">
                <button type="submit" class="btn btn-primary btn-block btn-flat">
                    <i class="fas fa-sign-in-alt mr-1"></i> Masuk
                </button>
            </div>
        </div>
    </form>
@stop
