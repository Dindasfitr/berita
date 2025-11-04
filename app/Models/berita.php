<?php

namespace App\Models;

use App\Models\User;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $table = 'berita';
    protected $primaryKey = 'id_berita';
    protected $fillable = [
        'id_user',
        'id_kategori',
        'judul',
        'isi',
        'gambar',
        'tgl_terbit',
        'is_premium',
    ];

    public function penulis()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }


    public function isPremium()
    {
        return (bool) $this->is_premium;
    }

    public function isFree()
    {
        return !$this->is_premium;
    }
}
