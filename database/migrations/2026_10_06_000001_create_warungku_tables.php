<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('purchase_price', 14, 2);
            $table->decimal('selling_price', 14, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('total', 14, 2);
            $table->timestamp('sold_at');
            $table->timestamps();

            $table->index('sold_at');
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->decimal('unit_price', 14, 2);
            // Harga beli disalin saat transaksi terjadi. Tanpa ini, laporan laba periode
            // lalu ikut berubah setiap kali harga beli barang diperbarui.
            $table->decimal('unit_cost', 14, 2);
            $table->decimal('subtotal', 14, 2);
        });

        Schema::create('cash_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 3);
            $table->decimal('amount', 14, 2);
            $table->string('note');
            $table->foreignId('sale_id')->nullable()->constrained()->cascadeOnDelete();
            // Belanja stok ditandai supaya tidak dihitung dua kali di laporan: uangnya
            // sudah masuk hitungan laba lewat modal barang terjual (unit_cost).
            $table->boolean('is_restock')->default(false);
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_entries');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
