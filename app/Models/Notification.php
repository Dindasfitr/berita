<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id_notification';
    protected $fillable = [
        'id_user',
        'title',
        'message',
        'type',
        'is_read',
        'data'
    ];
    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }
}
