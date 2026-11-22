<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderApprovalController extends Controller
{
    /**
     * Approve a pending purchase order.
     */
    public function approve(
        PurchaseOrder $purchaseOrder,
        NotificationService $notifications
    ) {
        DB::transaction(function () use (
            $purchaseOrder,
            $notifications
        ) {
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

            $purchaseOrderId = $purchaseOrder->id;
            $actorId = auth()->id();

            DB::afterCommit(function () use (
                $purchaseOrderId,
                $actorId,
                $notifications
            ) {
                $po = PurchaseOrder::with('creator')
                    ->find($purchaseOrderId);

                if (
                    !$po ||
                    !$po->creator ||
                    $po->creator->id === $actorId
                ) {
                    return;
                }

                $notifications->notifyUser(
                    user: $po->creator,
                    category: 'purchase',
                    title: 'Purchase Order Approved',
                    message: "Purchase Order {$po->po_number} has been approved.",
                    url: route('purchase-orders.show', $po),
                    metadata: [
                        'purchase_order_id' => $po->id,
                        'po_number' => $po->po_number,
                        'status' => 'approved',
                        'action' => 'approved',
                    ]
                );
            });
        });

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order approved successfully.');
    }

    /**
     * Reject a pending purchase order.
     */
    public function reject(
        Request $request,
        PurchaseOrder $purchaseOrder,
        NotificationService $notifications
    ) {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use (
            $purchaseOrder,
            $data,
            $notifications
        ) {
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

            $purchaseOrderId = $purchaseOrder->id;
            $actorId = auth()->id();
            $reason = $data['rejection_reason'];

            DB::afterCommit(function () use (
                $purchaseOrderId,
                $actorId,
                $reason,
                $notifications
            ) {
                $po = PurchaseOrder::with('creator')
                    ->find($purchaseOrderId);

                if (
                    !$po ||
                    !$po->creator ||
                    $po->creator->id === $actorId
                ) {
                    return;
                }

                $notifications->notifyUser(
                    user: $po->creator,
                    category: 'purchase',
                    title: 'Purchase Order Rejected',
                    message: "Purchase Order {$po->po_number} was rejected. Reason: {$reason}",
                    url: route('purchase-orders.show', $po),
                    metadata: [
                        'purchase_order_id' => $po->id,
                        'po_number' => $po->po_number,
                        'status' => 'rejected',
                        'action' => 'rejected',
                        'rejection_reason' => $reason,
                    ]
                );
            });
        });

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order rejected.');
    }
}
