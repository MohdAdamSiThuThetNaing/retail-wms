
@extends('layouts.app')

@section('title', 'Purchase Order Details')
@section('breadcrumb', 'Purchase Orders / Details')

@section('content')

@php
    $statusLabels = [
        'draft' => 'Draft',
        'pending_approval' => 'Pending Approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'partially_received' => 'Partially Received',
        'received' => 'Received',
        'cancelled' => 'Cancelled',
    ];

    $statusClasses = [
        'draft' => 'po-status-draft',
        'pending_approval' => 'po-status-pending',
        'approved' => 'po-status-approved',
        'rejected' => 'po-status-rejected',
        'partially_received' => 'po-status-partial',
        'received' => 'po-status-received',
        'cancelled' => 'po-status-cancelled',
    ];

    $status = $purchaseOrder->status;
    $canEdit = in_array($status, ['draft', 'rejected']);
    $canReceive = in_array($status, ['approved', 'partially_received']);
    $outstandingTotal = $purchaseOrder->items->sum('outstanding_quantity');
@endphp

<div class="page-heading">
    <div>
        <div class="page-eyebrow">PURCHASING MANAGEMENT</div>
        <h1>{{ $purchaseOrder->po_number }}</h1>
        <p>Purchase order details and workflow management.</p>
    </div>

    <div class="po-detail-heading-actions">
        <a href="{{ route('purchase-orders.index') }}"
           class="btn btn-secondary">
            ← Back to Purchase Orders
        </a>

        @if($canEdit)
            @can('purchase-orders.create')
                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}"
                   class="btn btn-secondary">
                    Edit Order
                </a>
            @endcan
        @endif
    </div>
</div>

{{-- Status and workflow actions --}}
<div class="panel po-detail-status-panel">
    <div>
        <div class="po-detail-label">CURRENT STATUS</div>

        <span class="status-pill {{ $statusClasses[$status] ?? 'po-status-draft' }}">
            {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
        </span>
    </div>

    <div class="po-workflow-actions">

        @if($canEdit)
            @can('purchase-orders.create')
                <form method="POST"
                      action="{{ route('purchase-orders.submit', $purchaseOrder) }}"
                      onsubmit="return confirm('Submit this purchase order for approval?')">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        Submit for Approval
                    </button>
                </form>
            @endcan
        @endif

        @if($status === 'pending_approval')
            @can('purchase-orders.approve')
                <form method="POST"
                      action="{{ route('purchase-orders.approve', $purchaseOrder) }}"
                      onsubmit="return confirm('Approve this purchase order?')">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        Approve Order
                    </button>
                </form>

                <button type="button"
                        class="btn btn-danger"
                        onclick="document.getElementById('reject-po-form').hidden = false; this.hidden = true;">
                    Reject Order
                </button>
            @endcan
        @endif

        @if($canReceive)
            @can('inventory.receive')
                <a href="{{ route('purchase-orders.receive.create', $purchaseOrder) }}"
                   class="btn btn-primary">
                    Receive Stock
                </a>
            @endcan
        @endif

    </div>
</div>

{{-- Rejection form --}}
@if($status === 'pending_approval')
    @can('purchase-orders.approve')
        <div class="panel po-rejection-panel"
             id="reject-po-form"
             hidden>

            <div class="panel-header">
                <div>
                    <div class="panel-title">Reject Purchase Order</div>
                    <div class="panel-subtitle">
                        Please provide a reason for rejection.
                    </div>
                </div>
            </div>

            <form method="POST"
                  action="{{ route('purchase-orders.reject', $purchaseOrder) }}"
                  class="po-detail-form">
                @csrf

                <div class="form-group">
                    <label for="rejection_reason">Rejection Reason</label>
                    <textarea
                        name="rejection_reason"
                        id="rejection_reason"
                        class="form-control"
                        rows="4"
                        required
                        maxlength="2000"
                        placeholder="Explain why this purchase order is being rejected..."
                    ></textarea>
                </div>

                <div class="po-form-actions">
                    <button type="button"
                            class="btn btn-secondary"
                            onclick="document.getElementById('reject-po-form').hidden = true; document.querySelector('.po-workflow-actions .btn-danger').hidden = false;">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-danger">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    @endcan
@endif

{{-- Order information --}}
<div class="po-detail-grid">

    <div class="panel">
        <div class="panel-header">
            <div>
                <div class="panel-title">Supplier Information</div>
                <div class="panel-subtitle">Supplier details</div>
            </div>
        </div>

        <div class="po-detail-body">
            <div class="po-info-item">
                <span>Company Name</span>
                <strong>{{ $purchaseOrder->supplier->company_name }}</strong>
            </div>

            <div class="po-info-item">
                <span>Contact Person</span>
                <strong>{{ $purchaseOrder->supplier->contact_person ?: '—' }}</strong>
            </div>

            <div class="po-info-item">
                <span>Email</span>
                <strong>{{ $purchaseOrder->supplier->email ?: '—' }}</strong>
            </div>

            <div class="po-info-item">
                <span>Phone</span>
                <strong>{{ $purchaseOrder->supplier->phone ?: '—' }}</strong>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div>
                <div class="panel-title">Order Information</div>
                <div class="panel-subtitle">Delivery and warehouse details</div>
            </div>
        </div>

        <div class="po-detail-body">
            <div class="po-info-item">
                <span>Purchase Order Number</span>
                <strong>{{ $purchaseOrder->po_number }}</strong>
            </div>

            <div class="po-info-item">
                <span>Warehouse</span>
                <strong>{{ $purchaseOrder->warehouse->name }}</strong>
            </div>

            <div class="po-info-item">
                <span>Order Date</span>
                <strong>{{ $purchaseOrder->order_date->format('d M Y') }}</strong>
            </div>

            <div class="po-info-item">
                <span>Expected Delivery</span>
                <strong>
                    {{ $purchaseOrder->expected_delivery_date?->format('d M Y') ?? 'Not specified' }}
                </strong>
            </div>
        </div>
    </div>

</div>

{{-- Products --}}
<div class="panel po-detail-products-panel">

    <div class="panel-header">
        <div>
            <div class="panel-title">Ordered Products</div>
            <div class="panel-subtitle">
                {{ $purchaseOrder->items->count() }} product(s) in this order
            </div>
        </div>
    </div>

    <div class="po-items-wrap">
        <table class="po-items-table po-detail-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Ordered</th>
                    <th>Received</th>
                    <th>Outstanding</th>
                    <th>Unit Cost</th>
                    <th>Line Total</th>
                </tr>
            </thead>

            <tbody>
                @foreach($purchaseOrder->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product->name }}</strong>
                        </td>

                        <td>{{ $item->product->sku }}</td>

                        <td>{{ number_format($item->ordered_quantity) }}</td>

                        <td>
                            <span class="po-quantity-received">
                                {{ number_format($item->received_quantity) }}
                            </span>
                        </td>

                        <td>
                            <strong class="{{ $item->outstanding_quantity > 0 ? 'po-quantity-outstanding' : 'po-quantity-complete' }}">
                                {{ number_format($item->outstanding_quantity) }}
                            </strong>
                        </td>

                        <td>
                            {{ $purchaseOrder->currency }}
                            {{ number_format((float) $item->unit_cost, 2) }}
                        </td>

                        <td>
                            <strong>
                                {{ $purchaseOrder->currency }}
                                {{ number_format((float) $item->line_total, 2) }}
                            </strong>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Financial Summary --}}
<div class="panel po-detail-financial-panel">
    <div class="panel-header">
        <div>
            <div class="panel-title">Financial Summary</div>
            <div class="panel-subtitle">Purchase order totals</div>
        </div>
    </div>

    <div class="po-detail-financial-body">
        <div class="po-total-list">
            <div class="po-total-row">
                <span>Subtotal</span>
                <strong>
                    {{ $purchaseOrder->currency }}
                    {{ number_format((float) $purchaseOrder->subtotal, 2) }}
                </strong>
            </div>

            <div class="po-total-row">
                <span>Discount</span>
                <strong>
                    {{ $purchaseOrder->currency }}
                    {{ number_format((float) $purchaseOrder->discount_amount, 2) }}
                </strong>
            </div>

            <div class="po-total-row">
                <span>Tax</span>
                <strong>
                    {{ $purchaseOrder->currency }}
                    {{ number_format((float) $purchaseOrder->tax_amount, 2) }}
                </strong>
            </div>

            <div class="po-total-row po-grand-total">
                <span>Grand Total</span>
                <strong>
                    {{ $purchaseOrder->currency }}
                    {{ number_format((float) $purchaseOrder->total, 2) }}
                </strong>
            </div>
        </div>
    </div>
</div>

{{-- Notes and rejection reason --}}
@if($purchaseOrder->notes || $purchaseOrder->rejection_reason)
    <div class="panel po-detail-notes-panel">
        <div class="panel-header">
            <div class="panel-title">Additional Information</div>
        </div>

        <div class="po-detail-body">
            @if($purchaseOrder->notes)
                <div class="po-note-block">
                    <strong>Order Notes</strong>
                    <p>{{ $purchaseOrder->notes }}</p>
                </div>
            @endif

            @if($purchaseOrder->rejection_reason)
                <div class="po-note-block po-rejection-note">
                    <strong>Rejection Reason</strong>
                    <p>{{ $purchaseOrder->rejection_reason }}</p>
                </div>
            @endif
        </div>
    </div>
@endif

{{-- Approval and receiving history --}}
<div class="panel po-history-panel">
    <div class="panel-header">
        <div>
            <div class="panel-title">Approval & Receiving History</div>
            <div class="panel-subtitle">
                Purchase order activity timeline
            </div>
        </div>
    </div>

    <div class="po-history-body">

        @forelse($purchaseOrder->approvals as $approval)
            <div class="po-history-item">

                <div class="po-history-marker"></div>

                <div class="po-history-content">
                    <div class="po-history-heading">
                        <strong>
                            {{ ucfirst(str_replace('_', ' ', $approval->action)) }}
                        </strong>

                        <span>
                            {{ $approval->created_at->format('d M Y, H:i') }}
                        </span>
                    </div>

                    <div class="po-history-user">
                        {{ $approval->user->name ?? 'System / User unavailable' }}
                    </div>

                    @if($approval->comments)
                        @php
                            $historyData = json_decode($approval->comments, true);
                        @endphp

                        @if($approval->action === 'received' && is_array($historyData))
                            <div class="po-history-comments">
                                @foreach($historyData as $productName => $quantity)
                                    <div>
                                        {{ $productName }}:
                                        {{ number_format((float) $quantity) }} received
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="po-history-comments">
                                {{ $approval->comments }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="po-history-empty">
                No approval or receiving activity recorded yet.
            </div>
        @endforelse

    </div>
</div>

{{-- Audit information --}}
<div class="panel po-audit-panel">
    <div class="panel-header">
        <div class="panel-title">Audit Information</div>
    </div>

    <div class="po-detail-body">
        <div class="po-info-item">
            <span>Created By</span>
            <strong>{{ $purchaseOrder->creator->name ?? 'Unavailable' }}</strong>
        </div>

        <div class="po-info-item">
            <span>Created At</span>
            <strong>{{ $purchaseOrder->created_at->format('d M Y, H:i') }}</strong>
        </div>

        <div class="po-info-item">
            <span>Approved By</span>
            <strong>{{ $purchaseOrder->approver->name ?? '—' }}</strong>
        </div>

        <div class="po-info-item">
            <span>Approved At</span>
            <strong>
                {{ $purchaseOrder->approved_at?->format('d M Y, H:i') ?? '—' }}
            </strong>
        </div>
    </div>
</div>

@endsection
