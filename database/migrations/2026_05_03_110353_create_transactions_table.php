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
        Schema::create('transactions', function (Blueprint $table) {
        $table->id('id_transaksi');
        $table->foreignId('id_user')->constrained('users', 'id_user');
        $table->string('nama_pemesan');
        $table->string('no_meja')->nullable();
        $table->enum('opsi_pemesanan',['dine in','take away']);
        $table->string('payment_method');
        $table->string('payment_status');
        $table->enum('status_pesanan',['pending','proses','seleai'])->default('pending');
        $table->decimal('harga_total', 12, 2);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
