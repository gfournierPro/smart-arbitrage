<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TariffRate extends Model
{
    protected $fillable = [
        'contract_id', 'label', 'price_cents',
        'starts_at', 'ends_at', 'months', 'is_curtailment', 'priority',
    ];

    protected function casts(): array
    {
        return [
            'months' => 'array',          // JSON <-> array PHP
            'is_curtailment' => 'boolean',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
