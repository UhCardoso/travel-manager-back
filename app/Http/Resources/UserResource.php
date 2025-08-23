<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserResourceData",
 *     title="User Resource Data",
 *     description="Dados do usuário retornados pela API",
 *
 *     @OA\Property(property="id", type="integer", example=1, description="ID único do usuário"),
 *     @OA\Property(property="name", type="string", example="João Silva", description="Nome completo do usuário"),
 *     @OA\Property(property="email", type="string", format="email", example="joao@email.com", description="Email do usuário"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, description="Data de verificação do email"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Data de criação do registro"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Data da última atualização")
 * )
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
