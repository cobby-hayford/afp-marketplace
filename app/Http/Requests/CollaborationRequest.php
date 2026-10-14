<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CollaborationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:200'], 'collaboration_type_id' => ['required', 'exists:collaboration_types,id'], 'organisation_id' => ['nullable', 'exists:organisations,id'], 'project_id' => ['nullable', 'exists:projects,id'], 'country_id' => ['required', 'exists:countries,id'], 'content_type_id' => ['nullable', 'exists:content_types,id'], 'genre_id' => ['nullable', 'exists:genres,id'], 'description' => ['required', 'string', 'max:8000'], 'closing_date' => ['nullable', 'date', 'after_or_equal:today'], 'target_countries' => ['array'], 'target_countries.*' => ['exists:countries,id']];
    }
}
