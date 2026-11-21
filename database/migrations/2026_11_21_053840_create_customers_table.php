
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('customer_code', 30)->unique();
            $table->string('customer_type', 30)->default('individual')->index();

            $table->string('name', 180);
            $table->string('company_name', 180)->nullable();
            $table->string('contact_person', 120)->nullable();

            $table->string('email', 180)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('tax_number', 100)->nullable();

            $table->text('address')->nullable();

            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->string('payment_terms', 20)->default('cash');

            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();

            $table->index(['customer_type', 'is_active']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
