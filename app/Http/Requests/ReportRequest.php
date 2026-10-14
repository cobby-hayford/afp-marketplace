<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return ['target_type' => ['required', Rule::in(['project', 'profile', 'stand', 'message'])], 'target_id' => ['required', 'integer', 'min:1'], 'reason_category' => ['required', Rule::in(['copyright', 'misrepresentation', 'inappropriate_content', 'spam', 'other'])], 'description' => ['required', 'string', 'max:5000']];
    }
}
