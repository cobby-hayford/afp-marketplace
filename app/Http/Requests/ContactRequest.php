<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:160'], 'email' => ['required', 'email', 'max:255'], 'organisation' => ['nullable', 'string', 'max:180'], 'subject' => ['required', 'string', 'max:200'], 'message' => ['required', 'string', 'max:5000']];
    }
}
