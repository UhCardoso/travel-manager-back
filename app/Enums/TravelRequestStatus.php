<?php

namespace App\Enums;

enum TravelRequestStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case CANCELLED = 'cancelled';

    /**
     * Get all available statuses
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all statuses for select options
     */
    public static function forSelect(): array
    {
        return [
            self::PENDING->value => 'Pendente',
            self::APPROVED->value => 'Aprovado',
            self::CANCELLED->value => 'Cancelado',
        ];
    }

    /**
     * Get status label for display
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::APPROVED => 'Aprovado',
            self::CANCELLED => 'Cancelado',
        };
    }
}
