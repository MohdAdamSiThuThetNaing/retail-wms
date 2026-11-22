
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sales_order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('previous_status', 32)->nullable();
            $table->string('new_status', 32);

            $table->text('comments')->nullable();

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['sales_order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_status_histories');
    }
};
