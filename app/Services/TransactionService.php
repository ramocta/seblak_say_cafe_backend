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
    /**
     * Membuat Transaksi Baru (Checkout)
     */
    public function createTransaction(array $data)
    {
        return DB::transaction(function () use ($data) {
            $method = $data['payment_method'];
            $totalHargaKalkulasi = 0;
            $proofPaymentPath = null;

            // agar error dikembalikan sebagai JSON bukan HTML
            if ($method === 'qris') {
                if (
                    !isset($data['proof_payment']) ||
                    !($data['proof_payment'] instanceof \Illuminate\Http\UploadedFile) ||
                    !$data['proof_payment']->isValid()
                ) {
                    throw new \InvalidArgumentException(
                        'Bukti pembayaran QRIS wajib diupload dan harus berupa file gambar yang valid.'
                    );
                }
            }

            // --- PROSES UPLOAD BUKTI PEMBAYARAN QRIS ---
            if ($method === 'qris' && isset($data['proof_payment'])) {
                $file = $data['proof_payment'];

                // ✅ Validasi tipe file — hanya izinkan gambar
                $allowedMimes = ['jpg', 'jpeg', 'png', 'webp'];
                $ext = strtolower($file->getClientOriginalExtension());

                if (!in_array($ext, $allowedMimes)) {
                    throw new \InvalidArgumentException(
                        'Format bukti pembayaran tidak valid. Gunakan JPG, PNG, atau WEBP.'
                    );
                }

                $filename = 'bukti-qris-' . time() . '-' . uniqid() . '.' . $ext;
                $file->storeAs('proof_payments', $filename, 'public');
                $proofPaymentPath = 'proof_payments/' . $filename;
            }


            // 1. Simpan Header Transaksi (Sesuai Struktur Migrasi Baru)
            $transaction = Transaction::create([
                'id_user'        => Auth::id(),
                'nama_pemesan'   => $data['nama_pemesan'],
                'no_meja'        => $data['no_meja'] ?? null,
                'opsi_pemesanan' => $data['opsi_pemesanan'],
                'payment_method' => $method,

                // Baik tunai maupun QRIS masuk dengan status awal 'pending'
                'status_pesanan' => 'pending',
                'proof_payment'  => $proofPaymentPath,
                'harga_total'    => 0, // Diupdate setelah perulangan item selesai
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
     * Membatalkan/Discard Pesanan oleh Pelanggan & Mengembalikan Seluruh Stok (Restock)
     */
    public function cancelOrder($id)
    {
        return DB::transaction(function () use ($id) {
            // Ambil data transaksi beserta detail menu dan toppingnya
            $transaction = Transaction::with(['pesananMenus.pesananToppings'])->findOrFail($id);

            // Validasi: Hanya pesanan yang masih 'pending' yang boleh di-discard
            if ($transaction->status_pesanan !== 'pending') {
                throw new \Exception("Gagal: Pesanan tidak dapat dibatalkan karena sedang diproses atau sudah selesai.");
            }

            // --- PROSES PEMBALIKAN STOK (RESTOCK) ---
            foreach ($transaction->pesananMenus as $pesananMenu) {
                $menu = Menu::find($pesananMenu->id_menu);
                if ($menu) {
                    $menu->increment('stok', $pesananMenu->qty);
                }

                foreach ($pesananMenu->pesananToppings as $pesananTopping) {
                    $topping = Topping::find($pesananTopping->id_topping);
                    if ($topping) {
                        $topping->increment('stok', $pesananTopping->qty);
                    }
                }
            }

            $transaction->update([
                'status_pesanan' => 'reject',
            ]);

            return $transaction;
        });
    }

    /**
     * Konfirmasi / Terima Pesanan oleh Admin (Kasir)
     */
    public function applyOrder($id)
    {
        return DB::transaction(function () use ($id) {
            $transaction = Transaction::findOrFail($id);

            if ($transaction->status_pesanan !== 'pending') {
                throw new \Exception("Gagal: Hanya pesanan berstatus 'pending' yang dapat disetujui.");
            }

            // Ketika admin klik setuju, status menjadi selesai
            // Dan kita catat ID user (admin) yang memprosesnya di kolom 'processed_by'
            $transaction->update([
                'status_pesanan' => 'done',
                'id_user'   => Auth::id() // ✅ Menyimpan ID User (Admin) yang mengeksekusi
            ]);

            return $transaction;
        });
    }

    /**
     * Menolak Pesanan oleh Admin & Mengembalikan Seluruh Stok (Restock)
     */
    public function rejectOrder($id)
    {
        return DB::transaction(function () use ($id) {
            $transaction = Transaction::with(['pesananMenus.pesananToppings'])->findOrFail($id);

            if ($transaction->status_pesanan !== 'pending') {
                throw new \Exception("Gagal: Hanya pesanan berstatus 'pending' yang dapat ditolak.");
            }

            // --- PROSES PEMBALIKAN STOK (RESTOCK) ---
            foreach ($transaction->pesananMenus as $pesananMenu) {
                $menu = Menu::find($pesananMenu->id_menu);
                if ($menu) {
                    $menu->increment('stok', $pesananMenu->qty);
                }

                foreach ($pesananMenu->pesananToppings as $pesananTopping) {
                    $topping = Topping::find($pesananTopping->id_topping);
                    if ($topping) {
                        $topping->increment('stok', $pesananTopping->qty);
                    }
                }
            }

            // Update status menjadi reject dan catat ID user (admin) yang menolak
            $transaction->update([
                'status_pesanan' => 'reject',
                'id_user'   => Auth::id() // ✅ Menyimpan ID User (Admin) yang mengeksekusi
            ]);

            return $transaction;
        });
    }

    /**
     * Mengambil Detail Transaksi Lengkap & Menyusun Format Datanya untuk Admin
     */
    public function getTransactionDetailForAdmin($id)
    {
        // 1. Ambil data transaksi beserta relasi detail menu dan toppingnya
        $transaction = Transaction::with([
            'pesananMenus.menu',
            'pesananMenus.pesananToppings.topping'
        ])->findOrFail($id);

        // 2. Buat URL penuh bukti pembayaran jika ada (QRIS)
        $proofPaymentUrl = $transaction->proof_payment
            ? url('storage/' . $transaction->proof_payment)
            : null;

        // 3. Susun data utuh di sini agar Controller tinggal terima beres
        return [
            'id_transaksi'      => $transaction->id_transaksi,
            'nama_pemesan'      => $transaction->nama_pemesan,
            'no_meja'           => $transaction->no_meja,
            'opsi_pemesanan'    => $transaction->opsi_pemesanan,
            'payment_method'    => $transaction->payment_method,
            'status_pesanan'    => $transaction->status_pesanan,
            'harga_total'       => $transaction->harga_total,
            'proof_payment_url' => $proofPaymentUrl, // Link gambar siap cek di Flutter
            'created_at'        => $transaction->created_at,
            'items'             => $transaction->pesananMenus // Array isi seblak + topping
        ];
    }

    /**
     * Mengambil Pendapatan Bulanan (Hanya dari pesanan yang sukses/selesai)
     */
    public function getMonthlyRevenue()
    {
        return Transaction::where('status_pesanan', 'done')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('harga_total');
    }

    /**
     * Mengambil Riwayat Transaksi
     */
    public function getTransactionHistory($status = null)
    {
        $query = Transaction::with(['pesananMenus.menu', 'pesananMenus.pesananToppings.topping'])
            ->orderBy('created_at', 'desc');

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
