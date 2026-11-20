
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('product_categories')
                ->nullOnDelete();

            $table->string('barcode', 100)->nullable()->unique();
            $table->string('brand', 120)->nullable();
            $table->string('unit', 30)->default('pcs');
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->string('image_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropUnique(['barcode']);

            $table->dropColumn([
                'category_id',
                'barcode',
                'brand',
                'unit',
                'cost_price',
                'image_path',
            ]);
        });
    }
};
