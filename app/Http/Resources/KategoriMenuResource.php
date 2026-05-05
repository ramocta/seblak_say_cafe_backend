<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KategoriMenuResource extends JsonResource
{
    public function toArray(Request $request): array
{
    return [
        'id' => $this->id_kategori_menu,
        'nama_kategori' => $this->nama,
        // Opsional: Menampilkan jumlah menu dalam kategori ini
        'total_menu' => $this->whenLoaded('menus', fn() => $this->menus->count()),
    ];
}
}
