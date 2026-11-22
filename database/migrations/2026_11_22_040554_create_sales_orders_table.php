
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_number', 50)->unique();

            $table->foreignId('customer_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->restrictOnDelete();

            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();

            $table->string('status', 32)->default('draft')->index();

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->string('currency', 3)->default('THB');

            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['warehouse_id', 'order_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
