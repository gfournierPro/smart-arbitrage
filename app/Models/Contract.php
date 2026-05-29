<?php

namespace App\Models;

use App\Domain\Enums\PricingScheme;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Contract extends Model
{
     protected $fillable = ['household_id', 'provider', 'pricing_scheme', 'currency'];

     protected function casts(): array 
    {
        return ['pricing_scheme' => PricingScheme::class];
    }

    public function houselod(): BelongsTo 
    {
        return $this->belongsTo(Household::class);
    }

    public function tariffRate(): HasMany
    {
        return $this->hasMany(TariffRate::class);
    }

}
