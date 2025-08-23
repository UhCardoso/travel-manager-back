<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Auth",
 *     title="Auth",
 *     description="Modelo de autenticação",
 *
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/User"
 *     ),
 *     @OA\Property(
 *         property="token",
 *         type="string",
 *         example="1|a1b2c3d4e5f6g7h8i9j0"
 *     ),
 *     @OA\Property(
 *         property="token_type",
 *         type="string",
 *         example="Bearer"
 *     )
 * )
 */
class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this->resource['user']),
            'token' => $this->resource['token'],
            'token_type' => $this->resource['token_type'],
        ];
    }
}
