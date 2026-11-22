
@php
    $existingItems = old('items');

    if ($existingItems === null) {
        $existingItems = isset($salesOrder)
            ? $salesOrder->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_amount' => $item->discount_amount,
                'notes' => $item->notes,
            ])->toArray()
            : [];
    }

    if (empty($existingItems)) {
        $existingItems = [[
            'product_id' => '',
            'quantity' => 1,
            'unit_price' => '',
            'discount_amount' => 0,
            'notes' => '',
        ]];
    }
@endphp

<div class="form-grid">
    <div class="form-group">
        <label for="customer_id">Customer *</label>
        <select id="customer_id" name="customer_id" required>
            <option value="">Select Customer</option>
            @foreach ($customers as $customer)
                <option
                    value="{{ $customer->id }}"
                    @selected((string) old('customer_id', $salesOrder->customer_id ?? '') === (string) $customer->id)
                >
                    {{ $customer->name }}
                    {{ $customer->company_name ? ' — '.$customer->company_name : '' }}
                </option>
            @endforeach
        </select>
        @error('customer_id') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="warehouse_id">Warehouse *</label>
        <select id="warehouse_id" name="warehouse_id" required>
            <option value="">Select Warehouse</option>
            @foreach ($warehouses as $warehouse)
                <option
                    value="{{ $warehouse->id }}"
                    @selected((string) old('warehouse_id', $salesOrder->warehouse_id ?? '') === (string) $warehouse->id)
                >
                    {{ $warehouse->name }} ({{ $warehouse->code }})
                </option>
            @endforeach
        </select>
        @error('warehouse_id') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="order_date">Order Date *</label>
        <input
            type="date"
            id="order_date"
            name="order_date"
            value="{{ old('order_date', isset($salesOrder) ? $salesOrder->order_date?->format('Y-m-d') : now()->format('Y-m-d')) }}"
            required
        >
        @error('order_date') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="expected_delivery_date">Expected Delivery Date</label>
        <input
            type="date"
            id="expected_delivery_date"
            name="expected_delivery_date"
            value="{{ old('expected_delivery_date', isset($salesOrder) ? $salesOrder->expected_delivery_date?->format('Y-m-d') : '') }}"
        >
        @error('expected_delivery_date') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="currency">Currency *</label>
        <select id="currency" name="currency" required>
            @foreach (['THB', 'USD', 'EUR', 'SGD', 'MMK'] as $currency)
                <option
                    value="{{ $currency }}"
                    @selected(old('currency', $salesOrder->currency ?? 'THB') === $currency)
                >
                    {{ $currency }}
                </option>
            @endforeach
        </select>
        @error('currency') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group full-width">
        <label for="notes">Order Notes</label>
        <textarea id="notes" name="notes" rows="3">{{ old('notes', $salesOrder->notes ?? '') }}</textarea>
        @error('notes') <small class="error-text">{{ $message }}</small> @enderror
    </div>
</div>

<div class="sales-items-section">
    <div class="section-heading">
        <div>
            <h2>Order Items</h2>
            <p>Add products and quantities for this sales order.</p>
        </div>

        <button type="button" class="btn btn-secondary" id="add-sales-item">
            + Add Product
        </button>
    </div>

    @error('items')
        <div class="sales-form-error">{{ $message }}</div>
    @enderror

    <div class="table-responsive">
        <table class="data-table sales-items-table">
            <thead>
                <tr>
                    <th style="min-width:220px">Product</th>
                    <th style="min-width:100px">Quantity</th>
                    <th style="min-width:130px">Unit Price</th>
                    <th style="min-width:130px">Line Discount</th>
                    <th style="min-width:140px">Line Total</th>
                    <th style="min-width:70px">Action</th>
                </tr>
            </thead>
            <tbody id="sales-items-body">
                @foreach ($existingItems as $index => $item)
                    <tr class="sales-item-row">
                        <td>
                            <select
                                name="items[{{ $index }}][product_id]"
                                class="product-select"
                                required
                            >
                                <option value="">Select Product</option>
                                @foreach ($products as $product)
                                    <option
                                        value="{{ $product->id }}"
                                        data-price="{{ $product->unit_price }}"
                                        data-sku="{{ $product->sku }}"
                                        @selected((string) ($item['product_id'] ?? '') === (string) $product->id)
                                    >
                                        {{ $product->name }} ({{ $product->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input
                                type="number"
                                name="items[{{ $index }}][quantity]"
                                class="item-quantity"
                                min="1"
                                step="1"
                                value="{{ $item['quantity'] ?? 1 }}"
                                required
                            >
                        </td>
                        <td>
                            <input
                                type="number"
                                name="items[{{ $index }}][unit_price]"
                                class="item-price"
                                min="0.01"
                                step="0.01"
                                value="{{ $item['unit_price'] ?? '' }}"
                                required
                            >
                        </td>
                        <td>
                            <input
                                type="number"
                                name="items[{{ $index }}][discount_amount]"
                                class="item-discount"
                                min="0"
                                step="0.01"
                                value="{{ $item['discount_amount'] ?? 0 }}"
                            >
                        </td>
                        <td>
                            <span class="item-line-total">0.00</span>
                        </td>
                        <td>
                            <button type="button" class="remove-sales-item" title="Remove product">
                                Remove
                            </button>
                        </td>
                        <input
                            type="hidden"
                            name="items[{{ $index }}][notes]"
                            value="{{ $item['notes'] ?? '' }}"
                        >
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="sales-totals-card">
    <div class="form-group">
        <label for="discount_amount">Order Discount</label>
        <input
            type="number"
            id="discount_amount"
            name="discount_amount"
            min="0"
            step="0.01"
            value="{{ old('discount_amount', $salesOrder->discount_amount ?? 0) }}"
        >
        @error('discount_amount') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="form-group">
        <label for="tax_amount">Tax Amount</label>
        <input
            type="number"
            id="tax_amount"
            name="tax_amount"
            min="0"
            step="0.01"
            value="{{ old('tax_amount', $salesOrder->tax_amount ?? 0) }}"
        >
        @error('tax_amount') <small class="error-text">{{ $message }}</small> @enderror
    </div>

    <div class="sales-total-lines">
        <div>
            <span>Subtotal</span>
            <strong id="sales-subtotal">0.00</strong>
        </div>
        <div>
            <span>Order Discount</span>
            <strong id="sales-discount">0.00</strong>
        </div>
        <div>
            <span>Tax</span>
            <strong id="sales-tax">0.00</strong>
        </div>
        <div class="grand-total">
            <span>Grand Total</span>
            <strong id="sales-grand-total">0.00</strong>
        </div>
    </div>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary">Cancel</a>
</div>

<template id="sales-item-template">
    <tr class="sales-item-row">
        <td>
            <select class="product-select" required>
                <option value="">Select Product</option>
                @foreach ($products as $product)
                    <option
                        value="{{ $product->id }}"
                        data-price="{{ $product->unit_price }}"
                        data-sku="{{ $product->sku }}"
                    >
                        {{ $product->name }} ({{ $product->sku }})
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" class="item-quantity" min="1" step="1" value="1" required>
        </td>
        <td>
            <input type="number" class="item-price" min="0.01" step="0.01" required>
        </td>
        <td>
            <input type="number" class="item-discount" min="0" step="0.01" value="0">
        </td>
        <td><span class="item-line-total">0.00</span></td>
        <td>
            <button type="button" class="remove-sales-item" title="Remove product">
                Remove
            </button>
        </td>
        <input type="hidden" class="item-notes" value="">
    </tr>
</template>
