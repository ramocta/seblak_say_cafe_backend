<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananTopping extends Model
{
    protected $table = 'pesanan_toppings';
    protected $primaryKey = 'id_pesanan_topping';
    protected $fillable = [
        'id_pesanan_menu',
        'id_topping',
        'qty',
        'harga_satuan',
        'subtotal'
    ];

    public function topping()
    {
        return $this->belongsTo(Topping::class, 'id_topping');
    }
}
