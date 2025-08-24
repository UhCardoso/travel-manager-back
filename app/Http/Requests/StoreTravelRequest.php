<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StoreTravelRequest",
 *     required={"name", "country", "departure_date", "return_date"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Nome da viagem"
 *     ),
 *     @OA\Property(
 *         property="country",
 *         type="string",
 *         maxLength=255,
 *         description="País de destino"
 *     ),
 *     @OA\Property(
 *         property="town",
 *         type="string",
 *         maxLength=255,
 *         nullable=true,
 *         description="Cidade de destino"
 *     ),
 *     @OA\Property(
 *         property="state",
 *         type="string",
 *         maxLength=255,
 *         nullable=true,
 *         description="Estado de destino"
 *     ),
 *     @OA\Property(
 *         property="region",
 *         type="string",
 *         maxLength=255,
 *         nullable=true,
 *         description="Região de destino"
 *     ),
 *     @OA\Property(
 *         property="departure_date",
 *         type="string",
 *         format="date",
 *         description="Data de partida"
 *     ),
 *     @OA\Property(
 *         property="return_date",
 *         type="string",
 *         format="date",
 *         description="Data de retorno"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="StoreTravelRequestErrors",
 *     title="Store Travel Request Error Messages",
 *     description="Mensagens de erro para a criação de solicitação de viagem",
 *
 *     @OA\Property(property="name.required", type="string", example="O nome é obrigatório."),
 *     @OA\Property(property="name.string", type="string", example="O nome deve ser uma string."),
 *     @OA\Property(property="name.max", type="string", example="O nome não pode ter mais de 255 caracteres."),
 *     @OA\Property(property="country.required", type="string", example="O país é obrigatório."),
 *     @OA\Property(property="country.string", type="string", example="O país deve ser uma string."),
 *     @OA\Property(property="country.max", type="string", example="O país não pode ter mais de 255 caracteres."),
 *     @OA\Property(property="town.string", type="string", example="A cidade deve ser uma string."),
 *     @OA\Property(property="town.max", type="string", example="A cidade não pode ter mais de 255 caracteres."),
 *     @OA\Property(property="state.string", type="string", example="O estado deve ser uma string."),
 *     @OA\Property(property="state.max", type="string", example="O estado não pode ter mais de 255 caracteres."),
 *     @OA\Property(property="region.string", type="string", example="A região deve ser uma string."),
 *     @OA\Property(property="region.max", type="string", example="A região não pode ter mais de 255 caracteres."),
 *     @OA\Property(property="departure_date.required", type="string", example="A data de partida é obrigatória."),
 *     @OA\Property(property="departure_date.date", type="string", example="A data de partida deve ser uma data válida."),
 *     @OA\Property(property="return_date.required", type="string", example="A data de retorno é obrigatória."),
 *     @OA\Property(property="return_date.date", type="string", example="A data de retorno deve ser uma data válida."),
 *     @OA\Property(property="return_date.after", type="string", example="A data de retorno deve ser posterior à data de partida.")
 * )
 */
class StoreTravelRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'town' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'departure_date' => 'required|date',
            'return_date' => 'required|date|after:departure_date',
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
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser uma string.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'country.required' => 'O país é obrigatório.',
            'country.string' => 'O país deve ser uma string.',
            'country.max' => 'O país não pode ter mais de 255 caracteres.',
            'town.string' => 'A cidade deve ser uma string.',
            'town.max' => 'A cidade não pode ter mais de 255 caracteres.',
            'state.string' => 'O estado deve ser uma string.',
            'state.max' => 'O estado não pode ter mais de 255 caracteres.',
            'region.string' => 'A região deve ser uma string.',
            'region.max' => 'A região não pode ter mais de 255 caracteres.',
            'departure_date.required' => 'A data de partida é obrigatória.',
            'departure_date.date' => 'A data de partida deve ser uma data válida.',
            'return_date.required' => 'A data de retorno é obrigatória.',
            'return_date.date' => 'A data de retorno deve ser uma data válida.',
            'return_date.after' => 'A data de retorno deve ser posterior à data de partida.',
        ];
    }
}
