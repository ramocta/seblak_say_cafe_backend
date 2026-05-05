<?php

// app/Http/Controllers/Api/AdminDashboardController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $service)
    {
        $this->transactionService = $service;
    }

    /**
     * Statistik untuk ringkasan dashboard
     */
    public function stats()
    {
        try {
            $revenue = $this->transactionService->getMonthlyRevenue();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'monthly_revenue' => $revenue,
                    'report_month' => now()->format('F Y')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Daftar Riwayat Transaksi (Bisa difilter)
     */
    public function history(Request $request)
    {
        $status = $request->query('status'); // misal: ?status=selesai
        $history = $this->transactionService->getTransactionHistory($status);

        return TransactionResource::collection($history);
    }

    /**
     * Data Struk untuk Printer Thermal
     */
    public function printReceipt($id)
    {
        try {
            $data = $this->transactionService->getReceiptDetail($id);
            
            // Kita kembalikan format JSON khusus yang siap dibaca oleh 
            // thermal printer plugin di Flutter
            return response()->json([
                'success' => true,
                'store_name' => 'Seblak Say Cafe',
                'order_info' => [
                    'invoice' => 'INV-' . $data->id_transaksi,
                    'customer' => $data->nama_pemesan,
                    'table' => $data->no_meja ?? 'Take Away',
                    'date' => $data->created_at->format('d/m/Y H:i'),
                ],
                'items' => $data->pesananMenus, // Detail menu & toppings
                'total' => $data->harga_total,
                'payment' => strtoupper($data->payment_method)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
    }
}