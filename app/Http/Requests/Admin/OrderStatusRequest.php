<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class OrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:'
                . Order::STATUS_PENDING . ','
                . Order::STATUS_PROCESSING . ','
                . Order::STATUS_SHIPPED . ','
                . Order::STATUS_DELIVERED . ','
                . Order::STATUS_CANCELLED,
            ],
        ];
    }
}
