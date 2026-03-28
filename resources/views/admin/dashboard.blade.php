@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalUsers }}</h3>
                <p>Total Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            @can('view-users')
            <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
            @else
            <span class="small-box-footer">&nbsp;</span>
            @endcan
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $activeUsers }}</h3>
                <p>Users Aktif</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-check"></i>
            </div>
            <span class="small-box-footer">&nbsp;</span>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $totalRoles }}</h3>
                <p>Total Roles</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tag"></i>
            </div>
            @can('view-roles')
            <a href="{{ route('admin.roles.index') }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
            @else
            <span class="small-box-footer">&nbsp;</span>
            @endcan
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $totalPermissions }}</h3>
                <p>Total Permissions</p>
            </div>
            <div class="icon">
                <i class="fas fa-key"></i>
            </div>
            @can('view-permissions')
            <a href="{{ route('admin.permissions.index') }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
            @else
            <span class="small-box-footer">&nbsp;</span>
            @endcan
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Sistem</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-bordered">
                    <tr><td width="200"><strong>Aplikasi</strong></td><td>DevMan - Device Manager</td></tr>
                    <tr><td><strong>Laravel</strong></td><td>{{ app()->version() }}</td></tr>
                    <tr><td><strong>PHP</strong></td><td>{{ PHP_VERSION }}</td></tr>
                    <tr><td><strong>Login Sebagai</strong></td><td>{{ auth()->user()->name }} ({{ auth()->user()->roles->pluck('name')->implode(', ') }})</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
