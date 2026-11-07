<?php

namespace App\Http\Requests;

use App\Services\ReviewableText;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewTextRequest extends FormRequest
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
            'type' => ['required', Rule::in(array_keys(ReviewableText::types()))],
            'id' => ['nullable', 'integer', 'min:1'],
            'fields' => ['required', 'array', 'min:1', 'max:6'],
            'fields.*' => ['required', 'string', 'max:18000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'fields.required' => 'Choisissez un texte à relire.',
            'fields.*.max' => 'Ce texte est trop long pour une relecture unique (18 000 caractères maximum).',
            'type.in' => 'Ce formulaire ne prend pas en charge la relecture IA.',
        ];
    }
}
