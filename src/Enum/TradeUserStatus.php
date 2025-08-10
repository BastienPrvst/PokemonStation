<?php

namespace App\Enum;

enum TradeUserStatus : int
{
	case ONGOING = 1;
	case CANCELED = 2;
	case VALIDATED = 3;
	case ACCEPTED = 4;

	public function label(): string
	{
		return match ($this) {
			self::ONGOING => 'En cours',
			self::CANCELED => 'Annulé',
			self::VALIDATED => 'Validé',
			self::ACCEPTED => 'Accepté',
		};
	}
}
