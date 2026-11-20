
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add management fields to existing warehouses table
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('contact_person', 120)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('notes')->nullable();
        });

        // Create hierarchical warehouse locations
        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('warehouse_locations')
                ->nullOnDelete();

            $table->string('code', 64);
            $table->string('name', 150);
            $table->string('type', 30)->default('bin');
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();

            $table->unique(['warehouse_id', 'code']);
            $table->index(['warehouse_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_locations');

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn([
                'contact_person',
                'phone',
                'email',
                'notes',
            ]);
        });
    }
};
