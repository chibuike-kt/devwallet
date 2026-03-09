<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunScenarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scenario'          => ['required', 'string'],
            'wallet_id'         => ['nullable', 'exists:wallets,id'],
            'target_wallet_id'  => ['nullable', 'exists:wallets,id'],
            'amount'            => ['nullable', 'numeric', 'min:1', 'max:100000000'],
            'narration'         => ['nullable', 'string', 'max:200'],
            'transaction_id'    => ['nullable', 'exists:transactions,id'],
        ];
    }

    /**
     * Convert the decimal amount input to minor units (kobo/cents).
     * User types 5000 (naira) → we store 500000 (kobo).
     */
    public function amountInMinorUnits(): int
    {
        return (int) round($this->amount * 100);
    }
}
