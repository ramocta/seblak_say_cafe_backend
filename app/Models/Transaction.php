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
        'payment_status',
        'status_pesanan',
        'qr_code_url', // Simpan URL QRIS
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
}