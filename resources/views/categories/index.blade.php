
@extends('layouts.app')

@section('title', 'Categories')
@section('breadcrumb', 'Categories')

@section('content')
<section class="page-heading">
    <div>
        <h1>Product Categories</h1>
        <div class="subtitle">Organize and manage your product catalog.</div>
    </div>
</section>

{{-- Create Category --}}
<div class="panel category-form-panel">
    <div class="panel-header">
        <div class="panel-title">Create Category</div>
    </div>

    <form method="POST" action="{{ route('categories.store') }}" class="form-body">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label>Category Name *</label>
                <input
                    class="form-control"
                    name="name"
                    required
                    maxlength="120"
                    value="{{ old('name') }}"
                    placeholder="e.g. Electronics"
                >
            </div>

            <div class="form-group">
                <label>Description</label>
                <input
                    class="form-control"
                    name="description"
                    maxlength="255"
                    value="{{ old('description') }}"
                    placeholder="Category description"
                >
            </div>

            <div class="form-group">
                <label>Status</label>
                <input type="hidden" name="is_active" value="0">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" checked>
                    Active
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">
                Create Category
            </button>
        </div>
    </form>
</div>

{{-- Category List --}}
<div class="panel product-panel">
    <div class="panel-header">
        <div>
            <div class="panel-title">Category List</div>
            <div class="panel-subtitle">Edit category details and manage status.</div>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td colspan="5">
                            <form
                                method="POST"
                                action="{{ route('categories.update', $category) }}"
                                class="category-row-form"
                            >
                                @csrf
                                @method('PUT')

                                <div class="category-edit-grid">
                                    <div>
                                        <label>Category Name</label>
                                        <input
                                            class="form-control"
                                            name="name"
                                            value="{{ $category->name }}"
                                            maxlength="120"
                                            required
                                        >
                                    </div>

                                    <div>
                                        <label>Description</label>
                                        <input
                                            class="form-control"
                                            name="description"
                                            value="{{ $category->description }}"
                                            maxlength="255"
                                        >
                                    </div>

                                    <div>
                                        <label>Products</label>
                                        <div class="category-product-count">
                                            {{ $category->products_count }}
                                        </div>
                                    </div>

                                    <div>
                                        <label>Status</label>
                                        <input type="hidden" name="is_active" value="0">

                                        <label class="checkbox-label">
                                            <input
                                                type="checkbox"
                                                name="is_active"
                                                value="1"
                                                {{ $category->is_active ? 'checked' : '' }}
                                            >
                                            Active
                                        </label>
                                    </div>

                                    <div class="category-actions">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="category-delete-row">
                                <span class="status-pill {{ $category->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>

                                <form
                                    method="POST"
                                    action="{{ route('categories.destroy', $category) }}"
                                    onsubmit="return confirm('Are you sure you want to delete this category?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="text-danger">
                                        Delete Category
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty">
                            No categories created yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $categories->links() }}
    </div>
</div>
@endsection
