@extends('adminlte::page')

@section('title', 'Create New User')

@section('content_header')
    <h1>Create New User</h1>
@stop

@section('content')
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">User Details</h3>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="card-body row">
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

                <div class="form-group col-sm-12 col-md-6">
                    <label for="name">Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        id="name" placeholder="Enter user's full name" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group col-sm-12 col-md-6">
                    <label for="email">Email address</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        id="email" placeholder="Enter email" value="{{ old('email') }}" required>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group col-sm-12 col-md-6">
                    <label for="document">Document (CPF/CNPJ)</label>
                    <input type="text" name="document" class="form-control @error('document') is-invalid @enderror"
                        id="document" placeholder="Enter CPF or CNPJ" value="{{ old('document') }}" required>
                    @error('document')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group col-sm-12 col-md-6">
                    <label for="password">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                        id="password" placeholder="Password" required>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group col-sm-12 col-md-6">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" id="password_confirmation"
                        placeholder="Confirm Password" required>
                </div>

                <div class="form-group col-sm-12 col-md-6">
                    <label for="user_type">User Type</label>
                    <select name="user_type" id="user_type" class="form-control @error('user_type') is-invalid @enderror"
                        required>
                        <option value="">Select User Type</option>
                        @foreach (\App\Enums\UserTypeEnum::cases() as $type)
                            <option value="{{ $type->value }}" {{ old('user_type') === $type->value ? 'selected' : '' }}>
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
            <div class="card-footer col-12">
                <button type="submit" class="btn btn-info">Submit</button>
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
