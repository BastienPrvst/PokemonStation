<?php

namespace App\Enum;

enum TradeUserStatus : int
{
	case ONGOING = 1;
	case CANCELED = 2;
	case ACCEPTED = 3;

	public function label(): string
	{
		return match ($this) {
			self::ONGOING => 'En cours',
			self::CANCELED => 'Annulé',
			self::ACCEPTED => 'Accepté'
		};
	}
}
