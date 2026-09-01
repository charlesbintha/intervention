<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectActionRequest extends FormRequest
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
            'project_activity_id' => ['nullable', 'integer', 'exists:project_activities,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'responsible_names' => ['required', 'array', 'min:1'],
            'responsible_names.*' => ['required', 'string', 'max:255', 'distinct'],
            'due_date' => ['required', 'date'],
            'priority' => ['required', 'in:low,normal,high,critical'],
        ];
    }

    public function messages(): array
    {
        return [
            'responsible_names.required' => 'Veuillez sélectionner au moins un responsable.',
            'responsible_names.min' => 'Veuillez sélectionner au moins un responsable.',
            'responsible_names.*.distinct' => 'Chaque responsable ne peut être sélectionné qu’une seule fois.',
        ];
    }
}
