<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectTrackingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'external_project_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('project_trackings')->where('user_id', $this->user()->id),
            ],
            'external_project_name' => ['required', 'string', 'max:255'],
            'external_opportunity_id' => ['nullable', 'string', 'max:255'],
            'subsidiary' => ['required', 'in:GUT,CP,UTA,UA,UTE,UC'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'current_start_date' => ['nullable', 'date'],
            'current_end_date' => ['nullable', 'date', 'after_or_equal:current_start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'external_project_code.required' => 'Veuillez sélectionner un projet.',
            'external_project_code.unique' => 'Vous avez déjà créé un suivi pour ce projet.',
            'external_project_name.required' => 'Le nom du projet est obligatoire.',
            'subsidiary.required' => 'Veuillez sélectionner une filiale.',
            'current_end_date.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
        ];
    }
}
