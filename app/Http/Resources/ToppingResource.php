<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ToppingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_topping,
            'nama' => $this->nama_topping,
            'harga' => (float) $this->harga,
            'stok' => $this->stok,
            'gambar_url' => $this->gambar ? asset('storage/' . $this->gambar) : null,
            'kategori' => [
                'id' => $this->id_kategori_topping,
                'nama' => $this->kategori->nama ?? 'Tanpa Kategori',
            ],
            'last_update' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}
