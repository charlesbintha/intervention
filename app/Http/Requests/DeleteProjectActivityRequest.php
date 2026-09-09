<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteProjectActivityRequest extends FormRequest
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
            'change_reason' => [$tracking?->baseline_approved_at ? 'required' : 'nullable', 'string', 'min:5'],
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
            'change_reason.required' => 'La justification de la suppression est obligatoire après validation du planning initial.',
            'change_reason.min' => 'La justification doit contenir au moins 5 caractères.',
        ];
    }
}
