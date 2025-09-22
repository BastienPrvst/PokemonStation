<?php

namespace App\Enum;

enum TradeStatus: int
{
    case CREATED = 1;
    case ONGOING = 2;
    case COMPLETED = 3;
    case CANCELLED = 4;

    public function label(): string
    {
        return match ($this) {
            self::CREATED   => 'Créé',
            self::ONGOING   => 'En cours',
            self::COMPLETED => 'Terminé',
            self::CANCELLED => 'Annulé',
        };
    }
}
