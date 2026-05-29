<?php

namespace App\Domain\Enums;

enum ArbitrageAction: string {
    case Consume ='consume'; // tirer du réseau
    case Store ='store';    // charger un actif de stockage
    case Inject ='inject';    // réinjecter au reaseau
    case Idle ='idle';    
}