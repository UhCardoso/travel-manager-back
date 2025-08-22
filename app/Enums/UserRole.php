<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    /**
     * Get all available roles
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all cases as an array for select inputs
     *
     * @return array<string, string>
     */
    public static function forSelect(): array
    {
        return [
            self::ADMIN->value => 'Administrador',
            self::USER->value => 'Usuário',
        ];
    }

    /**
     * Get role label for display
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::USER => 'Usuário',
        };
    }
}
