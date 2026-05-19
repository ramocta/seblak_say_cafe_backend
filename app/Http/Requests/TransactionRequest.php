<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_pemesan'   => 'required|string|max:100',
            'no_meja'        => 'nullable|string',
            'opsi_pemesanan' => 'required|in:dine in,take away',

            // ✅ Fix Bug 2: batasi nilai payment_method
            'payment_method' => 'required|in:tunai,qris',

            // ✅ Fix Bug 1: proof_payment wajib jika payment_method = qris
            'proof_payment'  => 'required_if:payment_method,qris|nullable|file|mimes:jpg,jpeg,png,webp|max:2048',

            'items'                          => 'required|array|min:1',
            'items.*.id_menu'                => 'required|exists:menus,id_menu',
            'items.*.qty'                    => 'required|integer|min:1',
            'items.*.toppings'               => 'nullable|array',
            'items.*.toppings.*.id_topping'  => 'required_with:items.*.toppings|exists:toppings,id_topping',
            'items.*.toppings.*.qty'         => 'required_with:items.*.toppings|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_pemesan.required'         => 'Nama pemesan wajib diisi.',
            'opsi_pemesanan.required'       => 'Opsi pemesanan wajib diisi.',
            'opsi_pemesanan.in'             => 'Opsi pemesanan harus dine in atau take away.',
            'payment_method.required'       => 'Metode pembayaran wajib diisi.',
            'payment_method.in'             => 'Metode pembayaran harus tunai atau qris.',

            // ✅ Pesan error spesifik untuk proof_payment
            'proof_payment.required_if'     => 'Bukti pembayaran QRIS wajib diupload.',
            'proof_payment.file'            => 'Bukti pembayaran harus berupa file.',
            'proof_payment.mimes'           => 'Bukti pembayaran harus berformat JPG, PNG, atau WEBP.',
            'proof_payment.max'             => 'Ukuran bukti pembayaran maksimal 2MB.',

            'items.required'                => 'Item pesanan tidak boleh kosong.',
            'items.*.id_menu.required'      => 'ID menu wajib diisi.',
            'items.*.id_menu.exists'        => 'Menu tidak ditemukan.',
            'items.*.qty.required'          => 'Jumlah item wajib diisi.',
            'items.*.qty.min'               => 'Jumlah item minimal 1.',
            'items.*.toppings.*.id_topping.exists' => 'Topping tidak ditemukan.',
            'items.*.toppings.*.qty.min'    => 'Jumlah topping minimal 1.',
        ];
    }

    // ✅ Fix Bug 3: override failedValidation agar selalu return JSON
    // bukan redirect ke halaman HTML saat validasi gagal
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}