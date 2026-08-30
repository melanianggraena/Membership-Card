<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    protected $fillable = ['outlet_code', 'outlet_name', 'description', 'status'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
