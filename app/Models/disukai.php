<?php

namespace App\Models;

use App\Models\User;
use App\Models\Berita;
use Illuminate\Database\Eloquent\Model;

class Disukai extends Model
{
    protected $table = 'disukai';
    protected $primaryKey = 'id_disukai';
    protected $fillable = ['id_user', 'id_berita', 'suka'];

    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function berita() {
        return $this->belongsTo(Berita::class, 'id_berita');
}

}
