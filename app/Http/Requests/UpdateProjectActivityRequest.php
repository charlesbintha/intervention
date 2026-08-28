<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $activity = $this->route('activity');

        return $activity && $this->user()->can('update', $activity->projectTracking);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tracking = $this->route('activity')?->projectTracking;

        return [
            'lot_name' => ['required', 'string', 'max:255'],
            'phase_name' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_agents' => ['nullable', 'array'],
            'assigned_agents.*' => ['required', 'string', 'max:255', 'distinct'],
            'external_stakeholders' => ['nullable', 'array', 'max:20'],
            'external_stakeholders.*.last_name' => ['required', 'string', 'max:255'],
            'external_stakeholders.*.first_name' => ['required', 'string', 'max:255'],
            'external_stakeholders.*.email' => ['nullable', 'email', 'max:255'],
            'current_start_date' => ['required', 'date'],
            'current_end_date' => ['required', 'date', 'after_or_equal:current_start_date'],
            'planned_quantity' => ['required', 'numeric', 'gt:0', 'lte:100'],
            'status' => ['required', 'in:not_started,in_progress,completed,suspended,blocked'],
            'deliverable' => ['nullable', 'string', 'max:255'],
            'priority' => ['required', 'in:low,normal,high,critical'],
            'change_reason' => [$tracking?->baseline_approved_at ? 'required' : 'nullable', 'string', 'min:5'],
        ];
    }
}
