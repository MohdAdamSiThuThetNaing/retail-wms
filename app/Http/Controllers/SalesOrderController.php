<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderStatusHistory;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\NotificationService;

class SalesOrderController extends Controller
{
    /**
     * Display sales orders.
     */
    public function index(Request $request)
    {
        $query = SalesOrder::with([
            'customer',
            'warehouse',
            'creator',
        ]);

        if ($request->filled('q')) {
            $search = $request->q;

            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $salesOrders = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => SalesOrder::count(),
            'draft' => SalesOrder::where('status', 'draft')->count(),
            'confirmed' => SalesOrder::where('status', 'confirmed')->count(),
            'processing' => SalesOrder::where('status', 'processing')->count(),
            'completed' => SalesOrder::where('status', 'completed')->count(),
            'cancelled' => SalesOrder::where('status', 'cancelled')->count(),
        ];

        return view('sales-orders.index', compact(
            'salesOrders',
            'stats'
        ));
    }

    /**
     * Show the sales order creation form.
     */
    public function create()
    {
        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('sales-orders.create', compact(
            'customers',
            'warehouses',
            'products'
        ));
    }

    /**
     * Store a new draft sales order.
     */
    public function store(
        Request $request,
        NotificationService $notifications
    )
    {
        $data = $this->validateOrder($request);

        $this->ensureActiveCustomerAndWarehouse(
            (int) $data['customer_id'],
            (int) $data['warehouse_id']
        );

        return DB::transaction(function () use (
            $data,
            $request,
            $notifications
        ) {
            $products = $this->getActiveProducts($data['items']);

            $items = $this->prepareItems($data['items'], $products);

            $totals = $this->calculateTotals(
                $items,
                $data['discount_amount'] ?? 0,
                $data['tax_amount'] ?? 0
            );

            $salesOrder = SalesOrder::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => SalesOrder::STATUS_DRAFT,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount'],
                'tax_amount' => $totals['tax'],
                'total_amount' => $totals['total'],
                'currency' => strtoupper($data['currency']),
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($items as $item) {
                $salesOrder->items()->create($item);
            }

            $this->recordStatus(
                $salesOrder,
                null,
                SalesOrder::STATUS_DRAFT,
                'Sales order created.',
                $request->user()->id
            );

            $salesOrderId = $salesOrder->id;
            $actorId = $request->user()->id;

            DB::afterCommit(function () use (
                $notifications,
                $salesOrderId,
                $actorId
            ) {
                $order = SalesOrder::find($salesOrderId);

                if (!$order) {
                    return;
                }

                $notifications->notifyPermissions(
                    permissions: [
                        'sales-orders.view',
                        'sales-orders.manage',
                    ],
                    category: 'sales',
                    title: 'New Sales Order Created',
                    message: "Sales Order {$order->order_number} has been created.",
                    url: route('sales-orders.show', $order),
                    metadata: [
                        'sales_order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'action' => 'created',
                    ],
                    exceptUserId: $actorId
                );
            });

            return redirect()
                ->route('sales-orders.show', $salesOrder)
                ->with('success', 'Sales order created successfully.');
        });
    }

    /**
     * Display a sales order.
     */
    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load([
            'customer',
            'warehouse',
            'creator',
            'confirmer',
            'items.product',
            'statusHistories.changedBy',
        ]);

        return view('sales-orders.show', compact('salesOrder'));
    }

    /**
     * Show the edit form for a draft order.
     */
    public function edit(SalesOrder $salesOrder)
    {
        $this->ensureEditable($salesOrder);

        $salesOrder->load('items.product');

        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('sales-orders.edit', compact(
            'salesOrder',
            'customers',
            'warehouses',
            'products'
        ));
    }

    /**
     * Update a draft sales order.
     */
    public function update(
        Request $request,
        SalesOrder $salesOrder,
        NotificationService $notifications
    )
    {
        $data = $this->validateOrder($request);

        $this->ensureActiveCustomerAndWarehouse(
            (int) $data['customer_id'],
            (int) $data['warehouse_id']
        );

        return DB::transaction(function () use (
            $data,
            $salesOrder,
            $notifications,
            $request
        ) {
            $salesOrder = SalesOrder::whereKey($salesOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureEditable($salesOrder);

            $products = $this->getActiveProducts($data['items']);

            $items = $this->prepareItems($data['items'], $products);

            $totals = $this->calculateTotals(
                $items,
                $data['discount_amount'] ?? 0,
                $data['tax_amount'] ?? 0
            );

            $salesOrder->update([
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount'],
                'tax_amount' => $totals['tax'],
                'total_amount' => $totals['total'],
                'currency' => strtoupper($data['currency']),
                'notes' => $data['notes'] ?? null,
            ]);

            $salesOrder->items()->delete();

            foreach ($items as $item) {
                $salesOrder->items()->create($item);
            }

            $salesOrderId = $salesOrder->id;
            $actorId = $request->user()->id;

            DB::afterCommit(function () use (
                $notifications,
                $salesOrderId,
                $actorId
            ) {
                $order = SalesOrder::find($salesOrderId);

                if (!$order) {
                    return;
                }

                $notifications->notifyPermissions(
                    permissions: [
                        'sales-orders.view',
                        'sales-orders.manage',
                    ],
                    category: 'sales',
                    title: 'Sales Order Updated',
                    message: "Sales Order {$order->order_number} has been updated.",
                    url: route('sales-orders.show', $order),
                    metadata: [
                        'sales_order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'action' => 'updated',
                    ],
                    exceptUserId: $actorId
                );
            });

            return redirect()
                ->route('sales-orders.show', $salesOrder)
                ->with('success', 'Sales order updated successfully.');
        });
    }

    /**
     * Confirm a draft sales order.
     */
    public function confirm(SalesOrder $salesOrder)
    {
        return DB::transaction(function () use ($salesOrder) {
            $salesOrder = SalesOrder::whereKey($salesOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$salesOrder->isDraft()) {
                throw ValidationException::withMessages([
                    'sales_order' => 'Only draft orders can be confirmed.',
                ]);
            }

            if (!$salesOrder->items()->exists()) {
                throw ValidationException::withMessages([
                    'items' => 'Add at least one product before confirmation.',
                ]);
            }

            $previousStatus = $salesOrder->status;

            $salesOrder->update([
                'status' => SalesOrder::STATUS_CONFIRMED,
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);

            $this->recordStatus(
                $salesOrder,
                $previousStatus,
                SalesOrder::STATUS_CONFIRMED,
                'Sales order confirmed.',
                auth()->id()
            );

            return redirect()
                ->route('sales-orders.show', $salesOrder)
                ->with('success', 'Sales order confirmed successfully.');
        });
    }

    /**
     * Start processing a confirmed sales order.
     */
    public function startProcessing(SalesOrder $salesOrder)
    {
        return DB::transaction(function () use ($salesOrder) {
            $salesOrder = SalesOrder::whereKey($salesOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($salesOrder->status !== SalesOrder::STATUS_CONFIRMED) {
                throw ValidationException::withMessages([
                    'sales_order' => 'Only confirmed orders can enter processing.',
                ]);
            }

            $previousStatus = $salesOrder->status;

            $salesOrder->update([
                'status' => SalesOrder::STATUS_PROCESSING,
                'processing_at' => now(),
            ]);

            $this->recordStatus(
                $salesOrder,
                $previousStatus,
                SalesOrder::STATUS_PROCESSING,
                'Sales order processing started.',
                auth()->id()
            );

            return redirect()
                ->route('sales-orders.show', $salesOrder)
                ->with('success', 'Sales order is now processing.');
        });
    }


    /**
     * Complete fulfillment, deduct inventory, and notify users.
     */
    public function complete(
        SalesOrder $salesOrder,
        InventoryService $inventoryService,
        NotificationService $notifications
    ) {
        return DB::transaction(function () use (
            $salesOrder,
            $inventoryService,
            $notifications
        ) {
            $salesOrder = SalesOrder::whereKey($salesOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($salesOrder->status !== SalesOrder::STATUS_PROCESSING) {
                throw ValidationException::withMessages([
                    'sales_order' => 'Only processing orders can be completed.',
                ]);
            }

            $salesOrder->load([
                'items.product',
                'warehouse',
            ]);

            if ($salesOrder->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'This sales order has no items.',
                ]);
            }

            foreach ($salesOrder->items as $item) {
                if (!$item->product || !$item->product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => "Product {$item->product_name} is inactive or unavailable.",
                    ]);
                }

                $inventoryService->issue(
                    $salesOrder->warehouse,
                    $item->product,
                    (int) $item->quantity,
                    auth()->id(),
                    $salesOrder->order_number
                );
            }

            $previousStatus = $salesOrder->status;

            $salesOrder->update([
                'status' => SalesOrder::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            $this->recordStatus(
                $salesOrder,
                $previousStatus,
                SalesOrder::STATUS_COMPLETED,
                'Sales order fulfilled and inventory deducted.',
                auth()->id()
            );

            // Notify users only after the transaction commits.
            DB::afterCommit(function () use ($salesOrder, $notifications) {
                $order = SalesOrder::find($salesOrder->id);

                if (!$order) {
                    return;
                }

                $notifications->notifyPermissions(
                    permissions: [
                        'sales-orders.view',
                        'sales-orders.manage',
                    ],
                    category: 'sales',
                    title: 'Sales Order Completed',
                    message: "Sales Order {$order->order_number} has been completed and inventory deducted.",
                    url: route('sales-orders.show', $order),
                    metadata: [
                        'sales_order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'action' => 'completed',
                    ]
                );
            });

            return redirect()
                ->route('sales-orders.show', $salesOrder)
                ->with(
                    'success',
                    'Sales order completed and inventory deducted successfully.'
                );
        }, attempts: 3);
    }


    /**
     * Cancel an eligible sales order.
     */
    public function cancel(Request $request, SalesOrder $salesOrder)
    {
        $data = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        return DB::transaction(function () use (
            $salesOrder,
            $data
        ) {
            $salesOrder = SalesOrder::whereKey($salesOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($salesOrder->status, [
                SalesOrder::STATUS_DRAFT,
                SalesOrder::STATUS_CONFIRMED,
                SalesOrder::STATUS_PROCESSING,
            ], true)) {
                throw ValidationException::withMessages([
                    'sales_order' => 'This sales order cannot be cancelled.',
                ]);
            }

            $previousStatus = $salesOrder->status;

            $salesOrder->update([
                'status' => SalesOrder::STATUS_CANCELLED,
                'cancellation_reason' => $data['cancellation_reason'] ?? null,
                'cancelled_at' => now(),
            ]);

            $this->recordStatus(
                $salesOrder,
                $previousStatus,
                SalesOrder::STATUS_CANCELLED,
                $data['cancellation_reason'] ?? 'Sales order cancelled.',
                auth()->id()
            );

            return redirect()
                ->route('sales-orders.show', $salesOrder)
                ->with('success', 'Sales order cancelled successfully.');
        });
    }

    /**
     * Delete a draft sales order.
     */
    public function destroy(SalesOrder $salesOrder)
    {
        $this->ensureEditable($salesOrder);

        $salesOrder->delete();

        return redirect()
            ->route('sales-orders.index')
            ->with('success', 'Draft sales order deleted successfully.');
    }

    /**
     * Validate sales order input.
     */
    private function validateOrder(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],

            'order_date' => ['required', 'date'],
            'expected_delivery_date' => [
                'nullable',
                'date',
                'after_or_equal:order_date',
            ],

            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'notes' => ['nullable', 'string', 'max:10000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'gt:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /**
     * Get active products selected for the order.
     */
    private function getActiveProducts(array $items)
    {
        $productIds = collect($items)
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id);

        $products = Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($products->count() !== $productIds->count()) {
            throw ValidationException::withMessages([
                'items' => 'One or more selected products are inactive or unavailable.',
            ]);
        }

        return $products;
    }

    /**
     * Prepare product snapshots and calculate line totals.
     */
    private function prepareItems(array $items, $products): array
    {
        $prepared = [];

        foreach ($items as $item) {
            $product = $products->get((int) $item['product_id']);

            $quantity = (int) $item['quantity'];
            $unitPrice = round((float) $item['unit_price'], 2);
            $discount = round((float) ($item['discount_amount'] ?? 0), 2);

            $grossTotal = round($quantity * $unitPrice, 2);

            if ($discount > $grossTotal) {
                throw ValidationException::withMessages([
                    'items' => "Discount cannot exceed the line total for {$product->name}.",
                ]);
            }

            $prepared[] = [
                'product_id' => $product->id,
                'product_sku' => $product->sku,
                'product_name' => $product->name,
                'unit' => $product->unit,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'line_total' => round($grossTotal - $discount, 2),
                'notes' => $item['notes'] ?? null,
            ];
        }

        return $prepared;
    }

    /**
     * Calculate order totals.
     */
    private function calculateTotals(
        array $items,
        float|string $discountAmount,
        float|string $taxAmount
    ): array {
        $subtotal = round(
            collect($items)->sum('line_total'),
            2
        );

        $discount = round((float) $discountAmount, 2);
        $tax = round((float) $taxAmount, 2);

        if ($discount > $subtotal) {
            throw ValidationException::withMessages([
                'discount_amount' => 'Order discount cannot exceed the subtotal.',
            ]);
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => round($subtotal - $discount + $tax, 2),
        ];
    }

    /**
     * Ensure the selected customer and warehouse are active.
     */
    private function ensureActiveCustomerAndWarehouse(
        int $customerId,
        int $warehouseId
    ): void {
        if (!Customer::whereKey($customerId)
            ->where('is_active', true)
            ->exists()) {
            throw ValidationException::withMessages([
                'customer_id' => 'The selected customer is inactive or unavailable.',
            ]);
        }

        if (!Warehouse::whereKey($warehouseId)
            ->where('is_active', true)
            ->exists()) {
            throw ValidationException::withMessages([
                'warehouse_id' => 'The selected warehouse is inactive or unavailable.',
            ]);
        }
    }

    /**
     * Only draft orders can be edited or deleted.
     */
    private function ensureEditable(SalesOrder $salesOrder): void
    {
        if (!$salesOrder->isDraft()) {
            throw ValidationException::withMessages([
                'sales_order' => 'Only draft sales orders can be edited or deleted.',
            ]);
        }
    }

    /**
     * Record order status changes.
     */
    private function recordStatus(
        SalesOrder $salesOrder,
        ?string $previousStatus,
        string $newStatus,
        ?string $comments,
        ?int $userId
    ): void {
        SalesOrderStatusHistory::create([
            'sales_order_id' => $salesOrder->id,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'comments' => $comments,
            'changed_by' => $userId,
        ]);
    }

    /**
     * Generate a unique sales order number.
     */
    private function generateOrderNumber(): string
    {
        do {
            $number = 'SO-' . now()->format('Ymd') . '-'
                . strtoupper(Str::random(6));
        } while (SalesOrder::where('order_number', $number)->exists());

        return $number;
    }
}
