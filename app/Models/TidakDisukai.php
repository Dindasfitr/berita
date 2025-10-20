<?php

namespace App\Models;

use App\Models\User;
use App\Models\Berita;
use Illuminate\Database\Eloquent\Model;

class TidakDisukai extends Model
{
    protected $table = 'tidak_disukai';
    protected $primaryKey = 'id_tidaksuka';
    protected $fillable = ['id_user', 'id_berita', 'tidak_suka'];

    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function berita() {
        return $this->belongsTo(Berita::class, 'id_berita');
    }

}
