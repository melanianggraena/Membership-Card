<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['member_code', 'full_name', 'phone', 'email', 'nfc_uid', 'balance', 'status', 'last_used', 'expired_at'];

    protected function casts(): array
    {
        return ['balance' => 'decimal:2', 'last_used' => 'datetime', 'expired_at' => 'datetime'];
    }

    public function topUps() { return $this->hasMany(TopUp::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }
    public function accessHistories() { return $this->hasMany(AccessHistory::class); }
}
