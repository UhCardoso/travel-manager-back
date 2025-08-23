<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SuccessResourceData",
 *     title="Success Resource Data",
 *     description="Estrutura padrão de resposta de sucesso da API",
 *
 *     @OA\Property(property="success", type="boolean", example=true, description="Indica se a operação foi bem-sucedida"),
 *     @OA\Property(property="message", type="string", example="Operação realizada com sucesso", description="Mensagem descritiva da operação"),
 *     @OA\Property(property="data", type="object", nullable=true, description="Dados retornados pela operação")
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
