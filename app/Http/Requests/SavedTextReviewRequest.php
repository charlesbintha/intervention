<?php

namespace App\Http\Requests;

use App\Services\ReviewableText;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavedTextReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'field' => ['required', 'string', Rule::in(ReviewableText::fields((string) $this->route('type')))],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['field.in' => 'Ce champ ne peut pas être corrigé par l’IA.', 'field.required' => 'Choisissez le texte à relire.'];
    }
}
