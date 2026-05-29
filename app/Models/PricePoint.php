<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricePoint extends Model
{
    protected $fillable = ['country_code', 'starts_at', 'price_cents', 'currency'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime'];
    }

    public function scopeForWindow($query, string $country, $from, $to)
    {
        return $query->where('country_code', $country)
            ->whereBetween('starts_at', [$from, $to])
            ->orderBy('starts_at');
    }
}
