<?php

namespace App\Http\Requests\Technician;

use App\Models\WorkOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Subida de fotos/evidencias por el técnico dueño de la OT (en diligenciamiento). */
class StoreWorkOrderPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workOrder = $this->route('work_order');

        return $workOrder instanceof WorkOrder
            && $this->user()?->technician?->id === $workOrder->technician_id
            && in_array($workOrder->status, ['assigned', 'in_progress'], true);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'], // 15 MB antes de comprimir
        ];
    }
}
