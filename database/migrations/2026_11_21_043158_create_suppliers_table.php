
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            $table->string('supplier_code', 30)->unique();
            $table->string('company_name', 180);
            $table->string('contact_person', 120)->nullable();

            $table->string('email', 180)->nullable();
            $table->string('phone', 50)->nullable();

            $table->string('tax_number', 100)->nullable();

            $table->text('address')->nullable();

            $table->enum('payment_terms', [
                'cash',
                'net_15',
                'net_30',
                'net_60',
            ])->default('cash');

            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['company_name', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
