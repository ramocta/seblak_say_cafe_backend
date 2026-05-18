<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    
    protected $primaryKey = 'id_transaksi';
    
    public $incrementing = true;

    protected $fillable = [
        'id_user',
        'nama_pemesan',
        'no_meja',
        'opsi_pemesanan', 
        'payment_method',
        'status_pesanan',
        'proof_payment', // Simpan URL Bukti Pembayaran
        'harga_total'
    ];

    /**
     * Relasi ke detail menu (One to Many)
     */
    public function pesananMenus()
    {
        return $this->hasMany(PesananMenu::class, 'id_transaksi', 'id_transaksi');
    }

    /**
     * Relasi ke User (Many to One)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}