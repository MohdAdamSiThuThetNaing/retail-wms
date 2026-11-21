<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderApprovalController extends Controller
{
    /**
     * Approve a pending purchase order.
     */
    public function approve(PurchaseOrder $purchaseOrder)
    {
        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder = PurchaseOrder::whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($purchaseOrder->status !== 'pending_approval') {
                throw ValidationException::withMessages([
                    'purchase_order' => 'Only pending purchase orders can be approved.',
                ]);
            }

            if (!$purchaseOrder->items()->exists()) {
                throw ValidationException::withMessages([
                    'purchase_order' => 'Cannot approve a purchase order without items.',
                ]);
            }

            $purchaseOrder->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => null,
                'rejected_at' => null,
            ]);

            $purchaseOrder->approvals()->create([
                'user_id' => auth()->id(),
                'action' => 'approved',
                'comments' => 'Purchase order approved.',
            ]);
        });

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order approved successfully.');
    }

    /**
     * Reject a pending purchase order.
     */
    public function reject(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($purchaseOrder, $data) {
            $purchaseOrder = PurchaseOrder::whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($purchaseOrder->status !== 'pending_approval') {
                throw ValidationException::withMessages([
                    'purchase_order' => 'Only pending purchase orders can be rejected.',
                ]);
            }

            $purchaseOrder->update([
                'status' => 'rejected',
                'rejection_reason' => $data['rejection_reason'],
                'rejected_at' => now(),
                'approved_by' => null,
                'approved_at' => null,
            ]);

            $purchaseOrder->approvals()->create([
                'user_id' => auth()->id(),
                'action' => 'rejected',
                'comments' => $data['rejection_reason'],
            ]);
        });

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order rejected.');
    }
}
