<?php

namespace App\Models;

use App\Domain\Enums\AssetType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class Asset extends Model
{
    protected $fillable = ['household_id', 'type', 'label', 'capacity_kwh', 'max_power_kw', 'current_charge_kwh'];

    protected function casts(): array
    {
        return [
            'type' => AssetType::class, 
            'capacity_kwh' => 'decimal:3',
            'max_power_kw' => 'decimal:3',
            'current_charge_kwh' => 'decimal:3',
        ];
    }

    public function houselod(): BelongsTo {
        return $this->belongsTo(Household::class);
    }

}
