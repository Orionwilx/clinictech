<?php

namespace App\Http\Requests\WorkOrder;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Subida de fotos/evidencias a la OT por el admin (llena OT en cualquier estado). */
class StoreWorkOrderPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update work_orders') ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'], // 15 MB antes de comprimir
        ];
    }
}
