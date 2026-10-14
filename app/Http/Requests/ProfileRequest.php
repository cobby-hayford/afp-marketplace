<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'professional_name' => ['nullable', 'string', 'max:160'],
            'country_id' => ['required', 'exists:countries,id'],
            'city' => ['nullable', 'string', 'max:120'],
            'short_bio' => ['required', 'string', 'max:500'],
            'biography' => ['required', 'string', 'max:5000'],
            'website' => ['nullable', 'url', 'max:255'],
            'association_number' => ['nullable', 'string', 'max:120'],
            'awards' => ['nullable', 'string', 'max:5000'],
            'social_links' => ['nullable', 'array'],
            'social_links.linkedin' => ['nullable', 'url', 'max:255'],
            'social_links.instagram' => ['nullable', 'url', 'max:255'],
            'social_links.x' => ['nullable', 'url', 'max:255'],
            'social_links.facebook' => ['nullable', 'url', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'contact_preference' => ['required', Rule::in(['platform', 'email', 'phone'])],
            'show_email' => ['boolean'],
            'show_phone' => ['boolean'],
            'roles' => ['array', 'max:10'],
            'roles.*' => ['string', 'max:80'],
            'genres' => ['array'],
            'genres.*' => ['integer', 'exists:genres,id'],
            'languages' => ['array'],
            'languages.*' => ['integer', 'exists:languages,id'],
            'content_types' => ['array'],
            'content_types.*' => ['integer', 'exists:content_types,id'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=4000,max_height=4000'],
        ];
    }
}
