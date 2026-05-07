<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $blueprint) {
            $blueprint->id('id_user'); 
            
            $blueprint->string('nama_user');
            
            $blueprint->string('username')->unique(); 
            
            $blueprint->string('password');
            
            $blueprint->rememberToken(); 
            
            $blueprint->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};