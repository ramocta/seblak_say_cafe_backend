<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PesananToppingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_pesanan_topping,
            'id_pesanan_menu' => $this->id_pesanan_menu,
            'topping' => new ToppingResource($this->whenLoaded('topping')),
            'qty' => (int) $this->qty,
            'harga_satuan' => (float) $this->harga_satuan,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}
