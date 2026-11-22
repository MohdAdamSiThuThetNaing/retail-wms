
@extends('layouts.app')

@section('title', 'Sales Order Details')

@section('content')
<div class="sales-order-management">
    <div class="page-header">
        <div>
            <h1>{{ $salesOrder->order_number }}</h1>
            <p>Sales order details, fulfillment status and order history.</p>
        </div>

        <div class="header-actions">
            <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary">
                Back to Orders
            </a>

            @if ($salesOrder->status === 'draft')
                @can('sales-orders.create')
                    <a href="{{ route('sales-orders.edit', $salesOrder) }}" class="btn btn-primary">
                        Edit Order
                    </a>
                @endcan
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="sales-alert success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="sales-alert error">
            <strong>Please check the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="order-overview-grid">
        <div class="content-card">
            <span class="detail-label">Order Status</span>
            <div>
                <span class="status-badge status-{{ $salesOrder->status }}">
                    {{ ucfirst($salesOrder->status) }}
                </span>
            </div>
        </div>

        <div class="content-card">
            <span class="detail-label">Order Date</span>
            <strong>{{ $salesOrder->order_date?->format('d M Y') ?? '—' }}</strong>
        </div>

        <div class="content-card">
            <span class="detail-label">Expected Delivery</span>
            <strong>{{ $salesOrder->expected_delivery_date?->format('d M Y') ?? '—' }}</strong>
        </div>

        <div class="content-card">
            <span class="detail-label">Order Total</span>
            <strong>
                {{ $salesOrder->currency }}
                {{ number_format((float) $salesOrder->total_amount, 2) }}
            </strong>
        </div>
    </div>

    <div class="detail-grid">
        <div class="content-card">
            <h2>Customer Information</h2>

            <div class="detail-list">
                <div>
                    <span>Customer Code</span>
                    <strong>{{ $salesOrder->customer->customer_code ?? '—' }}</strong>
                </div>
                <div>
                    <span>Customer Name</span>
                    <strong>{{ $salesOrder->customer->name ?? '—' }}</strong>
                </div>
                <div>
                    <span>Company</span>
                    <strong>{{ $salesOrder->customer->company_name ?? '—' }}</strong>
                </div>
                <div>
                    <span>Email</span>
                    <strong>{{ $salesOrder->customer->email ?? '—' }}</strong>
                </div>
                <div>
                    <span>Phone</span>
                    <strong>{{ $salesOrder->customer->phone ?? '—' }}</strong>
                </div>
                <div>
                    <span>Address</span>
                    <strong>{{ $salesOrder->customer->address ?? '—' }}</strong>
                </div>
            </div>
        </div>

        <div class="content-card">
            <h2>Order Information</h2>

            <div class="detail-list">
                <div>
                    <span>Order Number</span>
                    <strong>{{ $salesOrder->order_number }}</strong>
                </div>
                <div>
                    <span>Warehouse</span>
                    <strong>{{ $salesOrder->warehouse->name ?? '—' }}</strong>
                </div>
                <div>
                    <span>Created By</span>
                    <strong>{{ $salesOrder->creator->name ?? '—' }}</strong>
                </div>
                <div>
                    <span>Confirmed By</span>
                    <strong>{{ $salesOrder->confirmer->name ?? '—' }}</strong>
                </div>
                <div>
                    <span>Created At</span>
                    <strong>{{ $salesOrder->created_at?->format('d M Y, H:i') ?? '—' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <h2>Order Items</h2>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Unit Price</th>
                        <th>Discount</th>
                        <th>Line Total</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($salesOrder->items as $item)
                        <tr>
                            <td>{{ $item->product_sku }}</td>
                            <td>
                                <strong>{{ $item->product_name }}</strong>
                            </td>
                            <td>{{ number_format($item->quantity) }}</td>
                            <td>{{ $item->unit ?: '—' }}</td>
                            <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td>{{ number_format((float) $item->discount_amount, 2) }}</td>
                            <td>
                                <strong>{{ number_format((float) $item->line_total, 2) }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">No items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="order-summary">
            <div>
                <span>Subtotal</span>
                <strong>{{ $salesOrder->currency }} {{ number_format((float) $salesOrder->subtotal, 2) }}</strong>
            </div>
            <div>
                <span>Order Discount</span>
                <strong>{{ $salesOrder->currency }} {{ number_format((float) $salesOrder->discount_amount, 2) }}</strong>
            </div>
            <div>
                <span>Tax</span>
                <strong>{{ $salesOrder->currency }} {{ number_format((float) $salesOrder->tax_amount, 2) }}</strong>
            </div>
            <div class="grand-total">
                <span>Grand Total</span>
                <strong>{{ $salesOrder->currency }} {{ number_format((float) $salesOrder->total_amount, 2) }}</strong>
            </div>
        </div>
    </div>

    @if ($salesOrder->notes)
        <div class="content-card">
            <h2>Order Notes</h2>
            <p class="order-notes">{{ $salesOrder->notes }}</p>
        </div>
    @endif

    @if ($salesOrder->cancellation_reason)
        <div class="content-card">
            <h2>Cancellation Reason</h2>
            <p class="order-notes">{{ $salesOrder->cancellation_reason }}</p>
        </div>
    @endif

    @can('sales-orders.manage')
        @if (in_array($salesOrder->status, ['draft', 'confirmed', 'processing'], true))
            <div class="content-card">
                <h2>Order Workflow</h2>

                <div class="workflow-actions">
                    @if ($salesOrder->status === 'draft')
                        <form method="POST" action="{{ route('sales-orders.confirm', $salesOrder) }}"
                              onsubmit="return confirm('Confirm this sales order?')">
                            @csrf
                            <button class="btn btn-primary" type="submit">Confirm Order</button>
                        </form>
                    @endif

                    @if ($salesOrder->status === 'confirmed')
                        <form method="POST" action="{{ route('sales-orders.processing', $salesOrder) }}"
                              onsubmit="return confirm('Start processing this order?')">
                            @csrf
                            <button class="btn btn-primary" type="submit">Start Processing</button>
                        </form>
                    @endif

                    @if ($salesOrder->status === 'processing')
                        <form method="POST" action="{{ route('sales-orders.complete', $salesOrder) }}"
                              onsubmit="return confirm('Complete this order and deduct inventory?')">
                            @csrf
                            <button class="btn btn-primary" type="submit">Complete Order</button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('sales-orders.cancel', $salesOrder) }}"
                          class="cancel-order-form"
                          onsubmit="return confirm('Cancel this sales order?')">
                        @csrf
                        <input
                            type="text"
                            name="cancellation_reason"
                            placeholder="Cancellation reason (optional)"
                            maxlength="2000"
                        >
                        <button class="btn btn-danger" type="submit">Cancel Order</button>
                    </form>
                </div>
            </div>
        @endif
    @endcan

    <div class="content-card">
        <h2>Status History</h2>

        <div class="history-list">
            @forelse ($salesOrder->statusHistories as $history)
                <div class="history-item">
                    <div class="history-marker"></div>
                    <div>
                        <strong>
                            {{ ucfirst($history->previous_status ?? 'Created') }}
                            →
                            {{ ucfirst($history->new_status) }}
                        </strong>
                        <p>{{ $history->comments ?: 'Status updated.' }}</p>
                        <small>
                            {{ $history->changedBy->name ?? 'System' }}
                            · {{ $history->created_at?->format('d M Y, H:i') }}
                        </small>
                    </div>
                </div>
            @empty
                <p class="empty-state">No status history available.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
