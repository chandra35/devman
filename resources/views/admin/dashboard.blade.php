@extends('adminlte::page')

@section('title', 'Dashboard')

@section('css')
<link rel="stylesheet" href="{{ asset('css/devman-theme.css') }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 style="margin-bottom:2px"><i class="fas fa-tachometer-alt" style="color:var(--dm-primary)"></i> Dashboard</h1>
            <small class="text-muted">Welcome back, {{ auth()->user()->name }}!</small>
        </div>
        <span class="badge badge-primary" style="font-size:0.8rem;padding:0.5em 1em">
            <i class="fas fa-clock mr-1"></i> {{ now()->translatedFormat('l, d F Y') }}
        </span>
    </div>
@stop

@section('content')
{{-- Stat Cards --}}
<div class="row">
    <div class="col-xl-3 col-md-6 col-12 mb-3">
        <div class="stat-card stat-bg-primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $totalUsers }}</div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            @can('view-users')
            <div class="stat-footer">
                <a href="{{ route('admin.users.index') }}">Lihat Detail <i class="fas fa-arrow-right ml-1" style="font-size:0.7rem"></i></a>
            </div>
            @endcan
        </div>
    </div>

    <div class="col-xl-3 col-md-6 col-12 mb-3">
        <div class="stat-card stat-bg-success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $activeUsers }}</div>
                    <div class="stat-label">Users Aktif</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
            <div class="stat-footer">
                <span style="font-size:0.8rem;color:var(--dm-success)">
                    <i class="fas fa-circle" style="font-size:0.5rem;vertical-align:middle"></i>
                    {{ $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0 }}% dari total
                </span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 col-12 mb-3">
        <div class="stat-card stat-bg-warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $totalRoles }}</div>
                    <div class="stat-label">Total Roles</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-user-tag"></i>
                </div>
            </div>
            @can('view-roles')
            <div class="stat-footer">
                <a href="{{ route('admin.roles.index') }}">Lihat Detail <i class="fas fa-arrow-right ml-1" style="font-size:0.7rem"></i></a>
            </div>
            @endcan
        </div>
    </div>

    <div class="col-xl-3 col-md-6 col-12 mb-3">
        <div class="stat-card stat-bg-danger">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $totalPermissions }}</div>
                    <div class="stat-label">Total Permissions</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-key"></i>
                </div>
            </div>
            @can('view-permissions')
            <div class="stat-footer">
                <a href="{{ route('admin.permissions.index') }}">Lihat Detail <i class="fas fa-arrow-right ml-1" style="font-size:0.7rem"></i></a>
            </div>
            @endcan
        </div>
    </div>
</div>

{{-- System Info --}}
<div class="row">
    <div class="col-lg-8">
        <div class="card info-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-server mr-2" style="color:var(--dm-primary)"></i>Informasi Sistem</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td width="200" class="pl-4"><i class="fas fa-laptop-code mr-2 text-muted"></i>Aplikasi</td>
                        <td><strong>DevMan</strong> &mdash; Device Manager</td>
                    </tr>
                    <tr>
                        <td class="pl-4"><i class="fab fa-laravel mr-2 text-muted"></i>Laravel</td>
                        <td>{{ app()->version() }}</td>
                    </tr>
                    <tr>
                        <td class="pl-4"><i class="fab fa-php mr-2 text-muted"></i>PHP</td>
                        <td>{{ PHP_VERSION }}</td>
                    </tr>
                    <tr>
                        <td class="pl-4"><i class="fas fa-user-shield mr-2 text-muted"></i>Login</td>
                        <td>
                            <strong>{{ auth()->user()->name }}</strong>
                            @foreach(auth()->user()->roles as $role)
                                <span class="badge badge-primary ml-1">{{ $role->name }}</span>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt mr-2" style="color:var(--dm-warning)"></i>Quick Actions</h3>
            </div>
            <div class="card-body">
                @can('view-users')
                <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-users mr-2"></i> Kelola Users
                </a>
                @endcan
                @can('view-roles')
                <a href="{{ route('admin.roles.index') }}" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-user-tag mr-2"></i> Kelola Roles
                </a>
                @endcan
                <a href="{{ route('admin.profile') }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-user-circle mr-2"></i> My Profile
                </a>
            </div>
        </div>
    </div>
</div>
@stop
