<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StoreUserRequest",
 *     required={"name", "email", "password", "password_confirmation"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Nome do usuário"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         maxLength=255,
 *         description="Email do usuário"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         minLength=8,
 *         description="Senha do usuário"
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         description="Confirmação da senha"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="StoreUserRequestErrors",
 *     title="Store User Request Error Messages",
 *     description="Mensagens de erro para a criação de usuário",
 *
 *     @OA\Property(property="name.required", type="string", example="O nome é obrigatório."),
 *     @OA\Property(property="name.string", type="string", example="O nome deve ser uma string."),
 *     @OA\Property(property="name.max", type="string", example="O nome não pode ter mais de 255 caracteres."),
 *     @OA\Property(property="email.required", type="string", example="O email é obrigatório."),
 *     @OA\Property(property="email.string", type="string", example="O email deve ser uma string."),
 *     @OA\Property(property="email.email", type="string", example="O email deve ser um endereço válido."),
 *     @OA\Property(property="email.max", type="string", example="O email não pode ter mais de 255 caracteres."),
 *     @OA\Property(property="email.unique", type="string", example="Este email já está em uso."),
 *     @OA\Property(property="password.required", type="string", example="A senha é obrigatória."),
 *     @OA\Property(property="password.string", type="string", example="A senha deve ser uma string."),
 *     @OA\Property(property="password.min", type="string", example="A senha deve ter pelo menos 8 caracteres."),
 *     @OA\Property(property="password.confirmed", type="string", example="A confirmação da senha não confere.")
 * )
 */
class StoreUserRequest extends FormRequest
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
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser uma string.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'O email é obrigatório.',
            'email.string' => 'O email deve ser uma string.',
            'email.email' => 'O email deve ser um endereço válido.',
            'email.max' => 'O email não pode ter mais de 255 caracteres.',
            'email.unique' => 'Este email já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser uma string.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
        ];
    }
}
