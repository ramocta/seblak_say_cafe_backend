<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananMenu extends Model
{
    protected $table = 'pesanan_menus';
    protected $primaryKey = 'id_pesanan_menu';
    protected $fillable = [
        'id_transaksi',
        'id_menu',
        'qty',
        'harga_satuan',
        'subtotal'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }

    public function pesananToppings()
    {
        return $this->hasMany(PesananTopping::class, 'id_pesanan_menu');
    }
}
