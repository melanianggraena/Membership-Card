<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function topUps()
    {
        return $this->hasMany(TopUp::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function notificationPreference()
    {
        return $this->hasOne(NotificationPreference::class);
    }
}
