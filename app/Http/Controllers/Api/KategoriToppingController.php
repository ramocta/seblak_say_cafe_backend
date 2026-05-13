<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KategoriToppingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    // Mengambil semua data dari tabel kategoris
    $kategori = \App\Models\KategoriTopping::all(); 

    return response()->json([
        'success' => true,
        'data'    => $kategori
    ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
