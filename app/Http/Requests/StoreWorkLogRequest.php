<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('projectTracking'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_activity_id' => ['required', 'integer', 'exists:project_activities,id'],
            'started_at' => ['required', 'date', 'before_or_equal:now'],
            'ended_at' => ['required', 'date', 'after:started_at'],
            'quantity_completed' => ['required', 'numeric', 'gt:0', 'lte:100'],
            'remaining_quantity_estimate' => ['nullable', 'numeric', 'min:0'],
            'work_description' => ['required', 'string', 'min:5'],
            'difficulties' => ['nullable', 'string'],
        ];
    }

    /**
     * Get the validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'started_at.required' => 'La date et l’heure de début sont obligatoires.',
            'started_at.before_or_equal' => 'Le début des travaux ne peut pas être dans le futur.',
            'ended_at.required' => 'La date et l’heure de fin sont obligatoires.',
            'ended_at.after' => 'La fin des travaux doit être postérieure au début.',
        ];
    }
}
