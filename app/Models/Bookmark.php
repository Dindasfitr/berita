<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    protected $table = 'bookmarks';
    protected $primaryKey = 'id_bookmark';
    protected $fillable = ['id_user', 'id_berita'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function berita()
    {
        return $this->belongsTo(Berita::class, 'id_berita', 'id_berita');
    }
}
