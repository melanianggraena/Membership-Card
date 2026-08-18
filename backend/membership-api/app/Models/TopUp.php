<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopUp extends Model
{
    protected $fillable = ['member_id', 'admin_id', 'amount', 'payment_method', 'notes'];
    protected function casts(): array { return ['amount' => 'decimal:2']; }
    public function member() { return $this->belongsTo(Member::class); }
    public function admin() { return $this->belongsTo(Admin::class); }
}
