<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriTopping extends Model
{
    protected $table = 'kategori_toppings';
    protected $primaryKey = 'id_kategori_topping';
    protected $fillable = ['nama'];

    public function menus()
    {
        return $this->hasMany(Topping::class, 'id_kategori_topping');
    }
}
