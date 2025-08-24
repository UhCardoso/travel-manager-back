<?php

namespace App\Traits;

use App\Enums\UserRole;

trait HasRole
{
    /**
     * Checks if the user is an administrator
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::ADMIN->value);
    }

    /**
     * Checks if the user is a regular user
     */
    public function isUser(): bool
    {
        return $this->hasRole(UserRole::USER->value);
    }

    /**
     * Checks if the user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Checks if the user has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Checks if the user has all specified roles
     */
    public function hasAllRoles(array $roles): bool
    {
        return count(array_intersect([$this->role], $roles)) === count($roles);
    }
}
