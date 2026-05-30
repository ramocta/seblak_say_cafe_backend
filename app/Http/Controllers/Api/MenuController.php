<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Http\Resources\MenuResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    // 1. Ambil Semua Menu
    public function index(Request $request)
    {
        $query = Menu::with('kategori');

        if ($request->kategori) {
            $query->where('id_kategori_menu', $request->kategori);
        }

        $menus = $query->get();

        return response()->json([
            'success' => true,
            'data' => MenuResource::collection($menus)
        ]);
    }

    // 2. Tambah Menu Baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_kategori_menu' => 'required|exists:kategori_menus,id_kategori_menu',
            'nama_menu'      => 'required|string|max:255',
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
            $path = $request->file('gambar')->store('menu', 'public');
            $data['gambar'] = $path;
        }

        $menu = Menu::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil ditambahkan',
            // Memuat relasi kategori agar data yang dikembalikan ke Flutter lengkap
            'data'    => new MenuResource($menu->load('kategori'))
        ], 201);
    }

    // 3. Detail Menu Tertentu
    public function show(Menu $menu)
    {
        return response()->json([
            'success' => true,
            'data'    => new MenuResource($menu)
        ]);
    }

    // 4. Update Menu
    public function update(Request $request, Menu $menu)
    {
        $validator = Validator::make($request->all(), [
            'id_kategori_menu' => 'sometimes|exists:kategori_menus,id_kategori_menu',
            'nama_menu'      => 'sometimes|string|max:255',
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

        // Ambil semua data input kecuali gambar (karena gambar dihandle manual)
        $data = $request->except('gambar');

        if ($request->hasFile('gambar')) {
            // 1. Hapus gambar lama dari folder storage jika ada
            if ($menu->gambar) {
                Storage::disk('public')->delete($menu->gambar);
            }

            // 2. Simpan gambar baru
            $data['gambar'] = $request->file('gambar')->store('menu', 'public');
        }

        // 3. Update data yang sudah ada (BUKAN Menu::create)
        $menu->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil diperbarui!',
            'data'    => new MenuResource($menu)
        ], 200); // Gunakan status 200 OK untuk update
    }



    // 5. Hapus Menu
    public function destroy(Menu $menu)
    {
        DB::transaction(function () use ($menu) {
            if ($menu->gambar) {
                Storage::disk('public')->delete($menu->gambar);
            }

            foreach ($menu->pesananMenus as $pesananMenu) {
                $pesananMenu->pesananToppings()->delete();
            }

            $menu->pesananMenus()->delete();
            $menu->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil dihapus'
        ]);
    }
}
