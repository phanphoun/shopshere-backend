<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'product_id' => $this->product_id,
            'quantity'   => $this->quantity,
            'subtotal'   => (float) $this->subtotal,
            'product'    => $this->whenLoaded('product', fn () => $this->product ? new ProductResource($this->product) : null),
        ];
    }
}
