<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pesanan_menus', function (Blueprint $table) {
        $table->id('id_pesanan_menu');
        $table->foreignId('id_transaksi')->constrained('transactions', 'id_transaksi');
        $table->foreignId('id_menu')->constrained('menus', 'id_menu');
        $table->integer('qty');
        $table->decimal('harga_satuan', 12, 2);
        $table->decimal('subtotal', 12, 2);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan_menus');
    }
};
