<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderReceivingController extends Controller
{
    /**
     * Display the receiving form.
     */
    public function create(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['approved', 'partially_received'], true)) {
            abort(403, 'Only approved purchase orders can be received.');
        }

        $purchaseOrder->load([
            'supplier',
            'warehouse',
            'items.product',
        ]);

        return view('purchase-orders.receive', compact('purchaseOrder'));
    }

    /**
     * Receive products against an approved purchase order.
     */
    public function store(
        Request $request,
        PurchaseOrder $purchaseOrder,
        InventoryService $inventoryService
    ) {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use (
            $data,
            $purchaseOrder,
            $inventoryService
        ) {
            $purchaseOrder = PurchaseOrder::whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($purchaseOrder->status, ['approved', 'partially_received'], true)) {
                throw ValidationException::withMessages([
                    'purchase_order' => 'This purchase order is not available for receiving.',
                ]);
            }

            $items = $purchaseOrder->items()
                ->with('product')
                ->lockForUpdate()
                ->get();

            $receivedSummary = [];
            $totalReceived = 0;

            foreach ($data['items'] as $itemId => $quantity) {
                $quantity = (int) ($quantity ?? 0);

                if ($quantity === 0) {
                    continue;
                }

                $item = $items->firstWhere('id', (int) $itemId);

                if (!$item) {
                    throw ValidationException::withMessages([
                        'items' => 'An invalid purchase order item was submitted.',
                    ]);
                }

                $outstanding = (int) $item->ordered_quantity
                    - (int) $item->received_quantity;

                if ($quantity > $outstanding) {
                    throw ValidationException::withMessages([
                        "items.{$itemId}" =>
                            "Cannot receive {$quantity}. Only {$outstanding} units remain outstanding.",
                    ]);
                }

                $inventoryService->receive(
                    warehouse: $purchaseOrder->warehouse,
                    product: $item->product,
                    quantity: $quantity,
                    actorId: auth()->id(),
                    reference: $purchaseOrder->po_number
                );

                $item->received_quantity =
                    (int) $item->received_quantity + $quantity;

                $item->save();

                $receivedSummary[] = [
                    'product' => $item->product->name,
                    'quantity' => $quantity,
                ];

                $totalReceived += $quantity;
            }

            if ($totalReceived === 0) {
                throw ValidationException::withMessages([
                    'items' => 'Enter at least one quantity greater than zero.',
                ]);
            }

            $allReceived = $items->every(
                fn ($item) =>
                    (int) $item->received_quantity >= (int) $item->ordered_quantity
            );

            $purchaseOrder->update([
                'status' => $allReceived ? 'received' : 'partially_received',
            ]);

            $purchaseOrder->approvals()->create([
                'user_id' => auth()->id(),
                'action' => 'received',
                'comments' => json_encode([
                    'items' => $receivedSummary,
                    'total_quantity' => $totalReceived,
                    'notes' => $data['notes'] ?? null,
                ]),
            ]);
        }, attempts: 3);

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order items received successfully.');
    }
}
