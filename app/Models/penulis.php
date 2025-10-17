<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class penulis extends Model
{
    protected $table = 'penulis';
    protected $primaryKey = 'id_penulis';
    protected $fillable = ['email', 'name'];
}
