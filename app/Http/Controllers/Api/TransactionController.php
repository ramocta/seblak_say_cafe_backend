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
     */
    public function store(TransactionRequest $request): JsonResponse
    {
        try {
            $transaction = $this->transactionService->createTransaction($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat!',
                'data' => new TransactionResource($transaction)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin melakukan konfirmasi/penyelesaian pesanan (Apply)
     */
    public function apply($id): JsonResponse
    {
        try {
            // Memanggil logika applyOrder yang baru di Service
            $transaction = $this->transactionService->applyOrder($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil diproses/diselesaikan!',
                'data' => new TransactionResource($transaction)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pesanan: ' . $e->getMessage()
            ], 400); // 400 karena biasanya kesalahan logika (misal: QRIS belum bayar)
        }
    }

    /**
     * Simulasi atau Webhook Pembayaran QRIS (Mark as Paid)
     */
    public function payQris($id): JsonResponse
    {
        try {
            $this->transactionService->markAsPaid($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran QRIS berhasil dikonfirmasi!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengonfirmasi pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

}