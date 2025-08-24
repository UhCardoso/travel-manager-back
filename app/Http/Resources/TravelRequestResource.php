<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="TravelRequestResource",
 *     title="Travel Request Resource",
 *     description="Recurso de solicitação de viagem",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="user", type="object", nullable=true, description="Informações do usuário (quando disponível)"),
 *     @OA\Property(property="name", type="string", example="Viagem para Barcelona"),
 *     @OA\Property(property="country", type="string", example="Espanha"),
 *     @OA\Property(property="town", type="string", example="Barcelona", nullable=true),
 *     @OA\Property(property="state", type="string", example="Catalunha", nullable=true),
 *     @OA\Property(property="region", type="string", example="Nordeste", nullable=true),
 *     @OA\Property(property="departure_date", type="string", format="date", example="2025-08-01"),
 *     @OA\Property(property="return_date", type="string", format="date", example="2025-08-07"),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="created_at", type="string", format="datetime"),
 *     @OA\Property(property="updated_at", type="string", format="datetime")
 * )
 */
class TravelRequestResource extends JsonResource
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
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'name' => $this->name,
            'country' => $this->country,
            'town' => $this->town,
            'state' => $this->state,
            'region' => $this->region,
            'departure_date' => $this->departure_date?->format('Y-m-d'),
            'return_date' => $this->return_date?->format('Y-m-d'),
            'status' => $this->status?->value ?? $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
