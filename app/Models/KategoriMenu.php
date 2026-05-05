<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriMenu extends Model
{
    protected $table = 'kategori_menus';
    protected $primaryKey = 'id_kategori_menu';
    protected $fillable = ['nama'];

    public function menus()
    {
        return $this->hasMany(Menu::class, 'id_kategori_menu');
    }
}