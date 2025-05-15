<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = ['code', 'discount', 'min_value', 'valid_until'];
    
    protected $casts = [
        'valid_until' => 'date'
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
