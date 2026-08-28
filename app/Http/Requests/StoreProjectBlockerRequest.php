<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectBlockerRequest extends FormRequest
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
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'min:5'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'proposed_solution' => ['nullable', 'string'],
            'opened_at' => ['required', 'date'],
        ];
    }
}
