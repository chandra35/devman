@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('css/devman-theme.css') }}">
@stop

@section('auth_header')
    <div style="text-align:center">
        <div style="width:64px;height:64px;background:linear-gradient(135deg,#6366f1,#818cf8);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;box-shadow:0 8px 24px rgba(99,102,241,0.3)">
            <i class="fas fa-laptop-code" style="font-size:1.6rem;color:#fff"></i>
        </div>
        <h4 style="font-weight:700;color:#1e293b;margin:0">DevMan</h4>
        <p style="color:#94a3b8;font-size:0.85rem;margin:4px 0 0">Sign in to your account</p>
    </div>
@stop

@section('auth_body')
    <form action="{{ route('login') }}" method="POST" id="loginForm">
        @csrf

        <div class="form-group">
            <label for="username">Username</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-user" style="color:#6366f1"></i></span>
                </div>
                <input type="text" name="username" id="username"
                       class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username') }}" placeholder="Enter username" autofocus required>
            </div>
            @error('username')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-lock" style="color:#6366f1"></i></span>
                </div>
                <input type="password" name="password" id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Enter password" required>
            </div>
            @error('password')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="custom-control-label" for="remember" style="font-size:0.85rem">Remember me</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="padding:0.65rem">
            <i class="fas fa-sign-in-alt mr-1"></i> Sign In
        </button>
    </form>
@stop

@section('auth_footer')
    <p class="text-center text-muted mb-0" style="font-size:0.8rem">
        &copy; {{ date('Y') }} DevMan &mdash; All rights reserved
    </p>
@stop
