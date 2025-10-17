<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class berita extends Model
{
    protected $table = 'berita';
    protected $primaryKey = 'id_berita';
    protected $fillable = ['id_penulis', 'id_kategori', 'judul', 'isi', 'gambar', 'tgl_terbit'];

    public function penulis()
    {
        return $this->morphTo(__FUNCTION__, 'penulis_type', 'id_penulis');
    }


    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }


}