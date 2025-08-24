<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="IndexTravelRequest",
 *     description="Parâmetros opcionais para filtrar solicitações de viagem",
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         nullable=true,
 *         description="Nome da viagem para filtrar solicitações (opcional)"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"pending", "approved", "cancelled"},
 *         nullable=true,
 *         description="Status da solicitação de viagem (opcional)"
 *     ),
 *     @OA\Property(
 *         property="departure_date",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         description="Data de partida para filtrar solicitações (opcional)"
 *     ),
 *     @OA\Property(
 *         property="return_date",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         description="Data de retorno para filtrar solicitações (opcional)"
 *     ),
 *     @OA\Property(
 *         property="per_page",
 *         type="integer",
 *         minimum=1,
 *         maximum=100,
 *         default=15,
 *         nullable=true,
 *         description="Número de itens por página (opcional, padrão: 15)"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="IndexTravelRequestErrors",
 *     title="Index Travel Request Error Messages",
 *     description="Mensagens de erro para filtros de solicitação de viagem",
 *
 *     @OA\Property(property="status.in", type="string", example="O status deve ser um dos valores válidos: pending, approved, cancelled."),
 *     @OA\Property(property="departure_date.date", type="string", example="A data de partida deve ser uma data válida."),
 *     @OA\Property(property="return_date.date", type="string", example="A data de retorno deve ser uma data válida."),
 *     @OA\Property(property="return_date.after", type="string", example="A data de retorno deve ser posterior à data de partida."),
 *     @OA\Property(property="per_page.integer", type="string", example="O número de itens por página deve ser um número inteiro."),
 *     @OA\Property(property="per_page.min", type="string", example="O número de itens por página deve ser pelo menos 1."),
 *     @OA\Property(property="per_page.max", type="string", example="O número de itens por página não pode ser maior que 100.")
 * )
 */
class IndexTravelRequest extends FormRequest
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
            'name' => 'nullable|string|max:255',
            'status' => 'nullable|string',
            'departure_date' => 'nullable|date',
            'return_date' => 'nullable|date',
            'per_page' => 'nullable|integer|min:1|max:100',
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
            'name.string' => 'O nome deve ser uma string.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'status.in' => 'O status deve ser um dos valores válidos: pending, approved, cancelled.',
            'departure_date.date' => 'A data de partida deve ser uma data válida.',
            'return_date.date' => 'A data de retorno deve ser uma data válida.',
            'return_date.after' => 'A data de retorno deve ser posterior à data de partida.',
            'per_page.integer' => 'O número de itens por página deve ser um número inteiro.',
            'per_page.min' => 'O número de itens por página deve ser pelo menos 1.',
            'per_page.max' => 'O número de itens por página não pode ser maior que 100.',
        ];
    }
}
