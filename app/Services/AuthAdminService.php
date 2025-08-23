<?php

namespace App\Services;

use App\Http\Resources\AuthResource;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthAdminService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Login admin user
     */
    public function login(string $email, string $password): AuthResource
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'message' => ['Email ou senha incorretos.'],
            ]);
        }

        $token = $user->createToken('admin-access')->plainTextToken;

        return new AuthResource([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout admin user
     */
    public function logout($user): array
    {
        if ($user instanceof User) {
            $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        } else {
            $userModel = $this->userRepository->findById($user->id);
            $userModel->tokens()->where('id', $userModel->currentAccessToken()->id)->delete();
        }

        return [
            'success' => true,
        ];
    }
}
