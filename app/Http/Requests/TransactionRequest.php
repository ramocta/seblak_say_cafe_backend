<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
{
    return [
        'id_user' => 'nullable|exists:users,id_user',
        'nama_pemesan' => 'required|string',    
        'no_meja' => 'nullable|string',
        'opsi_pemesanan' => 'required|in:dine in,take away',
        'payment_method' => 'required|string',
        'harga_total'    => 'nullable|numeric',
        'items' => 'required|array',
        'items.*.id_menu' => 'required|exists:menus,id_menu',
        'items.*.qty' => 'required|integer|min:1',
        'items.*.toppings' => 'array', // Tidak wajib 'required' agar menu lain bisa kosong
        'items.*.toppings.*.id_topping' => 'required_with:items.*.toppings|exists:toppings,id_topping',
        'items.*.toppings.*.qty' => 'required_with:items.*.toppings|integer|min:1',
    ];
}
}
