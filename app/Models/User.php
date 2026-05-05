<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users'; // Sesuaikan jika nama tabel Anda 'user'
    protected $primaryKey = 'id_user';
    protected $fillable = 
    [
    'nama_user',
    'username',
    'password'
    ];
    protected $hidden = ['password','remember_token']; // Agar password tidak ikut terkirim ke API
}
