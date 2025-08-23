<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     title="Success Response",
 *     description="Modelo de resposta de sucesso",
 *
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operação realizada com sucesso"),
 *     @OA\Property(property="data", type="object", nullable=true)
 * )
 */
class SuccessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => $this->resource['message'] ?? 'Operação realizada com sucesso',
            'data' => $this->resource['data'] ?? null,
        ];
    }
}
