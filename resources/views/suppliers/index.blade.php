
@extends('layouts.app')

@section('title', 'Suppliers')
@section('breadcrumb', 'Suppliers')

@section('content')

<section class="page-heading">
    <div>
        <h1>Supplier Management</h1>
        <div class="subtitle">Manage suppliers, contacts and payment terms.</div>
    </div>

    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
        + Add Supplier
    </a>
</section>

<section class="stats-grid product-stats">
    <article class="stat-card">
        <div class="stat-label">Total Suppliers</div>
        <div class="stat-value">{{ number_format($stats['total']) }}</div>
    </article>

    <article class="stat-card">
        <div class="stat-label">Active Suppliers</div>
        <div class="stat-value">{{ number_format($stats['active']) }}</div>
    </article>

    <article class="stat-card">
        <div class="stat-label">Inactive Suppliers</div>
        <div class="stat-value">{{ number_format($stats['inactive']) }}</div>
    </article>
</section>

<div class="panel product-panel">
    <div class="panel-header">
        <div>
            <div class="panel-title">Supplier Directory</div>
            <div class="panel-subtitle">Search and manage registered suppliers.</div>
        </div>
    </div>

    <form method="GET" action="{{ route('suppliers.index') }}" class="filter-bar">
        <input
            type="search"
            name="q"
            value="{{ request('q') }}"
            placeholder="Search supplier, code, contact or email..."
            class="form-control search-input"
        >

        <select name="status" class="form-control">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>

        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Reset</a>
    </form>

    <div class="table-wrap">
        <table class="product-table">
            <thead>
                <tr>
                    <th>Supplier</th>
                    <th>Supplier Code</th>
                    <th>Contact</th>
                    <th>Email / Phone</th>
                    <th>Payment Terms</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($suppliers as $supplier)
                    <tr>
                        <td>
                            <strong>{{ $supplier->company_name }}</strong>
                        </td>

                        <td>{{ $supplier->supplier_code }}</td>

                        <td>{{ $supplier->contact_person ?: '—' }}</td>

                        <td>
                            <div>{{ $supplier->email ?: '—' }}</div>
                            <div class="secondary">{{ $supplier->phone ?: '' }}</div>
                        </td>

                        <td>
                            {{ [
                                'cash' => 'Cash',
                                'net_15' => 'Net 15',
                                'net_30' => 'Net 30',
                                'net_60' => 'Net 60',
                            ][$supplier->payment_terms] ?? $supplier->payment_terms }}
                        </td>

                        <td>
                            <span class="status-pill {{ $supplier->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <td>
                            <div class="action-links">
                                <a href="{{ route('suppliers.show', $supplier) }}">View</a>
                                <a href="{{ route('suppliers.edit', $supplier) }}">Edit</a>

                                <form
                                    method="POST"
                                    action="{{ route('suppliers.destroy', $supplier) }}"
                                    onsubmit="return confirm('Delete this supplier?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty">
                            No suppliers found. Add your first supplier to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $suppliers->links() }}
    </div>
</div>

@endsection
