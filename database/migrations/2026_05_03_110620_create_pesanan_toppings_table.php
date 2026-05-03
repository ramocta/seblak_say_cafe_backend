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
        Schema::create('pesanan_toppings', function (Blueprint $table) {
        $table->id('id_pesanan_topping');
        // Relasi ke id_detail_mn dari tabel pesanan_menu
        $table->foreignId('id_pesanan_menu')->constrained('pesanan_menus', 'id_pesanan_menu');
        $table->foreignId('id_topping')->constrained('toppings', 'id_topping');
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
        Schema::dropIfExists('pesanan_toppings');
    }
};
