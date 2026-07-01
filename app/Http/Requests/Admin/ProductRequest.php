<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id'    => ['required', 'integer', 'exists:categories,id'],
            'name'           => ['required', 'string', 'min:2', 'max:200'],
            'slug'           => ['nullable', 'string', 'max:200', Rule::unique('products', 'slug')->ignore($productId)],
            'sku'            => ['nullable', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($productId)],
            'description'    => ['required', 'string', 'min:10'],
            'price'          => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99', 'lte:price'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'featured'       => ['nullable', 'boolean'],
            'status'         => ['nullable', 'boolean'],
            'image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery.*'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
