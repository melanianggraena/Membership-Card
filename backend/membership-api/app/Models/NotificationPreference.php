<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = ['admin_id', 'enabled', 'top_up', 'nfc_access', 'transaction', 'system'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'top_up' => 'boolean', 'nfc_access' => 'boolean', 'transaction' => 'boolean', 'system' => 'boolean'];
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
