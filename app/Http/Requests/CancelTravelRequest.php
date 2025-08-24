<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CancelTravelRequest",
 *     description="Parâmetros para cancelar uma solicitação de viagem",
 *
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"cancelled"},
 *         description="Status para cancelar a solicitação de viagem",
 *         required=true
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="CancelTravelRequestErrors",
 *     title="Cancel Travel Request Error Messages",
 *     description="Mensagens de erro para cancelamento de solicitação de viagem",
 *
 *     @OA\Property(property="status.required", type="string", example="O status é obrigatório."),
 *     @OA\Property(property="status.string", type="string", example="O status deve ser uma string."),
 *     @OA\Property(property="status.in", type="string", example="O status deve ser cancelled."),
 *     @OA\Property(property="status.cannot_cancel_approved", type="string", example="Não é possível cancelar uma solicitação aprovada.")
 * )
 */
class CancelTravelRequest extends FormRequest
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
            'status' => 'required|string|in:cancelled',
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
            'status.in' => 'O status deve ser cancelled.',
        ];
    }
}
