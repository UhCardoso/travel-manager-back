<?php

namespace App\Repositories\Eloquent;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Update user
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(User $user): bool
    {
        return $user->role === UserRole::ADMIN->value;
    }

    /**
     * Check if user is user
     */
    public function isUser(User $user): bool
    {
        return $user->role === UserRole::USER->value;
    }
}
