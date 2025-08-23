<?php

namespace App\Services;

use App\Http\Resources\AuthResource;
use App\Repositories\Contracts\UserRepositoryInterface;

class AuthUserService
{
    protected UserRepositoryInterface $userRepository;

    /**
     * Create a new AuthUserService instance
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Create a new user
     */
    public function create(array $data): AuthResource
    {
        $user = $this->userRepository->create($data);

        $token = $user->createToken('user-access')->plainTextToken;

        return new AuthResource([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
