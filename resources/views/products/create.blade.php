
@extends('layouts.app')

@section('title', 'Create Product')
@section('breadcrumb', 'Products / Create')

@section('content')
<section class="page-heading">
    <div>
        <h1>Create Product</h1>
        <div class="subtitle">Register a new product in your catalog.</div>
    </div>
</section>

<div class="panel form-panel">
    <div class="panel-header">
        <div class="panel-title">Product Information</div>
    </div>

    <form method="POST" action="{{ route('products.store') }}"
          enctype="multipart/form-data" class="form-body">
        @include('products._form')
    </form>
</div>
@endsection
