<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'id_report';
    protected $fillable = [
        'id_user',
        'id_berita',
        'reason',
        'description',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function berita()
    {
        return $this->belongsTo(Berita::class, 'id_berita', 'id_berita');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}
