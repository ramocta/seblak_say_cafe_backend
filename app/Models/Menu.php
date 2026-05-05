<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';
    protected $primaryKey = 'id_menu';
    protected $fillable =
     ['id_kategori_menu',
        'nama_menu',
        'harga',
        'stok',
        'gambar'];

    public function kategori()
    {
        return $this->belongsTo(KategoriMenu::class, 'id_kategori_menu');
    }
}
