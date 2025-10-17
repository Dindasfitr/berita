<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pembaca extends Model
{
    protected $table = 'pembaca';
    protected $primaryKey = 'id_pembaca';
    protected $fillable = ['email', 'name'];
}
