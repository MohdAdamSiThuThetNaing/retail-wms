@extends('layouts.app')

@section('title', 'User Details')
@section('breadcrumb', 'User Details')

@section('content')
<div class="users-page">

    <div class="users-page-header">
        <div>
            <span class="users-eyebrow">USER MANAGEMENT</span>
            <h1>User Details</h1>
            <p>Review account information and assigned permissions.</p>
        </div>

        <div class="users-header-actions">
            <a href="{{ route('users.index') }}" class="users-btn users-btn-secondary">
                ← Back
            </a>

            @can('users.edit')
                <a href="{{ route('users.edit', $user) }}" class="users-btn users-btn-primary">
                    Edit User
                </a>
            @endcan
        </div>
    </div>

    <div class="users-panel users-detail-panel">

        <div class="users-detail-heading">
            <div class="users-avatar users-avatar-large">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>

            <div>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>

                @if($user->is_active)
                    <span class="users-status users-status-active">Active</span>
                @else
                    <span class="users-status users-status-inactive">Inactive</span>
                @endif
            </div>
        </div>

        <div class="users-detail-grid">
            <div class="users-detail-item">
                <span>User ID</span>
                <strong>#{{ $user->id }}</strong>
            </div>

            <div class="users-detail-item">
                <span>Created At</span>
                <strong>{{ $user->created_at?->format('d M Y, H:i') ?? '—' }}</strong>
            </div>

            <div class="users-detail-item">
                <span>Last Updated</span>
                <strong>{{ $user->updated_at?->format('d M Y, H:i') ?? '—' }}</strong>
            </div>
        </div>

        <div class="users-detail-section">
            <h3>Assigned Roles</h3>

            <div class="users-role-list">
                @forelse($user->getRoleNames() as $role)
                    <span class="users-role-tag">{{ $role }}</span>
                @empty
                    <span class="users-muted">No roles assigned.</span>
                @endforelse
            </div>
        </div>

        <div class="users-detail-section">
            <h3>Effective Permissions</h3>

            <div class="users-permission-list">
                @forelse($user->getAllPermissions()->sortBy('name') as $permission)
                    <span>{{ $permission->name }}</span>
                @empty
                    <span class="users-muted">No permissions assigned.</span>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection