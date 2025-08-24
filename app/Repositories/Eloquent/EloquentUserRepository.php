<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface
{
    protected User $model;

    /**
     * Constructor to inject the model
     */
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
     * Create a new user
     */
    public function store(array $data): User
    {
        return $this->model->create($data);
    }
}
