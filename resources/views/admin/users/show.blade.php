@extends('adminlte::page')

@section('title', 'User Details')

@section('content_header')
    <h1>User Details</h1>
@stop

@section('content')
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">User Information (#{{ $user->id }})</h3>
            <div class="card-tools">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm" title="Edit User">
                    <i class="fas fa-edit"></i> Edit User
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm" title="Back to User List">
                    <i class="fas fa-list"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <p><strong>Name:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Document:</strong> {{ $user->document }}</p>
                    <p><strong>User Type:</strong> <span class="badge bg-info">{{ $user->user_type->value }}</span></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Balance:</strong> R$ {{ number_format($user->balance, 2, ',', '.') }}</p>
                    <p><strong>Created At:</strong> {{ $user->created_at->format('Y/m/d H:i:s') }}</p>
                    <p><strong>Last Updated:</strong> {{ $user->updated_at->format('Y/m/d H:i:s') }}</p>
                    <p><strong>Email Verified At:</strong>
                        {{ $user->email_verified_at ? $user->email_verified_at->format('Y/m/d H:i:s') : 'Not verified' }}
                    </p>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to User List
            </a>
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning float-right">
                <i class="fas fa-edit"></i> Edit User
            </a>
        </div>
    </div>
@stop

@section('css')
    {{-- Add any specific CSS for this page if needed --}}
@stop

@section('js')
    {{-- Add any specific JS for this page if needed --}}
@stop
