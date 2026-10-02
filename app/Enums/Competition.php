<?php

namespace App\Enums;

enum Competition: int
{
    case Molkky   = 1;
    case Tore     = 2;
    case VSArashi = 3;

    public function label(): string
    {
        return match ($this) {
            self::Molkky   => 'モルック',
            self::Tore     => 'Tore',
            self::VSArashi => 'VS嵐',
        };
    }

    /** @return array<int, self> */
    public static function ordered(): array
    {
        return self::cases();
    }
}
