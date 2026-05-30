<?php

namespace App\Domain\Services;

use App\Domain\Enums\ArbitrageAction;
use App\Domain\ValueObjects\ArbitrageDecision;
use App\Models\Asset;
use App\Models\Household;
use App\Models\PricePoint;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ArbitrageEngine
{
    public function __construct(
        private readonly PricingService $pricing,
    ) {}

    /**
     * Calcule les décisions d'arbitrage pour un foyer sur une fenêtre de prix.
     *
     * @param  Collection<int, PricePoint>  $pricePoints  ordonnés par créneau
     * @return Collection<int, ArbitrageDecision>
     */
    public function plan(Household $household, Collection $pricePoints): Collection
    {
        $contract = $household->contract;
        $storage = $household->storageAssets();

        // Seuils dérivés des prix de la fenêtre : quartiles bas / haut.
        $prices = $pricePoints->pluck('price_cents')->sort()->values();
        $lowThreshold = $prices->get((int) floor($prices->count() * 0.25)) ?? 0;
        $highThreshold = $prices->get((int) floor($prices->count() * 0.75)) ?? 0;

        $decisions = collect();

        foreach ($pricePoints as $spot) {
            $slot = CarbonImmutable::parse($spot->starts_at);
            $price = $this->pricing->effectivePriceCents($contract, $slot, $spot);

            foreach ($storage as $asset) {
                $decisions->push(
                    $this->decideForAsset($asset, $slot, $price, $lowThreshold, $highThreshold)
                );
            }
        }

        return $decisions;
    }

    private function decideForAsset(
        Asset $asset,
        CarbonImmutable $slot,
        int $price,
        int $low,
        int $high,
    ): ArbitrageDecision {
        $headroom = $asset->capacity_kwh - $asset->current_charge_kwh; // place restante
        $stored = $asset->current_charge_kwh;                          // énergie dispo
        $step = (float) $asset->max_power_kw;                          // sur 1h

        // Prix bas et de la place => on charge.
        if ($price <= $low && $headroom > 0) {
            $energy = min($step, $headroom);
            $asset->current_charge_kwh += $energy; // on simule l'évolution de l'état
            return new ArbitrageDecision(
                $slot, $asset->id, ArbitrageAction::Store, $energy, $price,
                "Prix bas ({$price}c) : charge de {$energy} kWh."
            );
        }

        // Prix haut et de l'énergie stockée => on décharge / réinjecte.
        if ($price >= $high && $stored > 0) {
            $energy = min($step, $stored);
            $asset->current_charge_kwh -= $energy;
            return new ArbitrageDecision(
                $slot, $asset->id, ArbitrageAction::Inject, $energy, $price,
                "Prix haut ({$price}c) : décharge de {$energy} kWh."
            );
        }

        // Sinon, on ne fait rien.
        return new ArbitrageDecision(
            $slot, $asset->id, ArbitrageAction::Idle, 0.0, $price,
            "Prix moyen ({$price}c) : aucune action."
        );
    }
}