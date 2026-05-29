<?php

namespace App\Domain\Enums;

enum PricingScheme: string {
    case Fixed ='fixed'; // tarif unique
    case PeakOffPeak ='peak_off_peak';    //HP/HC
    case Seasonal ='seasonal';    // saisonnier
    case Dynamic ='dynamic';    
}