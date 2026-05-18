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
            $table->foreignId('id_user')->nullable()->constrained('users', 'id_user');
            $table->string('nama_pemesan');
            $table->string('no_meja')->nullable();
            $table->enum('opsi_pemesanan', ['dine in', 'take away']);
            $table->enum('payment_method', ['tunai', 'qris']);
            
            // 1. Perubahan Enum Status Pesanan (Pending, Selesai, Reject)
            $table->enum('status_pesanan', ['pending', 'done', 'reject'])->default('pending');
            
            // 2. Mengubah qr_code_url menjadi proof_payment (nullable karena jika tunai tidak wajib upload)
            $table->string('proof_payment', 255)->nullable();
            
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