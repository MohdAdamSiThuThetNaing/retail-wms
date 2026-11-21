
@extends('layouts.app')

@section('title', 'Edit Purchase Order')
@section('breadcrumb', 'Edit Purchase Order')

@section('content')

@php
    $initialItems = old('items', $purchaseOrder->items->map(function ($item) {
        return [
            'product_id' => $item->product_id,
            'ordered_quantity' => $item->ordered_quantity,
            'unit_cost' => $item->unit_cost,
        ];
    })->values()->all());
@endphp

<section class="page-heading">
    <div>
        <h1>Edit Purchase Order</h1>
        <div class="subtitle">
            Update order {{ $purchaseOrder->po_number }}.
        </div>
    </div>

    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">
        ← Back to Purchase Order
    </a>
</section>

<form method="POST"
      action="{{ route('purchase-orders.update', $purchaseOrder) }}"
      id="purchase-order-form">

    @csrf
    @method('PUT')

    {{-- Order Information --}}
    <div class="panel po-form-panel">

        <div class="panel-header">
            <div>
                <div class="panel-title">Order Information</div>
                <div class="panel-subtitle">
                    Update supplier, warehouse and delivery details.
                </div>
            </div>
        </div>

        <div class="form-grid">

            <div class="form-group">
                <label for="supplier_id">Supplier *</label>
                <select name="supplier_id" id="supplier_id" class="form-control" required>
                    <option value="">Select supplier</option>

                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}"
                            @selected(old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id)>
                            {{ $supplier->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="warehouse_id">Destination Warehouse *</label>
                <select name="warehouse_id" id="warehouse_id" class="form-control" required>
                    <option value="">Select warehouse</option>

                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}"
                            @selected(old('warehouse_id', $purchaseOrder->warehouse_id) == $warehouse->id)>
                            {{ $warehouse->name }} ({{ $warehouse->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="order_date">Order Date *</label>
                <input
                    type="date"
                    name="order_date"
                    id="order_date"
                    class="form-control"
                    value="{{ old('order_date', $purchaseOrder->order_date?->format('Y-m-d')) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="expected_delivery_date">Expected Delivery Date</label>
                <input
                    type="date"
                    name="expected_delivery_date"
                    id="expected_delivery_date"
                    class="form-control"
                    value="{{ old('expected_delivery_date', $purchaseOrder->expected_delivery_date?->format('Y-m-d')) }}"
                >
            </div>

            <div class="form-group">
                <label for="currency">Currency *</label>
                <select name="currency" id="currency" class="form-control" required>
                    @foreach(['THB', 'USD', 'EUR', 'MMK', 'SGD', 'CNY', 'JPY'] as $currency)
                        <option value="{{ $currency }}"
                            @selected(old('currency', $purchaseOrder->currency) === $currency)>
                            {{ $currency }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    {{-- Order Items --}}
    <div class="panel po-form-panel">

        <div class="panel-header po-items-header">
            <div>
                <div class="panel-title">Order Items</div>
                <div class="panel-subtitle">
                    Update products, quantities and unit costs.
                </div>
            </div>

            <button type="button" class="btn btn-primary btn-sm" id="add-po-item">
                + Add Product
            </button>
        </div>

        <div class="po-items-wrap">
            <table class="product-table po-items-table">
                <thead>
                    <tr>
                        <th style="min-width: 260px;">Product *</th>
                        <th style="min-width: 120px;">Quantity *</th>
                        <th style="min-width: 140px;">Unit Cost *</th>
                        <th style="min-width: 140px;">Line Total</th>
                        <th style="width: 75px;">Action</th>
                    </tr>
                </thead>

                <tbody id="po-items-body"></tbody>
            </table>
        </div>

        <div class="po-add-mobile">
            <button type="button" class="btn btn-secondary" id="add-po-item-mobile">
                + Add Another Product
            </button>
        </div>

    </div>

    {{-- Financial Summary --}}
    <div class="panel po-form-panel">

        <div class="panel-header">
            <div>
                <div class="panel-title">Financial Summary</div>
                <div class="panel-subtitle">Review updated order costs.</div>
            </div>
        </div>

        <div class="po-summary-body">

            <div class="po-summary-fields">

                <div class="form-group">
                    <label for="discount_amount">Discount Amount</label>
                    <input
                        type="number"
                        name="discount_amount"
                        id="discount_amount"
                        class="form-control"
                        min="0"
                        step="0.01"
                        value="{{ old('discount_amount', $purchaseOrder->discount_amount) }}"
                    >
                </div>

                <div class="form-group">
                    <label for="tax_amount">Tax Amount</label>
                    <input
                        type="number"
                        name="tax_amount"
                        id="tax_amount"
                        class="form-control"
                        min="0"
                        step="0.01"
                        value="{{ old('tax_amount', $purchaseOrder->tax_amount) }}"
                    >
                </div>

            </div>

            <div class="po-total-list">

                <div class="po-total-row">
                    <span>Subtotal</span>
                    <strong id="po-subtotal">0.00</strong>
                </div>

                <div class="po-total-row">
                    <span>Discount</span>
                    <strong id="po-discount">0.00</strong>
                </div>

                <div class="po-total-row">
                    <span>Tax</span>
                    <strong id="po-tax">0.00</strong>
                </div>

                <div class="po-total-row po-grand-total">
                    <span>Grand Total</span>
                    <strong id="po-grand-total">0.00</strong>
                </div>

            </div>

            <div class="po-currency-note">
                All amounts are calculated in the selected order currency.
            </div>

        </div>
    </div>

    {{-- Notes --}}
    <div class="panel po-form-panel">

        <div class="panel-header">
            <div>
                <div class="panel-title">Additional Notes</div>
                <div class="panel-subtitle">Optional order instructions or remarks.</div>
            </div>
        </div>

        <div class="po-notes-body">
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea
                    name="notes"
                    id="notes"
                    class="form-control"
                    rows="4"
                    placeholder="Enter delivery instructions or additional information..."
                >{{ old('notes', $purchaseOrder->notes) }}</textarea>
            </div>
        </div>

    </div>

    {{-- Form Actions --}}
    <div class="po-form-actions">

        <a href="{{ route('purchase-orders.show', $purchaseOrder) }}"
           class="btn btn-secondary">
            Cancel
        </a>

        <button type="submit" class="btn btn-primary" id="save-po-button">
            Update Purchase Order
        </button>

    </div>

</form>

{{-- Product Options --}}
<template id="po-product-options">
    <option value="">Select product</option>

    @foreach($products as $product)
        <option
            value="{{ $product->id }}"
            data-cost="{{ number_format((float) $product->cost_price, 2, '.', '') }}"
        >
            {{ $product->sku }} — {{ $product->name }}
        </option>
    @endforeach
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const itemsBody = document.getElementById('po-items-body');
    const productTemplate = document.getElementById('po-product-options');

    const discountInput = document.getElementById('discount_amount');
    const taxInput = document.getElementById('tax_amount');
    const currencyInput = document.getElementById('currency');

    const initialItems = @json($initialItems);

    let itemIndex = 0;

    function formatMoney(value) {
        return Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function addItem(productId = '', quantity = 1, unitCost = '') {

        const row = document.createElement('tr');
        const index = itemIndex++;

        row.className = 'po-item-row';

        row.innerHTML = `
            <td>
                <select
                    name="items[${index}][product_id]"
                    class="form-control po-product-select"
                    required
                >
                    ${productTemplate.innerHTML}
                </select>
            </td>

            <td>
                <input
                    type="number"
                    name="items[${index}][ordered_quantity]"
                    class="form-control po-quantity"
                    min="1"
                    step="1"
                    value="${quantity}"
                    required
                >
            </td>

            <td>
                <input
                    type="number"
                    name="items[${index}][unit_cost]"
                    class="form-control po-unit-cost"
                    min="0.01"
                    step="0.01"
                    value="${unitCost}"
                    placeholder="0.00"
                    required
                >
            </td>

            <td>
                <strong class="po-line-total">0.00</strong>
            </td>

            <td>
                <button
                    type="button"
                    class="po-remove-item"
                    aria-label="Remove product"
                    title="Remove product"
                >
                    ×
                </button>
            </td>
        `;

        itemsBody.appendChild(row);

        const select = row.querySelector('.po-product-select');

        if (productId) {
            select.value = String(productId);
        }

        calculateTotals();
    }

    function calculateTotals() {

        let subtotal = 0;

        itemsBody.querySelectorAll('.po-item-row').forEach(row => {

            const quantity = Number(row.querySelector('.po-quantity').value) || 0;
            const unitCost = Number(row.querySelector('.po-unit-cost').value) || 0;

            const lineTotal = quantity * unitCost;

            row.querySelector('.po-line-total').textContent =
                formatMoney(lineTotal);

            subtotal += lineTotal;
        });

        const discount = Math.max(0, Number(discountInput.value) || 0);
        const tax = Math.max(0, Number(taxInput.value) || 0);
        const currency = currencyInput.value;

        document.getElementById('po-subtotal').textContent =
            currency + ' ' + formatMoney(subtotal);

        document.getElementById('po-discount').textContent =
            currency + ' ' + formatMoney(discount);

        document.getElementById('po-tax').textContent =
            currency + ' ' + formatMoney(tax);

        document.getElementById('po-grand-total').textContent =
            currency + ' ' + formatMoney(subtotal - discount + tax);
    }

    document.getElementById('add-po-item').addEventListener('click', () => addItem());

    document.getElementById('add-po-item-mobile').addEventListener('click', () => addItem());

    itemsBody.addEventListener('input', function (event) {
        if (
            event.target.matches('.po-quantity') ||
            event.target.matches('.po-unit-cost')
        ) {
            calculateTotals();
        }
    });

    itemsBody.addEventListener('change', function (event) {

        if (event.target.matches('.po-product-select')) {

            const row = event.target.closest('tr');
            const selectedOption = event.target.selectedOptions[0];

            row.querySelector('.po-unit-cost').value =
                selectedOption?.dataset.cost || '';

            calculateTotals();
        }
    });

    itemsBody.addEventListener('click', function (event) {

        if (event.target.matches('.po-remove-item')) {

            const rows = itemsBody.querySelectorAll('.po-item-row');

            if (rows.length <= 1) {
                alert('At least one product is required.');
                return;
            }

            event.target.closest('tr').remove();
            calculateTotals();
        }
    });

    discountInput.addEventListener('input', calculateTotals);
    taxInput.addEventListener('input', calculateTotals);
    currencyInput.addEventListener('change', calculateTotals);

    document.getElementById('purchase-order-form').addEventListener('submit', function (event) {

        const rows = itemsBody.querySelectorAll('.po-item-row');

        if (rows.length === 0) {
            event.preventDefault();
            alert('Please add at least one product.');
            return;
        }

        const selectedProducts = [];

        for (const row of rows) {

            const productId = row.querySelector('.po-product-select').value;
            const quantity = Number(row.querySelector('.po-quantity').value);
            const unitCost = Number(row.querySelector('.po-unit-cost').value);

            if (
                !productId ||
                !Number.isInteger(quantity) ||
                quantity < 1 ||
                !Number.isFinite(unitCost) ||
                unitCost <= 0
            ) {
                event.preventDefault();
                alert('Please select a product and enter a valid quantity and unit cost.');
                return;
            }

            if (selectedProducts.includes(productId)) {
                event.preventDefault();
                alert('Each product can only be added once to a purchase order.');
                return;
            }

            selectedProducts.push(productId);
        }

        const subtotal = Array.from(rows).reduce((sum, row) => {
            return sum +
                Number(row.querySelector('.po-quantity').value) *
                Number(row.querySelector('.po-unit-cost').value);
        }, 0);

        const discount = Number(discountInput.value) || 0;

        if (discount > subtotal) {
            event.preventDefault();
            alert('Discount cannot exceed the subtotal.');
            return;
        }

        document.getElementById('save-po-button').disabled = true;
        document.getElementById('save-po-button').textContent = 'Updating...';
    });

    // Load existing purchase order items.
    if (initialItems.length > 0) {
        initialItems.forEach(item => {
            addItem(
                item.product_id,
                item.ordered_quantity,
                item.unit_cost
            );
        });
    } else {
        addItem();
    }

});
</script>

@endsection
