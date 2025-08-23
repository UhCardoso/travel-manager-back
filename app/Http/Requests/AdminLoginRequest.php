<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AdminLoginRequest",
 *     required={"email", "password"},
 *
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email do administrador"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         minLength=6,
 *         description="Senha do administrador"
 *     )
 * )
 */
class AdminLoginRequest extends FormRequest
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
