<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string|current_password',
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */

    public function messages(): array
    {
        return [
            'current_password.required' => 'Password lama harus diisi!',
            'current_password.string' => 'Password lama harus berupa string!',
            'current_password.current_password' => 'Password lama salah!',
            'password.required' => 'Password baru harus diisi!',
            'password.string' => 'Password baru harus berupa string!',
            'password.min' => 'Password baru minimal 8 karakter!',
        ];
    }
}
