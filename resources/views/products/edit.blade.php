
@extends('layouts.app')

@section('title', 'Edit Product')
@section('breadcrumb', 'Products / Edit')

@section('content')
<section class="page-heading">
    <div>
        <h1>Edit Product</h1>
        <div class="subtitle">Update {{ $product->name }}.</div>
    </div>
</section>

<div class="panel form-panel">
    <div class="panel-header">
        <div class="panel-title">Product Information</div>
    </div>

    <form method="POST"
          action="{{ route('products.update', $product) }}"
          enctype="multipart/form-data"
          class="form-body">
        @method('PUT')

        @if($product->image_path)
            <div class="current-image">
                <img src="{{ asset('storage/'.$product->image_path) }}"
                     alt="{{ $product->name }}">
                <span>Current product image</span>
            </div>
        @endif

        @include('products._form')
    </form>
</div>
@endsection
