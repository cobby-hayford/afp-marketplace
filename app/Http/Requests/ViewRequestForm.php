<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ViewRequestForm extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return ['organisation_id' => ['nullable', 'exists:organisations,id'], 'position' => ['required', 'string', 'max:160'], 'interest_type' => ['required', Rule::in(['Acquisition', 'Distribution', 'Broadcast', 'Streaming', 'Co-production', 'Investment', 'Sales', 'Licensing', 'Festival', 'Other'])], 'territory' => ['nullable', 'string', 'max:255'], 'reason' => ['required', 'string', 'max:3000'], 'message' => ['nullable', 'string', 'max:3000']];
    }
}
