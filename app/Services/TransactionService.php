<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\PesananMenu;
use App\Models\PesananTopping;
use App\Models\Menu;
use App\Models\Topping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    public function createTransaction(array $data)
    {
        return DB::transaction(function () use ($data) {
            $method = $data['payment_method'];
            $totalHargaKalkulasi = 0;

            // 1. Simpan Header Transaksi
            $transaction = Transaction::create([
                'id_user'        => Auth::id(),
                'nama_pemesan'   => $data['nama_pemesan'],
                'no_meja'        => $data['no_meja'],
                'opsi_pemesanan' => $data['opsi_pemesanan'],
                'payment_method' => $method,
                'payment_status' => 'pending',
                // Jika tunai langsung masuk 'proses' (dapur), jika QRIS 'menunggu' pembayaran
                'status_pesanan' => ($method === 'tunai') ? 'proses' : 'pending',
                'qr_code_url'    => ($method === 'qris') 
                                    ? "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=SEBLAK-PAY-" . time() 
                                    : null,
                'harga_total'    => 0,
            ]);

            foreach ($data['items'] as $item) {
                // Load menu beserta kategorinya untuk validasi
                $menu = Menu::with('kategori')->findOrFail($item['id_menu']);

                // --- VALIDASI STOK MENU ---
                if ($menu->stok < $item['qty']) {
                    throw new \Exception("Stok menu {$menu->nama_menu} tidak mencukupi (Tersisa: {$menu->stok}).");
                }
                
                // --- VALIDASI KATEGORI (WAJIB TOPPING) ---
                // Asumsi: ID Kategori 1 adalah Seblak
                if ($menu->id_kategori_menu == 1) {
                    if (empty($item['toppings']) || count($item['toppings']) == 0) {
                        throw new \Exception("Menu {$menu->nama_menu} wajib memilih minimal 1 topping.");
                    }
                }

                $subtotalMenu = $menu->harga * $item['qty'];
                $totalHargaKalkulasi += $subtotalMenu;

                // 2. Simpan Detail Menu
                $pesananMenu = PesananMenu::create([
                    'id_transaksi' => $transaction->id_transaksi,
                    'id_menu'      => $menu->id_menu,
                    'qty'          => $item['qty'],
                    'harga_satuan' => $menu->harga,
                    'subtotal'     => $subtotalMenu,
                ]);

                // 3. Simpan Topping
                if (!empty($item['toppings'])) {
                    foreach ($item['toppings'] as $top) {
                        $topping = Topping::findOrFail($top['id_topping']);

                        // --- VALIDASI STOK TOPPING ---
                        if ($topping->stok < $top['qty']) {
                            throw new \Exception("Stok topping {$topping->nama_topping} tidak mencukupi.");
                        }

                        $subtotalTopping = $topping->harga * $top['qty'];
                        $totalHargaKalkulasi += $subtotalTopping;
                        
                        PesananTopping::create([
                            // Pastikan 'id_pesanan_menu' adalah FK yang benar di tabel pesanan_toppings
                            'id_pesanan_menu' => $pesananMenu->id_pesanan_menu, 
                            'id_topping'      => $topping->id_topping,
                            'qty'             => $top['qty'],
                            'harga_satuan'    => $topping->harga,
                            'subtotal'        => $subtotalTopping,
                        ]);
                        
                        // Kurangi stok topping
                        $topping->decrement('stok', $top['qty']);
                    }
                }
                
                // Kurangi stok menu utama
                $menu->decrement('stok', $item['qty']);
            }

            // 4. Update harga_total akhir setelah semua kalkulasi subtotal selesai
            $transaction->update([
                'harga_total' => $totalHargaKalkulasi
            ]);

            return $transaction;
        });
    }
    /**
     * Fungsi Apply/Konfirmasi Admin
     */
    public function applyOrder($id)
    {
        return DB::transaction(function () use ($id) {
            $transaction = Transaction::findOrFail($id);

            if ($transaction->payment_method === 'tunai') {
                // Alur Tunai: Klik apply langsung bayar & selesai
                $transaction->update([
                    'payment_status' => 'paid',
                    'status_pesanan' => 'selesai'
                ]);
            } else {
                // Alur QRIS: Cek apakah sudah dibayar (paid) sebelum diselesaikan
                if ($transaction->payment_status !== 'paid') {
                    throw new \Exception("Gagal: Pesanan QRIS belum dibayar oleh pelanggan.");
                }

                $transaction->update([
                    'status_pesanan' => 'selesai'
                ]);
            }

            return $transaction;
        });
    }

    /**
     * Simulasi Pembayaran QRIS (Dipanggil Webhook/User)
     */
    public function markAsPaid($id)
    {
        $transaction = Transaction::findOrFail($id);
        
        return $transaction->update([
            'payment_status' => 'paid',
            'status_pesanan' => 'proses' // Pindahkan dari 'menunggu' ke 'proses' (dapur)
        ]);
    }


        public function getMonthlyRevenue()
    {
        return Transaction::where('payment_status', 'paid')
            ->where('status_pesanan', 'selesai')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('harga_total');
    }

    public function getTransactionHistory($status = null)
{
    $query = Transaction::with(['pesananMenus.menu', 'pesananMenus.pesananToppings.topping'])
        ->orderBy('created_at', 'desc');

    // Jika ada filter status (misal: 'selesai', 'proses'), terapkan ke query
    if ($status) {
        $query->where('status_pesanan', $status);
    }

    return $query->get();
}

    /**
     * Mengambil data lengkap untuk struk
     */
    public function getReceiptDetail($id)
    {
        return Transaction::with(['pesananMenus.menu', 'pesananMenus.pesananToppings.topping'])
            ->findOrFail($id);
    }
}

