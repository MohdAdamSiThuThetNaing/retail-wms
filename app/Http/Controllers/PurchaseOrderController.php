<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\NotificationService;

class PurchaseOrderController extends Controller
{
    /**
     * Display purchase orders.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'warehouse', 'creator']);

        if ($request->filled('q')) {
            $search = $request->q;

            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $purchaseOrders = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => PurchaseOrder::count(),
            'draft' => PurchaseOrder::where('status', 'draft')->count(),
            'pending_approval' => PurchaseOrder::where('status', 'pending_approval')->count(),
            'approved' => PurchaseOrder::where('status', 'approved')->count(),
            'partially_received' => PurchaseOrder::where('status', 'partially_received')->count(),
            'received' => PurchaseOrder::where('status', 'received')->count(),
        ];

        return view('purchase-orders.index', compact(
            'purchaseOrders',
            'stats'
        ));
    }

    /**
     * Show the purchase order creation form.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('company_name')
            ->get();

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('purchase-orders.create', compact(
            'suppliers',
            'warehouses',
            'products'
        ));
    }

    /**
     * Store a new draft purchase order.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.ordered_quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'gt:0'],
        ]);

        $this->ensureActiveSupplierAndWarehouse(
            (int) $data['supplier_id'],
            (int) $data['warehouse_id']
        );

        return DB::transaction(function () use ($data, $request) {
            $productIds = collect($data['items'])
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

            $subtotal = $this->calculateSubtotal($data['items']);

            $discount = round((float) ($data['discount_amount'] ?? 0), 2);
            $tax = round((float) ($data['tax_amount'] ?? 0), 2);

            if ($discount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Discount cannot exceed the subtotal.',
                ]);
            }

            $total = round($subtotal - $discount + $tax, 2);

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $this->generatePoNumber(),
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => 'draft',
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'currency' => strtoupper($data['currency']),
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($data['items'] as $item) {
                $quantity = (int) $item['ordered_quantity'];
                $unitCost = round((float) $item['unit_cost'], 2);

                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'ordered_quantity' => $quantity,
                    'received_quantity' => 0,
                    'unit_cost' => $unitCost,
                    'line_total' => round($quantity * $unitCost, 2),
                ]);
            }

            return redirect()
                ->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order created successfully.');
        });
    }

    /**
     * Display a purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'warehouse',
            'creator',
            'approver',
            'items.product',
            'approvals.user',
        ]);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the edit form for a draft or rejected PO.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        $this->ensureEditable($purchaseOrder);

        $purchaseOrder->load('items.product');

        $suppliers = Supplier::where('is_active', true)
            ->orderBy('company_name')
            ->get();

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('purchase-orders.edit', compact(
            'purchaseOrder',
            'suppliers',
            'warehouses',
            'products'
        ));
    }

    /**
     * Update a draft or rejected PO.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->ensureEditable($purchaseOrder);

        $data = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.ordered_quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'gt:0'],
        ]);

        $this->ensureActiveSupplierAndWarehouse(
            (int) $data['supplier_id'],
            (int) $data['warehouse_id']
        );

        return DB::transaction(function () use ($data, $purchaseOrder) {
            $purchaseOrder = PurchaseOrder::whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureEditable($purchaseOrder);

            $productIds = collect($data['items'])
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id);

            $products = Product::whereIn('id', $productIds)
                ->where('is_active', true)
                ->get();

            if ($products->count() !== $productIds->count()) {
                throw ValidationException::withMessages([
                    'items' => 'One or more selected products are inactive or unavailable.',
                ]);
            }

            $subtotal = $this->calculateSubtotal($data['items']);

            $discount = round((float) ($data['discount_amount'] ?? 0), 2);
            $tax = round((float) ($data['tax_amount'] ?? 0), 2);

            if ($discount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Discount cannot exceed the subtotal.',
                ]);
            }

            $purchaseOrder->update([
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => round($subtotal - $discount + $tax, 2),
                'currency' => strtoupper($data['currency']),
                'notes' => $data['notes'] ?? null,
                'rejection_reason' => null,
                'rejected_at' => null,
            ]);

            $purchaseOrder->items()->delete();

            foreach ($data['items'] as $item) {
                $quantity = (int) $item['ordered_quantity'];
                $unitCost = round((float) $item['unit_cost'], 2);

                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'ordered_quantity' => $quantity,
                    'received_quantity' => 0,
                    'unit_cost' => $unitCost,
                    'line_total' => round($quantity * $unitCost, 2),
                ]);
            }

            return redirect()
                ->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order updated successfully.');
        });
    }

    /**
     * Submit a draft or rejected PO for approval.
     */

    public function submit(
        PurchaseOrder $purchaseOrder,
        NotificationService $notifications
    ) {
        return DB::transaction(function () use (
            $purchaseOrder,
            $notifications
        ) {
            $purchaseOrder = PurchaseOrder::whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureEditable($purchaseOrder);

            if (!$purchaseOrder->items()->exists()) {
                throw ValidationException::withMessages([
                    'items' => 'Add at least one product before submitting.',
                ]);
            }

            $purchaseOrder->update([
                'status' => 'pending_approval',
                'submitted_at' => now(),
                'rejection_reason' => null,
                'rejected_at' => null,
            ]);

            $purchaseOrder->approvals()->create([
                'user_id' => auth()->id(),
                'action' => 'submitted',
                'comments' => 'Purchase order submitted for approval.',
            ]);

            $purchaseOrderId = $purchaseOrder->id;
            $actorId = auth()->id();

            DB::afterCommit(function () use (
                $notifications,
                $purchaseOrderId,
                $actorId
            ) {
                $po = PurchaseOrder::find($purchaseOrderId);

                if (!$po) {
                    return;
                }

                $notifications->notifyPermissions(
                    permissions: ['purchase-orders.approve'],
                    category: 'purchase',
                    title: 'Purchase Order Awaiting Approval',
                    message: "Purchase Order {$po->po_number} has been submitted for approval.",
                    url: route('purchase-orders.show', $po),
                    metadata: [
                        'purchase_order_id' => $po->id,
                        'po_number' => $po->po_number,
                        'status' => 'pending_approval',
                        'action' => 'submitted',
                    ],
                    exceptUserId: $actorId
                );
            });

            return redirect()
                ->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order submitted for approval.');
        });
    }


    /**
     * Delete a draft or rejected PO.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $this->ensureEditable($purchaseOrder);

        $purchaseOrder->delete();

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }

    private function ensureEditable(PurchaseOrder $purchaseOrder): void
    {
        if (!in_array($purchaseOrder->status, ['draft', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'purchase_order' => 'Only draft or rejected purchase orders can be edited.',
            ]);
        }
    }

    private function ensureActiveSupplierAndWarehouse(
        int $supplierId,
        int $warehouseId
    ): void {
        if (!Supplier::whereKey($supplierId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'supplier_id' => 'The selected supplier is inactive or unavailable.',
            ]);
        }

        if (!Warehouse::whereKey($warehouseId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'warehouse_id' => 'The selected warehouse is inactive or unavailable.',
            ]);
        }
    }

    private function calculateSubtotal(array $items): float
    {
        return round(
            collect($items)->sum(function ($item) {
                return (int) $item['ordered_quantity']
                    * round((float) $item['unit_cost'], 2);
            }),
            2
        );
    }

    private function generatePoNumber(): string
    {
        do {
            $number = 'PO-' . now()->format('Ymd') . '-'
                . strtoupper(\Illuminate\Support\Str::random(6));
        } while (PurchaseOrder::where('po_number', $number)->exists());

        return $number;
    }
}
