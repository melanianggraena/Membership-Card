<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Promo extends Model
{
    protected $fillable = ['title', 'image_path', 'description', 'terms', 'discount_type', 'discount_value', 'start_date', 'end_date', 'status'];
    protected $hidden = ['image_path'];
    protected $appends = ['image_url'];
    protected function casts(): array { return ['discount_value' => 'decimal:2', 'start_date' => 'date', 'end_date' => 'date']; }
    public function getImageUrlAttribute(): ?string { return $this->image_path ? url(Storage::disk('public')->url($this->image_path)) : null; }
    public static function deactivateExpired(): int
    {
        return static::where('status', 'active')
            ->whereDate('end_date', '<', today())
            ->update(['status' => 'inactive']);
    }

    public function scopeActive($query)
    {
        static::deactivateExpired();
        return $query->where('status', 'active')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today());
    }

    protected static function booted(): void
    {
        static::retrieved(function (Promo $promo) {
            if ($promo->status === 'active' && $promo->end_date && $promo->end_date->lt(today())) {
                $promo->status = 'inactive';
                $promo->saveQuietly();
            }
        });
    }

    public function transactions() { return $this->hasMany(Transaction::class); }

    public function discountFor(float $amount): float
    {
        $discount = $this->discount_type === 'percentage'
            ? $amount * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        return round(min($amount, max(0, $discount)), 2);
    }
}
