@extends('layouts.app')

@section('title', 'Create User')
@section('breadcrumb', 'Create User')

@section('content')
<div class="users-page">

    <div class="users-page-header">
        <div>
            <span class="users-eyebrow">USER MANAGEMENT</span>
            <h1>Create User</h1>
            <p>Create a new account and assign access permissions.</p>
        </div>

        <a href="{{ route('users.index') }}" class="users-btn users-btn-secondary">
            ← Back to Users
        </a>
    </div>

    <div class="users-panel users-form-panel">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            @include('users._form')

            <div class="users-form-footer">
                <a href="{{ route('users.index') }}" class="users-btn users-btn-secondary">
                    Cancel
                </a>

                <button type="submit" class="users-btn users-btn-primary">
                    Create User
                </button>
            </div>
        </form>
    </div>

</div>
@endsection