<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectTrackingRequest extends FormRequest
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
            'subsidiary' => ['required', 'in:GUT,CP,UTA,UA,UTE,UC'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'current_start_date' => ['nullable', 'date'],
            'current_end_date' => ['nullable', 'date', 'after_or_equal:current_start_date'],
            'status' => ['required', 'in:draft,active,suspended,completed'],
        ];
    }
}
