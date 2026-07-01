<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'product_id' => $this->product_id,
            'rating'     => $this->rating,
            'stars'      => $this->stars,
            'comment'    => $this->comment,
            'approved'   => (bool) $this->approved,
            'user'       => $this->whenLoaded('user', fn () => [
                'id'     => $this->user->id,
                'name'   => $this->user->name,
                'avatar' => $this->user->avatar_url,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
