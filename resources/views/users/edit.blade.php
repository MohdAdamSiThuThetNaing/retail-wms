@extends('layouts.app')

@section('title', 'Edit User')
@section('breadcrumb', 'Edit User')

@section('content')
<div class="users-page">

    <div class="users-page-header">
        <div>
            <span class="users-eyebrow">USER MANAGEMENT</span>
            <h1>Edit User</h1>
            <p>Update account details, roles, and access status.</p>
        </div>

        <a href="{{ route('users.index') }}" class="users-btn users-btn-secondary">
            ← Back to Users
        </a>
    </div>

    <div class="users-panel users-form-panel">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')

            @include('users._form')

            <div class="users-form-footer">
                <a href="{{ route('users.index') }}" class="users-btn users-btn-secondary">
                    Cancel
                </a>

                <button type="submit" class="users-btn users-btn-primary">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection