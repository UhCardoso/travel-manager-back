<?php

namespace App\Models;

use App\Traits\HasRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User Model",
 *     description="Modelo de usuário do sistema",
 *
 *     @OA\Property(property="id", type="integer", example=1, description="ID único do usuário"),
 *     @OA\Property(property="name", type="string", example="João Silva", description="Nome completo do usuário"),
 *     @OA\Property(property="email", type="string", format="email", example="joao@example.com", description="Email único do usuário"),
 *     @OA\Property(property="role", type="string", enum={"admin","user"}, example="user", description="Papel do usuário no sistema"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, description="Data de verificação do email"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Data de criação do registro"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Data da última atualização")
 * )
 *
 * @OA\RequestBody(
 *     request="UserCreate",
 *     description="Dados para criação de usuário",
 *     required=true,
 *
 *     @OA\JsonContent(
 *         required={"name","email","password","role"},
 *
 *         @OA\Property(property="name", type="string", example="João Silva", description="Nome completo do usuário"),
 *         @OA\Property(property="email", type="string", format="email", example="joao@example.com", description="Email único do usuário"),
 *         @OA\Property(property="password", type="string", format="password", example="senha123", description="Senha do usuário", minLength=6),
 *         @OA\Property(property="role", type="string", enum={"admin","user"}, example="user", description="Papel do usuário no sistema")
 *     )
 * )
 *
 * @OA\RequestBody(
 *     request="UserUpdate",
 *     description="Dados para atualização de usuário",
 *     required=true,
 *
 *     @OA\JsonContent(
 *
 *         @OA\Property(property="name", type="string", example="João Silva", description="Nome completo do usuário"),
 *         @OA\Property(property="email", type="string", format="email", example="joao@example.com", description="Email único do usuário"),
 *         @OA\Property(property="password", type="string", format="password", example="novaSenha123", description="Nova senha do usuário", minLength=6),
 *         @OA\Property(property="role", type="string", enum={"admin","user"}, example="user", description="Papel do usuário no sistema")
 *     )
 * )
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRole, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'role',
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
