<?php

namespace App\Models;

use App\Models\User;
use App\Models\Berita;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'history';
    protected $primaryKey = 'id_history';
    protected $fillable = ['id_user', 'id_berita'];

    public function user() {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function berita() {
        return $this->belongsTo(Berita::class, 'id_berita', 'id_berita');
    }
}
