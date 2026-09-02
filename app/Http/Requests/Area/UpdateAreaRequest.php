<?php

namespace App\Http\Requests\Area;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update areas') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $area = $this->route('area');

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('areas', 'name')
                    ->where(fn ($q) => $q->where('client_id', $area->client_id))
                    ->ignore($area->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }
}
