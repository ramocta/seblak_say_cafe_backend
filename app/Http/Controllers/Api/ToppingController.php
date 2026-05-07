<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Topping;
use App\Http\Resources\ToppingResource; // Pastikan resource ini sudah dibuat
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ToppingController extends Controller
{
    // 1. Ambil Semua Topping
    public function index(Request $request)
    {
        $query = Topping::with('kategori');

        if ($request->kategori){
            $query->where('id_kategori_topping',$request->kategori);
        }
        
        $toppings = $query->get();

        return response()->json([
            'success' => true,
            'data' => ToppingResource::collection($toppings)
        ]);
    }

    // 2. Tambah Topping Baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_kategori_topping' => 'required|exists:kategori_toppings,id_kategori_topping',
            'nama_topping'   => 'required|string|max:255',
            'harga'          => 'required|numeric',
            'stok'           => 'required|integer',
            'gambar'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            // Menyimpan gambar ke folder storage/app/public/topping
            $path = $request->file('gambar')->store('topping', 'public');
            $data['gambar'] = $path;
        }

        $topping = Topping::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Topping berhasil ditambahkan',
            'data'    => new ToppingResource($topping->load('kategori'))
        ], 201);
    }

    // 3. Detail Topping Tertentu
    public function show(Topping $topping)
    {
        return response()->json([
            'success' => true,
            'data'    => new ToppingResource($topping->load('kategori'))
        ]);
    }

    // 4. Update Topping
    public function update(Request $request, Topping $topping)
    {
        $validator = Validator::make($request->all(), [
            'id_kategori_topping' => 'sometimes|exists:kategori_toppings,id_kategori_topping',
            'nama_topping'   => 'sometimes|string|max:255',
            'harga'          => 'sometimes|numeric',
            'stok'           => 'sometimes|integer',
            'gambar'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->except('gambar');

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($topping->gambar) {
                Storage::disk('public')->delete($topping->gambar);
            }

            // Simpan gambar baru
            $data['gambar'] = $request->file('gambar')->store('topping', 'public');
        }

        $topping->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Topping berhasil diperbarui!',
            'data'    => new ToppingResource($topping)
        ], 200);
    }

    // 5. Hapus Topping
    public function destroy(Topping $topping)
    {
        if ($topping->gambar) {
            Storage::disk('public')->delete($topping->gambar);
        }

        $topping->delete();

        return response()->json([
            'success' => true,
            'message' => 'Topping berhasil dihapus'
        ]);
    }
}
