
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_approvals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action', 32);
            $table->text('comments')->nullable();

            $table->timestamps();

            $table->index(['purchase_order_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_approvals');
    }
};
