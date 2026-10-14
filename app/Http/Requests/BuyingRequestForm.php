<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuyingRequestForm extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:200'], 'organisation_id' => ['nullable', 'exists:organisations,id'], 'description' => ['required', 'string', 'max:8000'], 'additional_requirements' => ['nullable', 'string', 'max:5000'], 'production_status' => ['nullable', 'string', 'max:100'], 'minimum_production_year' => ['nullable', 'integer', 'between:1900,'.(now()->year + 2)], 'runtime_requirements' => ['nullable', 'string', 'max:255'], 'episode_requirements' => ['nullable', 'string', 'max:255'], 'rights_sought' => ['nullable', 'string', 'max:3000'], 'territories' => ['nullable', 'string', 'max:3000'], 'deadline' => ['nullable', 'date', 'after_or_equal:today'], 'content_types' => ['required', 'array', 'min:1'], 'content_types.*' => ['exists:content_types,id'], 'genres' => ['array'], 'genres.*' => ['exists:genres,id'], 'countries' => ['array'], 'countries.*' => ['exists:countries,id'], 'languages' => ['array'], 'languages.*' => ['exists:languages,id']];
    }
}
