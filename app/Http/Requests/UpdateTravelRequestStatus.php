<?php

namespace App\Http\Requests;

use App\Enums\TravelRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateTravelRequestStatus",
 *     description="Parâmetros para atualizar o status de uma solicitação de viagem",
 *     required={"status"},
 *
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"pending", "approved", "cancelled"},
 *         description="Novo status da solicitação de viagem"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UpdateTravelRequestStatusErrors",
 *     title="Update Travel Request Status Error Messages",
 *     description="Mensagens de erro para atualização de status de solicitação de viagem",
 *
 *     @OA\Property(property="status.required", type="string", example="O status é obrigatório."),
 *     @OA\Property(property="status.string", type="string", example="O status deve ser uma string."),
 *     @OA\Property(property="status.in", type="string", example="O status deve ser um dos valores válidos: pending, approved, cancelled.")
 * )
 */
class UpdateTravelRequestStatus extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|string|in:'.implode(',', TravelRequestStatus::values()),
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'O status é obrigatório.',
            'status.string' => 'O status deve ser uma string.',
            'status.in' => 'O status deve ser um dos valores válidos: pending, approved, cancelled.',
        ];
    }
}
