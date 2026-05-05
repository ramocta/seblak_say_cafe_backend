<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KategoriToppingResource extends JsonResource
{
    public function toArray(Request $request): array
{
    return [
        'id' => $this->id_kategori_topping,
        'nama_kategori' => $this->nama,
    ];
}
}
