
@extends('layouts.app')

@section('title', 'Customer Management')

@section('content')
<div class="customer-management">

    <div class="page-header">
        <div>
            <h1>Customer Management</h1>
            <p>Manage customer profiles, account details and customer types.</p>
        </div>

        @can('customers.manage')
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                + Add Customer
            </a>
        @endcan
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Total Customers</span>
            <strong>{{ number_format($stats['total']) }}</strong>
        </div>

        <div class="stat-card">
            <span class="stat-label">Active Customers</span>
            <strong>{{ number_format($stats['active']) }}</strong>
        </div>

        <div class="stat-card">
            <span class="stat-label">Inactive Customers</span>
            <strong>{{ number_format($stats['inactive']) }}</strong>
        </div>

        <div class="stat-card">
            <span class="stat-label">New This Month</span>
            <strong>{{ number_format($stats['new_this_month']) }}</strong>
        </div>
    </div>

    <div class="content-card">

        <form method="GET" action="{{ route('customers.index') }}" class="filter-form">

            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search code, name, company, email or phone..."
            >

            <select name="type">
                <option value="">All Customer Types</option>
                @foreach (['individual', 'retail', 'wholesale', 'corporate'] as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>
                        {{ ucfirst($type) }}
                    </option>
                @endforeach
            </select>

            <select name="status">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>
                    Active
                </option>
                <option value="inactive" @selected(request('status') === 'inactive')>
                    Inactive
                </option>
            </select>

            <button type="submit" class="btn btn-primary">Filter</button>

            <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                Reset
            </a>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Customer Code</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th>Phone</th>
                        <th>Credit Limit</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>{{ $customer->customer_code }}</td>

                            <td>
                                <strong>{{ $customer->name }}</strong>

                                @if ($customer->company_name)
                                    <br>
                                    <small>{{ $customer->company_name }}</small>
                                @endif
                            </td>

                            <td>{{ ucfirst($customer->customer_type) }}</td>

                            <td>{{ $customer->email ?: '—' }}</td>

                            <td>{{ $customer->phone ?: '—' }}</td>

                            <td>
                                {{ number_format((float) $customer->credit_limit, 2) }}
                            </td>

                            <td>
                                <span class="status-badge {{ $customer->is_active ? 'active' : 'inactive' }}">
                                    {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('customers.show', $customer) }}">View</a>

                                @can('customers.manage')
                                    <a href="{{ route('customers.edit', $customer) }}">Edit</a>

                                    @if ($customer->is_active)
                                        <form
                                            method="POST"
                                            action="{{ route('customers.destroy', $customer) }}"
                                            style="display:inline"
                                            onsubmit="return confirm('Deactivate this customer?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="link-button">
                                                Deactivate
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                No customers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $customers->links() }}
        </div>

    </div>
</div>
@endsection
