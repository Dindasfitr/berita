<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class disukai extends Model
{
    protected $table = 'disukai';
    protected $primaryKey = 'id_disukai';
    protected $fillable = ['id_pembaca', 'id_berita'];
}
