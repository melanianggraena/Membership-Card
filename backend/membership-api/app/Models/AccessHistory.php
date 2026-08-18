<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessHistory extends Model
{
    protected $fillable = ['member_id', 'room_id', 'uid', 'access_status', 'reason', 'scanned_at'];
    protected function casts(): array { return ['scanned_at' => 'datetime']; }
    public function member() { return $this->belongsTo(Member::class); }
    public function room() { return $this->belongsTo(Room::class); }
}
