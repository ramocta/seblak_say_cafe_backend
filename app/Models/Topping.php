<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PesananTopping;

class Topping extends Model
{
    protected $table = 'toppings';
    protected $primaryKey = 'id_topping';
    protected $fillable =
    [
        'id_kategori_topping',
        'nama_topping',
        'harga',
        'stok',
        'gambar'
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriTopping::class, 'id_kategori_topping');
    }

    public function pesananToppings()
    {
        return $this->hasMany(PesananTopping::class, 'id_topping', 'id_topping');
    }
}
