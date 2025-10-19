<?php

namespace App\Models;

use App\Models\User;
use App\Models\kategori;
use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $table = 'berita';
    protected $primaryKey = 'id_berita';
    protected $fillable = ['id_user', 'id_kategori', 'judul', 'isi', 'gambar', 'tgl_terbit'];

    public function penulis()
    {
        return $this->belongsTo(User::class, 'id_penulis');
    }


    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }


}