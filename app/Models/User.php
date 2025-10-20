<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'membership',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isPenulis()
    {
        return $this->role === 'penulis';
    }

    public function isPembaca()
    {
        return $this->role === 'pembaca';
    }


    public function isGuest()
    {
        return $this->membership === 'guest';
    }

    public function isFree()
    {
        return $this->membership === 'free';
    }

    public function isPremium()
    {
        return $this->membership === 'premium';
    }
}
