<?php

use App\Domain\Enums\ArbitrageAction;
use App\Domain\Services\ArbitrageEngine;
use App\Domain\Services\PricingService;
use App\Models\Asset;
use App\Models\Contract;
use App\Models\Household;
use App\Models\PricePoint;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('charge la batterie quand le prix est bas et décharge quand il est haut', function () {
    $household = Household::create([
        'name' => 'Test', 'country_code' => 'FR',
    ]);

    // Contrat dynamique => le prix effectif suit le spot.
    Contract::create([
        'household_id' => $household->id,
        'provider' => 'Test',
        'pricing_scheme' => 'dynamic',
    ]);

    $household->assets()->create([
        'type' => 'battery', 'label' => 'Bat',
        'capacity_kwh' => 10, 'max_power_kw' => 5, 'current_charge_kwh' => 0,
    ]);

    // 4 créneaux : 2 bas (10c, 20c) puis 2 hauts (80c, 90c).
    $prices = collect([10, 20, 80, 90])->map(function ($cents, $i) {
        return PricePoint::create([
            'country_code' => 'FR',
            'starts_at' => CarbonImmutable::parse('2026-01-01 00:00')->addHours($i),
            'price_cents' => $cents,
        ]);
    });

    $household->load(['assets', 'contract.tariffRates']);

    $engine = new ArbitrageEngine(new PricingService());
    $decisions = $engine->plan($household, $prices);

    // Aux créneaux bas : on stocke. Aux créneaux hauts : on réinjecte.
    expect($decisions[0]->action)->toBe(ArbitrageAction::Store)
        ->and($decisions[1]->action)->toBe(ArbitrageAction::Store)
        ->and($decisions[2]->action)->toBe(ArbitrageAction::Idle)
        ->and($decisions[3]->action)->toBe(ArbitrageAction::Inject);
});