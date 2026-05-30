<?php

namespace App\Domain\Services;

use App\Domain\Enums\PricingScheme;
use App\Models\Contract;
use App\Models\PricePoint;
use Carbon\CarbonImmutable;

class PricingService
{
    /**
     * Prix effectif du kWh sur un créneau, en centimes.
     * Combine le contrat et, le cas échéant, le prix spot du marché.
     */
    public function effectivePriceCents(
        Contract $contract,
        CarbonImmutable $slotStart,
        ?PricePoint $spot = null,
    ): int {
        return match ($contract->pricing_scheme) {
            PricingScheme::Dynamic => $this->dynamicPrice($spot),
            default => $this->tariffPrice($contract, $slotStart),
        };
    }

    /** Tarif dynamique : on suit le prix spot directement. */
    private function dynamicPrice(?PricePoint $spot): int
    {
        if ($spot === null) {
            throw new \RuntimeException('Prix spot manquant pour un contrat dynamique.');
        }
        return $spot->price_cents; // peut être négatif
    }

    /** Tarif réglementé : on sélectionne la bonne tranche tarifaire. */
    private function tariffPrice(Contract $contract, CarbonImmutable $slotStart): int
    {
        $month = $slotStart->month;
        $time = $slotStart->format('H:i:s');

        $applicable = $contract->tariffRates
            ->filter(function ($rate) use ($month, $time) {
                $seasonOk = empty($rate->months) || in_array($month, $rate->months, true);
                $timeOk = $rate->starts_at === null
                    || ($time >= $rate->starts_at && $time < $rate->ends_at);
                return $seasonOk && $timeOk;
            })
            ->sortByDesc('priority'); // effacement > HC > HP en cas de chevauchement

        $rate = $applicable->first();

        if ($rate === null) {
            throw new \RuntimeException("Aucune tranche tarifaire pour {$slotStart}.");
        }

        return $rate->price_cents;
    }
}