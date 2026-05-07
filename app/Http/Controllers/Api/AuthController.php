<?php
// app/Http/Controllers/Api/AuthController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
public function register(Request $request)
{
    // 1. Sesuaikan validasi dengan kolom baru
    $validator = Validator::make($request->all(), [
        'nama_user' => 'required|string|max:255',
        'username'  => 'required|string|max:255|unique:users,username',
        'password'  => 'required|string|min:8',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors()
        ], 422);
    }

    // 2. Simpan data sesuai dengan $fillable di model User
    $user = User::create([
        'nama_user' => $request->nama_user,
        'username'  => $request->username,
        'password'  => Hash::make($request->password),
    ]);

    // 3. Generate token menggunakan Sanctum
    $token = $user->createToken('remember_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Registrasi berhasil',
        'data' => [
            'id_user'   => $user->id_user,
            'nama_user' => $user->nama_user,
            'username'  => $user->username,
        ],
        'access_token' => $token,
        'token_type'   => 'Bearer',
    ], 201);
}


public function login(Request $request)
{
    // PERBAIKAN 1: Gunakan validasi yang benar. 
    // 'username' bukan rule bawaan seperti 'email'. Cukup 'string'.
    $validator = Validator::make($request->all(), [
        'username' => 'required|string',
        'password' => 'required|string|min:6',
    ]);

    // PERBAIKAN 2: Jika validasi gagal, kembalikan JSON, bukan redirect.
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors()
        ], 422);
    }

    $user = User::where('username', $request->username)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Username atau password salah.'
        ], 401);
    }

    // PERBAIKAN 3: Pastikan model User menggunakan trait HasApiTokens
    $token = $user->createToken('remember_token')->plainTextToken;

    return response()->json([
        'success' => true, // Tambahkan status success agar konsisten
        'message' => 'Login berhasil',
        'user' => [
            'id_user' => $user->id_user,
            'nama_user' => $user->nama_user,
            'username' => $user->username,
        ],
        'token' => $token,
        'token_type' => 'Bearer'
    ]);
}

    public function logout(Request $request)
    {
        // Menghapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout'
        ]);
    }
}