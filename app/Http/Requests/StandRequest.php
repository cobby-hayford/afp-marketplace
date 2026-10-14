<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:180'],
            'stand_type' => ['required', 'string', 'max:80'],
            'country_id' => ['required', 'exists:countries,id'],
            'organisation_id' => ['nullable', 'exists:organisations,id'],
            'summary' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:5000'],
            'offerings' => ['nullable', 'string', 'max:3000'],
            'looking_for' => ['nullable', 'string', 'max:3000'],
            'website' => ['nullable', 'url', 'max:255'],
            'social_links' => ['nullable', 'array'],
            'social_links.linkedin' => ['nullable', 'url', 'max:255'],
            'social_links.instagram' => ['nullable', 'url', 'max:255'],
            'social_links.x' => ['nullable', 'url', 'max:255'],
            'social_links.facebook' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=4000,max_height=4000'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144', 'dimensions:max_width=8000,max_height=5000'],
        ];
    }
}
