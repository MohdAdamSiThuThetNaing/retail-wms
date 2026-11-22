
@extends('layouts.app')

@section('title', 'Receive Purchase Order')
@section('breadcrumb', 'Purchase Orders / Receive Stock')

@section('content')

@php
    $statusLabels = [
        'approved' => 'Approved',
        'partially_received' => 'Partially Received',
    ];

    $totalOrdered = $purchaseOrder->items->sum('ordered_quantity');
    $totalReceived = $purchaseOrder->items->sum('received_quantity');
    $totalOutstanding = $purchaseOrder->items->sum(
        fn ($item) => max(0, $item->ordered_quantity - $item->received_quantity)
    );
@endphp

<div class="page-heading">
    <div>
        <div class="page-eyebrow">PURCHASING MANAGEMENT</div>
        <h1>Receive Stock</h1>
        <p>Record received quantities against {{ $purchaseOrder->po_number }}.</p>
    </div>

    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}"
       class="btn btn-secondary">
        ← Back to Purchase Order
    </a>
</div>

{{-- Purchase Order Summary --}}
<div class="panel">
    <div class="panel-header">
        <div>
            <div class="panel-title">Purchase Order Information</div>
            <div class="panel-subtitle">Order and delivery details</div>
        </div>

        <span class="status-pill {{ $purchaseOrder->status === 'approved' ? 'po-status-approved' : 'po-status-partial' }}">
            {{ $statusLabels[$purchaseOrder->status] ?? ucfirst($purchaseOrder->status) }}
        </span>
    </div>

    <div class="po-detail-body">
        <div class="po-info-item">
            <span>Purchase Order</span>
            <strong>{{ $purchaseOrder->po_number }}</strong>
        </div>

        <div class="po-info-item">
            <span>Supplier</span>
            <strong>{{ $purchaseOrder->supplier->company_name }}</strong>
        </div>

        <div class="po-info-item">
            <span>Warehouse</span>
            <strong>{{ $purchaseOrder->warehouse->name }}</strong>
        </div>

        <div class="po-info-item">
            <span>Order Date</span>
            <strong>{{ $purchaseOrder->order_date->format('d M Y') }}</strong>
        </div>
    </div>
</div>

{{-- Receiving Summary --}}
<div class="po-detail-grid">
    <div class="panel">
        <div class="po-detail-body">
            <div class="po-detail-label">TOTAL ORDERED</div>
            <h2>{{ number_format($totalOrdered) }}</h2>
        </div>
    </div>

    <div class="panel">
        <div class="po-detail-body">
            <div class="po-detail-label">TOTAL RECEIVED</div>
            <h2>{{ number_format($totalReceived) }}</h2>
        </div>
    </div>

    <div class="panel">
        <div class="po-detail-body">
            <div class="po-detail-label">OUTSTANDING</div>
            <h2>{{ number_format($totalOutstanding) }}</h2>
        </div>
    </div>
</div>

{{-- Validation Errors --}}
@if($errors->any())
    <div class="panel" style="border-left: 4px solid #dc3545;">
        <strong style="color: #dc3545;">Please correct the following:</strong>

        <ul style="margin: 10px 0 0 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Receiving Form --}}
<form method="POST"
      action="{{ route('purchase-orders.receive.store', $purchaseOrder) }}">

    @csrf

    <div class="panel po-detail-products-panel">

        <div class="panel-header">
            <div>
                <div class="panel-title">Products to Receive</div>
                <div class="panel-subtitle">
                    Enter the quantity received for each product.
                    Enter zero for products not received.
                </div>
            </div>
        </div>

        <div class="po-items-wrap">
            <table class="po-items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Ordered</th>
                        <th>Received</th>
                        <th>Outstanding</th>
                        <th>Receive Quantity</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($purchaseOrder->items as $item)
                        @php
                            $outstanding = max(
                                0,
                                (int) $item->ordered_quantity -
                                (int) $item->received_quantity
                            );
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $item->product->name }}</strong>
                            </td>

                            <td>{{ $item->product->sku }}</td>

                            <td>
                                {{ number_format($item->ordered_quantity) }}
                            </td>

                            <td>
                                <span class="po-quantity-received">
                                    {{ number_format($item->received_quantity) }}
                                </span>
                            </td>

                            <td>
                                <strong class="{{ $outstanding > 0 ? 'po-quantity-outstanding' : 'po-quantity-complete' }}">
                                    {{ number_format($outstanding) }}
                                </strong>
                            </td>

                            <td style="min-width: 150px;">
                                @if($outstanding > 0)
                                    <input
                                        type="number"
                                        name="items[{{ $item->id }}]"
                                        class="form-control"
                                        min="0"
                                        max="{{ $outstanding }}"
                                        step="1"
                                        value="{{ old('items.' . $item->id, 0) }}"
                                        required
                                    >

                                    @error('items.' . $item->id)
                                        <small style="color: #dc3545;">
                                            {{ $message }}
                                        </small>
                                    @enderror
                                @else
                                    <span class="po-quantity-complete">
                                        Fully Received
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Receiving Notes --}}
    <div class="panel">
        <div class="panel-header">
            <div>
                <div class="panel-title">Receiving Notes</div>
                <div class="panel-subtitle">
                    Optional notes about this delivery.
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>

            <textarea
                name="notes"
                id="notes"
                class="form-control"
                rows="4"
                maxlength="2000"
                placeholder="Enter delivery notes, discrepancies, or other information..."
            >{{ old('notes') }}</textarea>

            @error('notes')
                <small style="color: #dc3545;">{{ $message }}</small>
            @enderror
        </div>
    </div>

    {{-- Actions --}}
    <div class="po-form-actions">
        <a href="{{ route('purchase-orders.show', $purchaseOrder) }}"
           class="btn btn-secondary">
            Cancel
        </a>

        <button type="submit"
                class="btn btn-primary"
                onclick="return confirm('Confirm receiving the entered stock quantities?')">
            Confirm Stock Receiving
        </button>
    </div>

</form>

@endsection
