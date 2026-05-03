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
        Schema::create('menus', function (Blueprint $table) {
        $table->id('id_menu');
        $table->foreignId('id_kategori_menu')->constrained('kategori_menus', 'id_kategori_menu');
        $table->string('nama_menu');
        $table->decimal('harga', 12, 2);
        $table->integer('stok');
        $table->string('gambar')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
