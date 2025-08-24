<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UserLoginRequest",
 *     required={"email", "password"},
 *
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email do usuário"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         minLength=6,
 *         description="Senha do usuário"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserLoginRequestErrors",
 *     title="User Login Request Error Messages",
 *     description="Mensagens de erro para o login de usuário",
 *
 *     @OA\Property(property="email.required", type="string", example="O email é obrigatório."),
 *     @OA\Property(property="email.email", type="string", example="O email deve ser um endereço válido."),
 *     @OA\Property(property="password.required", type="string", example="A senha é obrigatória."),
 *     @OA\Property(property="password.string", type="string", example="A senha deve ser uma string."),
 *     @OA\Property(property="password.min", type="string", example="A senha deve ter pelo menos 6 caracteres.")
 * )
 */
class UserLoginRequest extends FormRequest
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
            'email' => 'required|email',
            'password' => 'required|string|min:6',
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
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser um endereço válido.',
            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser uma string.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
        ];
    }
}
