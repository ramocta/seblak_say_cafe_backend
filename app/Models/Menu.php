<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PesananMenu;

class Menu extends Model
{
    protected $table = 'menus';
    protected $primaryKey = 'id_menu';
    protected $fillable =
    [
        'id_kategori_menu',
        'nama_menu',
        'harga',
        'stok',
        'gambar'
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriMenu::class, 'id_kategori_menu');
    }

    public function pesananMenus()
    {
        return $this->hasMany(PesananMenu::class, 'id_menu', 'id_menu');
    }
}
