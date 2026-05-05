<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PesananMenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_pesanan_menu,
            'id_transaksi' => $this->id_transaksi,
            'menu' => new MenuResource($this->whenLoaded('menu')),
            'qty' => (int) $this->qty,
            'harga_satuan' => (float) $this->harga_satuan,
            'subtotal' => (float) $this->subtotal,
            // Menyertakan topping yang dipilih untuk menu ini
            'toppings_detail' => PesananToppingResource::collection($this->whenLoaded('pesananToppings')),
        ];
    }
}
