<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $service)
    {
        $this->transactionService = $service;
    }

    /**
     * Membuat transaksi baru (User Checkout)
     * Menerima kiriman JSON (Tunai) atau Multipart FormData (QRIS + File Gambar)
     */
    public function store(TransactionRequest $request): JsonResponse
    {
        try {
            $dto = $request->validated();

            // ✅ Pass file object jika ada
            if ($request->hasFile('proof_payment')) {
                $dto['proof_payment'] = $request->file('proof_payment');
            }

            $transaction = $this->transactionService->createTransaction($dto);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat!',
                'data'    => new TransactionResource($transaction),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            // ✅ Tangkap error validasi manual dari service sebagai 422
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil Detail Transaksi Lengkap (Hanya Menampilkan Response)
     */
    public function show($id): JsonResponse
    {
        try {
            // Controller cukup memanggil service, lalu melemparkan hasilnya ke response
            $formattedData = $this->transactionService->getTransactionDetailForAdmin($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail transaksi berhasil dimuat.',
                'data'    => $formattedData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail transaksi: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Admin melakukan konfirmasi/penyelesaian pesanan (Apply)
     */
    public function apply($id): JsonResponse
    {
        try {
            $transaction = $this->transactionService->applyOrder($id);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil disetujui dan diselesaikan!',
                'data' => new TransactionResource($transaction)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pesanan: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * ✅ BARU: Admin menolak pesanan masuk (Reject)
     * Otomatis memicu pembalikan/pengembalian (restock) stok menu & topping
     */
    public function reject($id): JsonResponse
    {
        try {
            $transaction = $this->transactionService->rejectOrder($id);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil ditolak dan stok telah dikembalikan.',
                'data' => new TransactionResource($transaction)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak pesanan: ' . $e->getMessage()
            ], 400);
        }
    }
}
