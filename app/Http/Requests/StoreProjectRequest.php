<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth middleware handles access; any authed user can create
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'min:2', 'max:80'],
            'description' => ['nullable', 'string', 'max:300'],
            'environment' => ['required', 'in:test,staging'],
            'color'       => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Project name is required.',
            'name.min'         => 'Project name must be at least 2 characters.',
            'environment.in'   => 'Environment must be either test or staging.',
        ];
    }
}
