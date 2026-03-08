<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'min:2', 'max:80'],
            'currency' => ['required', 'in:NGN,USD,KES,GHS'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Wallet name is required.',
            'currency.required' => 'Please select a currency.',
            'currency.in'       => 'Currency must be one of: NGN, USD, KES, GHS.',
        ];
    }
}
