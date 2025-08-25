<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Http\Resources\AuthResource;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
    public function store(array $data): AuthResource
    {
        $data['role'] = UserRole::USER->value;

        $user = $this->userRepository->store($data);

        $token = $user->createToken('user-access')->plainTextToken;

        return new AuthResource([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Login user
     */
    public function login(string $email, string $password): AuthResource
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email ou senha inválidos.'],
            ]);
        }

        if ($user->role == UserRole::ADMIN->value) {
            throw ValidationException::withMessages([
                'email' => ["Administrador deve fazer login via painel de administração."],
            ]);
        }

        $token = $user->createToken('user-access')->plainTextToken;

        return new AuthResource([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout user
     */
    public function logout($user): array
    {
        if ($user instanceof User) {
            if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
            }
        } else {
            $userModel = $this->userRepository->findById($user->id);
            if ($userModel && method_exists($userModel, 'currentAccessToken') && $userModel->currentAccessToken()) {
                $userModel->tokens()->where('id', $userModel->currentAccessToken()->id)->delete();
            }
        }

        return [
            'success' => true,
        ];
    }
}
