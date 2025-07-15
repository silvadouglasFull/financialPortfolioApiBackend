@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
    <h1>Edit User</h1>
@stop

@section('content')
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">Edit User Details (#{{ $user->id }})</h3>
        </div>
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT') {{-- Laravel convention for update requests --}}
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

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        id="name" placeholder="Enter user's full name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        id="email" placeholder="Enter email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="document">Document (CPF/CNPJ)</label>
                    <input type="text" name="document" class="form-control @error('document') is-invalid @enderror"
                        id="document" placeholder="Enter CPF or CNPJ" value="{{ old('document', $user->document) }}"
                        required>
                    @error('document')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password (Leave blank to keep current password)</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                        id="password" placeholder="New Password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" id="password_confirmation"
                        placeholder="Confirm New Password">
                </div>

                <div class="form-group">
                    <label for="user_type">User Type</label>
                    <select name="user_type" id="user_type" class="form-control @error('user_type') is-invalid @enderror"
                        required>
                        <option value="">Select User Type</option>
                        @foreach (\App\Enums\UserTypeEnum::cases() as $type)
                            <option value="{{ $type->value }}"
                                {{ old('user_type', $user->user_type->value) === $type->value ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type->name)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">Update</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary float-right">Cancel</a>
            </div>
        </form>
    </div>
@stop

@section('css')
    {{-- Add any specific CSS for this page if needed --}}
@stop

@section('js')
    {{-- Add any specific JS for this page if needed --}}
@stop
