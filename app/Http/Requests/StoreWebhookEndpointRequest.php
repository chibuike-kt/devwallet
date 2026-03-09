<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebhookEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url'         => ['required', 'url', 'max:500'],
            'description' => ['nullable', 'string', 'max:200'],
            'events'      => ['nullable', 'array'],
            'events.*'    => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'A valid endpoint URL is required.',
            'url.url'      => 'Please enter a valid URL including http:// or https://',
        ];
    }
}
