<?php

namespace App\Domain\ValueObjects;

use App\Domain\Enums\ArbitrageAction;
use Carbon\CarbonImmutable;

final readonly class ArbitrageDecision
{
    public function __construct(
        public CarbonImmutable $slotStart,
        public int $assetId,
        public ArbitrageAction $action,
        public float $energyKwh,        // énergie déplacée sur ce créneau
        public int $effectivePriceCents, // prix effectif retenu
        public string $reason,           // explication lisible (debug + doc)
    ) {}

    /** Coût (positif) ou gain (négatif) estimé de cette décision, en centimes. */
    public function costCents(): int
    {
        return match ($this->action) {
            ArbitrageAction::Consume,
            ArbitrageAction::Store => (int) round($this->energyKwh * $this->effectivePriceCents),
            ArbitrageAction::Inject => -(int) round($this->energyKwh * $this->effectivePriceCents),
            ArbitrageAction::Idle => 0,
        };
    }
}