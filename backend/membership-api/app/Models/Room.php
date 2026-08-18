<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['room_name', 'description', 'access_price', 'capacity', 'status'];
    protected function casts(): array { return ['access_price' => 'decimal:2']; }
    public function transactions() { return $this->hasMany(Transaction::class); }
    public function accessHistories() { return $this->hasMany(AccessHistory::class); }
}
