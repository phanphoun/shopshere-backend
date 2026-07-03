<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'order_number'    => $this->order_number,
            'user'            => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ]),
            'items'           => OrderItemResource::collection($this->whenLoaded('items')),
            'subtotal'        => (float) $this->subtotal,
            'tax'             => (float) $this->tax,
            'shipping_fee'    => (float) $this->shipping_fee,
            'discount'        => (float) $this->discount,
            'total'           => (float) $this->total,
            'status'          => $this->status,
            'payment_status'  => $this->payment_status,
            'payment_method'  => $this->payment_method,
            'shipping_address'=> $this->shipping_address,
            'phone'           => $this->phone,
            'notes'           => $this->notes,
            'aba_payway_txn_id' => $this->aba_payway_txn_id,
            'paid_at'      => $this->paid_at?->toISOString(),
            'shipped_at'      => $this->shipped_at?->toISOString(),
            'delivered_at'    => $this->delivered_at?->toISOString(),
            'items_count'     => (int) $this->items->sum('quantity'),
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
        ];
    }
}
