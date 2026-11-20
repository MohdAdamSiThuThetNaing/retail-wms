@extends('layouts.app')

@section('title', 'Warehouses')
@section('breadcrumb', 'Warehouses')

@section('content')
<section class="page-heading">
    <div>
        <h1>Warehouse Management</h1>
        <div class="subtitle">Manage warehouse facilities, contacts and storage capacity.</div>
    </div>
    <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
        + Add Warehouse
    </a>
</section>

<section class="stats-grid product-stats">
    <article class="stat-card">
        <div class="stat-label">Total Warehouses</div>
        <div class="stat-value">{{ number_format($stats['total']) }}</div>
    </article>
    <article class="stat-card">
        <div class="stat-label">Active Warehouses</div>
        <div class="stat-value">{{ number_format($stats['active']) }}</div>
    </article>
    <article class="stat-card">
        <div class="stat-label">Inactive Warehouses</div>
        <div class="stat-value">{{ number_format($stats['inactive']) }}</div>
    </article>
    <article class="stat-card">
        <div class="stat-label">Total Stock Units</div>
        <div class="stat-value">{{ number_format((float) $stats['stock_units']) }}</div>
    </article>
</section>

<div class="panel product-panel">
    <div class="panel-header">
        <div>
            <div class="panel-title">Warehouse Directory</div>
            <div class="panel-subtitle">Search and manage registered warehouses</div>
        </div>
    </div>

    <form method="GET" action="{{ route('warehouses.index') }}" class="filter-bar">
        <input
            type="search"
            name="q"
            value="{{ request('q') }}"
            placeholder="Search warehouse name, code or address..."
            class="form-control search-input"
        >

        <select name="status" class="form-control">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>

        <button class="btn btn-primary" type="submit">Filter</button>
        <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">Reset</a>
    </form>

    <div class="table-wrap">
        <table class="product-table">
            <thead>
                <tr>
                    <th>Warehouse</th>
                    <th>Code</th>
                    <th>Contact</th>
                    <th>Stock Records</th>
                    <th>Stock Units</th>
                    <th>Locations</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouses as $warehouse)
                    <tr>
                        <td>
                            <div class="product-name">{{ $warehouse->name }}</div>
                            <div class="secondary">{{ $warehouse->address ?: 'No address' }}</div>
                        </td>
                        <td><strong>{{ $warehouse->code }}</strong></td>
                        <td>
                            <div>{{ $warehouse->contact_person ?: '—' }}</div>
                            <div class="secondary">{{ $warehouse->phone ?: $warehouse->email ?: 'No contact' }}</div>
                        </td>
                        <td>{{ number_format($warehouse->stocks_count) }}</td>
                        <td>{{ number_format((float) ($warehouse->stocks_sum_quantity ?? 0)) }}</td>
                        <td>{{ number_format($warehouse->locations_count) }}</td>
                        <td>
                            <span class="status-pill {{ $warehouse->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="action-links">
                                <a href="{{ route('warehouses.show', $warehouse) }}">View</a>
                                <a href="{{ route('warehouses.edit', $warehouse) }}">Edit</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty">
                            No warehouses found. Add your first warehouse to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $warehouses->links() }}
    </div>
</div>
@endsection
