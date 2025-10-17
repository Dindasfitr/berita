<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class history extends Model
{
    protected $table = 'history';
    protected $primaryKey = 'id_history';
    protected $fillable = ['id_pembaca', 'id_berita'];
}
