<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
    return [
        'id' => $this->id_menu,
        'nama' => $this->nama_menu,
        'harga' => (float) $this->harga, // Paksa ke float agar Flutter tidak error
        'stok' => $this->stok,
        'gambar_url' => $this->gambar ? asset('storage/' . $this->gambar) : null,
        'kategori' => [
            'id' => $this->id_kategori_menu,
            'nama' => $this->kategori->nama ?? 'Tanpa Kategori',
        ],
        'last_update' => $this->updated_at->format('Y-m-d H:i:s'),
    ];
    }
}
