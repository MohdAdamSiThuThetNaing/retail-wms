
@csrf

<div class="form-grid">
    <div class="form-group">
        <label>Product Name *</label>
        <input class="form-control" name="name" required maxlength="180"
               value="{{ old('name', $product->name ?? '') }}"
               placeholder="Enter product name">
    </div>

    <div class="form-group">
        <label>SKU *</label>
        <input class="form-control" name="sku" required maxlength="64"
               value="{{ old('sku', $product->sku ?? '') }}"
               placeholder="e.g. PROD-0001">
    </div>

    <div class="form-group">
        <label>Barcode</label>
        <input class="form-control" name="barcode" maxlength="100"
               value="{{ old('barcode', $product->barcode ?? '') }}"
               placeholder="Enter barcode">
    </div>

    <div class="form-group">
        <label>Category</label>
        <select class="form-control" name="category_id">
            <option value="">Uncategorized</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}"
                    @selected(old('category_id', $product->category_id ?? '') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Brand</label>
        <input class="form-control" name="brand" maxlength="120"
               value="{{ old('brand', $product->brand ?? '') }}"
               placeholder="Brand name">
    </div>

    <div class="form-group">
        <label>Unit of Measurement *</label>
        <select class="form-control" name="unit" required>
            @foreach(['pcs', 'box', 'pack', 'kg', 'g', 'liter', 'meter', 'set'] as $unit)
                <option value="{{ $unit }}"
                    @selected(old('unit', $product->unit ?? 'pcs') === $unit)>
                    {{ strtoupper($unit) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Cost Price *</label>
        <input class="form-control" type="number" name="cost_price"
               min="0" step="0.01" required
               value="{{ old('cost_price', $product->cost_price ?? '0.00') }}">
    </div>

    <div class="form-group">
        <label>Selling Price *</label>
        <input class="form-control" type="number" name="unit_price"
               min="0" step="0.01" required
               value="{{ old('unit_price', $product->unit_price ?? '0.00') }}">
    </div>

    <div class="form-group form-full">
        <label>Description</label>
        <textarea class="form-control" name="description" rows="4"
                  placeholder="Product description">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    <div class="form-group">
        <label>Product Image</label>
        <input class="form-control" type="file" name="image"
               accept=".jpg,.jpeg,.png,.webp">
        <small>JPG, PNG or WEBP. Maximum 2 MB.</small>
    </div>

    <div class="form-group">
        <label>Product Status</label>
        <input type="hidden" name="is_active" value="0">
        <label class="checkbox-label">
            <input type="checkbox" name="is_active" value="1"
                @checked(old('is_active', $product->is_active ?? true))>
            Active product
        </label>
    </div>
</div>

<div class="form-actions">
    <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
        {{ isset($product) ? 'Save Changes' : 'Create Product' }}
    </button>
</div>
