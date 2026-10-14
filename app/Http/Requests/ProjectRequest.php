<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('project') ? $this->user()->can('update', $this->route('project')) : (bool) $this->user();
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:200'], 'alternative_title' => ['nullable', 'string', 'max:200'], 'logline' => ['required', 'string', 'max:500'], 'short_synopsis' => ['required', 'string', 'max:1200'], 'description' => ['required', 'string', 'max:10000'], 'content_type_id' => ['required', 'exists:content_types,id'], 'country_id' => ['required', 'exists:countries,id'], 'project_stage_id' => ['required', 'exists:project_stages,id'], 'production_year' => ['nullable', 'integer', 'between:1900,'.(now()->year + 5)], 'runtime_minutes' => ['nullable', 'integer', 'min:1', 'max:1000'], 'episode_count' => ['nullable', 'integer', 'min:1', 'max:5000'], 'episode_runtime_minutes' => ['nullable', 'integer', 'min:1', 'max:1000'], 'budget' => ['nullable', 'numeric', 'min:0'], 'currency' => ['nullable', 'string', 'size:3'], 'release_status' => ['nullable', 'string', 'max:80'], 'rights_available' => ['nullable', 'string', 'max:5000'], 'territories' => ['nullable', 'string', 'max:5000'], 'restricted_territories' => ['nullable', 'string', 'max:5000'], 'trailer_url' => ['nullable', 'url', 'max:2048'], 'teaser_url' => ['nullable', 'url', 'max:2048'], 'audio_preview_url' => ['nullable', 'url', 'max:2048'], 'screener_url' => ['nullable', 'url', 'max:2048'], 'visibility' => ['required', Rule::in(['public', 'members'])], 'organisation_id' => ['nullable', 'exists:organisations,id'], 'stand_id' => ['nullable', 'exists:stands,id'], 'genres' => ['array'], 'genres.*' => ['integer', 'exists:genres,id'], 'languages' => ['array'], 'languages.*' => ['integer', 'exists:languages,id'], 'subtitle_languages' => ['array'], 'subtitle_languages.*' => ['integer', 'exists:languages,id'], 'tags' => ['array'], 'tags.*' => ['integer', 'exists:project_tags,id'], 'co_production_countries' => ['array'], 'co_production_countries.*' => ['integer', 'exists:countries,id'], 'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144', 'dimensions:max_width=6000,max_height=6000'], 'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:max_width=8000,max_height=5000'], 'documents' => ['array', 'max:10'], 'documents.*' => ['file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:15360'], 'document_access' => ['nullable', Rule::in(['public', 'authenticated', 'request_required', 'private'])]];
    }
}
