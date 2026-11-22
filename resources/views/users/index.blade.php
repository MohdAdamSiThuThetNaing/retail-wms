@extends('layouts.app')

@section('title', 'User Management')
@section('breadcrumb', 'Users')

@section('content')
<div class="users-page">

    <div class="users-page-header">
        <div>
            <span class="users-eyebrow">ACCESS CONTROL</span>
            <h1>User Management</h1>
            <p>Manage user accounts, access status, and assigned roles.</p>
        </div>

        @can('users.create')
            <a href="{{ route('users.create') }}" class="users-btn users-btn-primary">
                <span>＋</span> Add User
            </a>
        @endcan
    </div>

    <div class="users-stats">
        <div class="users-stat-card">
            <div class="users-stat-icon users-stat-blue">♙</div>
            <div>
                <span>Total Users</span>
                <strong>{{ $totalUsers ?? $users->total() }}</strong>
            </div>
        </div>

        <div class="users-stat-card">
            <div class="users-stat-icon users-stat-green">✓</div>
            <div>
                <span>Active Users</span>
                <strong>{{ $activeUsers ?? '—' }}</strong>
            </div>
        </div>

        <div class="users-stat-card">
            <div class="users-stat-icon users-stat-red">⊘</div>
            <div>
                <span>Inactive Users</span>
                <strong>{{ $inactiveUsers ?? '—' }}</strong>
            </div>
        </div>
    </div>

    <div class="users-panel">

        <form method="GET" action="{{ route('users.index') }}" class="users-filters">
            <div class="users-search">
                <span>⌕</span>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by name or email..."
                >
            </div>

            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>

            <button type="submit" class="users-btn users-btn-secondary">
                Apply Filters
            </button>

            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('users.index') }}" class="users-clear">Clear</a>
            @endif
        </form>

        <div class="users-table-wrap">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="users-actions-heading">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="users-identity">
                                    <div class="users-avatar">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        @if(auth()->id() === $user->id)
                                            <small>You</small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>{{ $user->email }}</td>

                            <td>
                                <div class="users-role-list">
                                    @forelse($user->getRoleNames() as $role)
                                        <span class="users-role-tag">{{ $role }}</span>
                                    @empty
                                        <span class="users-muted">No role assigned</span>
                                    @endforelse
                                </div>
                            </td>

                            <td>
                                @if($user->is_active)
                                    <span class="users-status users-status-active">Active</span>
                                @else
                                    <span class="users-status users-status-inactive">Inactive</span>
                                @endif
                            </td>

                            <td>{{ $user->created_at?->format('d M Y') ?? '—' }}</td>

                            <td>
                                <div class="users-row-actions">
                                    <a href="{{ route('users.show', $user) }}" title="View">View</a>

                                    @can('users.edit')
                                        <a href="{{ route('users.edit', $user) }}" title="Edit">Edit</a>
                                    @endcan

                                    @can('users.delete')
                                        @if(auth()->id() !== $user->id)
                                            <form
                                                method="POST"
                                                action="{{ route('users.destroy', $user) }}"
                                                onsubmit="return confirm('Deactivate this user account?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="users-delete-link">
                                                    Deactivate
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="users-empty">
                                    <div>♙</div>
                                    <strong>No users found</strong>
                                    <p>Try changing your search or filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="users-pagination">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif

    </div>
</div>
@endsection