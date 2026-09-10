<?php

namespace App\Http\Requests\Report;

use App\Http\Controllers\Admin\ReportController;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Generar un reporte en cola. */
class ExportReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view reports') ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'report_type' => ['required', Rule::in(array_keys(ReportController::TYPES))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
}
