<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    public $timestamps = false;
    protected $fillable = ['member_id', 'otp_code', 'expired_at', 'verified_at', 'created_at'];
    protected function casts(): array { return ['expired_at' => 'datetime', 'verified_at' => 'datetime', 'created_at' => 'datetime']; }
    public function member() { return $this->belongsTo(Member::class); }
}
