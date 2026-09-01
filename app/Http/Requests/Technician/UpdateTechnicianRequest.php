<?php

namespace App\Http\Requests\Technician;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateTechnicianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update technicians') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $technician = $this->route('technician');

        return [
            'name' => ['required', 'string', 'max:255'],
            'document' => ['required', 'string', 'max:255', Rule::unique('technicians', 'document')->ignore($technician->id)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($technician->user_id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }
}
