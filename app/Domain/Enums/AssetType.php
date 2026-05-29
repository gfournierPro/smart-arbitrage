<?php

namespace App\Domain\Enums;

enum AssetType: string
{
    case Battery = 'battery';
    case ElectricVehicule ='electric_vehicule';
    case SolarPanel = 'solar_panel';
    case WaterHeater ='water_heater';

    // asset can store energie
    public function isStorage(): bool
    {
        return in_array($this, [self::Battery, self::ElectricVehicule], true);
    }

    public function isProducer(): bool 
    {
        return $this === self::SolarPanel;
    }
}