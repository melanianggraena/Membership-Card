<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['transaction_code', 'member_id', 'admin_id', 'room_id', 'outlet_id', 'promo_id', 'transaction_type', 'reference_id', 'original_amount', 'discount_amount', 'amount', 'balance_before', 'balance_after', 'status'];
    protected function casts(): array { return ['original_amount' => 'decimal:2', 'discount_amount' => 'decimal:2', 'amount' => 'decimal:2', 'balance_before' => 'decimal:2', 'balance_after' => 'decimal:2']; }
    public function member() { return $this->belongsTo(Member::class); }
    public function admin() { return $this->belongsTo(Admin::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function outlet() { return $this->belongsTo(Outlet::class); }
    public function promo() { return $this->belongsTo(Promo::class); }
}
