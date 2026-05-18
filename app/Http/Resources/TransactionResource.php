<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_transaksi'   => $this->id_transaksi,
            'nama_pemesan'   => $this->nama_pemesan,
            'no_meja'        => $this->no_meja,
            'opsi_pemesanan' => $this->opsi_pemesanan,
            'payment_method' => $this->payment_method,
            'status_pesanan' => $this->status_pesanan,
            'harga_total'    => $this->harga_total,
            
            // Tambahkan proof_payment agar bisa diakses oleh Flutter/Frontend
            'proof_payment'    => $this->proof_payment ? asset('storage/' . $this->proof_payment) : null,
            
            'tanggal_transaksi' => $this->created_at->format('Y-m-d H:i:s'),

            // Memuat detail menu jika relasi sudah di-load (eager loading)
            'items' => PesananMenuResource::collection($this->whenLoaded('pesananMenus')),
        ];
    }
}