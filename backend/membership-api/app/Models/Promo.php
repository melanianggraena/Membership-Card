<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Promo extends Model
{
    protected $fillable = ['title', 'image_path', 'description', 'terms', 'start_date', 'end_date', 'status'];
    protected $hidden = ['image_path'];
    protected $appends = ['image_url'];
    protected function casts(): array { return ['start_date' => 'date', 'end_date' => 'date']; }
    public function getImageUrlAttribute(): ?string { return $this->image_path ? url(Storage::disk('public')->url($this->image_path)) : null; }
    public function scopeActive($query) { return $query->where('status', 'active')->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today()); }
}
