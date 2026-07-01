<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'items'      => CartItemResource::collection($this->whenLoaded('items')),
            'items_count'=> $this->whenLoaded('items', fn() => (int) $this->items->sum('quantity'), 0),
            'subtotal'   => $this->whenLoaded('items', fn() => (float) $this->subtotal, 0),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
