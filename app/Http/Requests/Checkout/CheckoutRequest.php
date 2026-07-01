<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shipping_address' => ['required', 'string', 'min:10', 'max:500'],
            'phone'            => ['required', 'string', 'min:6', 'max:32'],
            'payment_method'   => ['required', 'string', 'in:cod'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }
}
