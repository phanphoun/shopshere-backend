<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'slug'                => $this->slug,
            'sku'                 => $this->sku,
            'description'         => $this->description,
            'price'               => (float) $this->price,
            'discount_price'      => $this->discount_price ? (float) $this->discount_price : null,
            'category_id'         => $this->category_id,
            'stock_quantity'      => $this->stock_quantity,
            'featured'            => (bool) $this->featured,
            'status'              => (bool) $this->status,
            'image_url'           => $this->image_url,
            'images'              => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id'  => $img->id,
                'url' => $img->url,
            ])),
            'category'            => new CategoryResource($this->whenLoaded('category')),
            'final_price'         => $this->final_price,
            'has_discount'        => $this->has_discount,
            'discount_percent'    => $this->discount_percent,
            'in_stock'            => $this->in_stock,
            'average_rating' => $this->when(
                $this->relationLoaded('approvedReviewsAvgAggregate') || $this->relationLoaded('approvedReviews'),
                fn () => $this->average_rating
            ),
            'reviews_count' => $this->when(
                $this->relationLoaded('approvedReviews') || array_key_exists('approved_reviews_count', $this->resource->getAttributes()),
                fn () => $this->reviews_count
            ),
            'total_sold' => $this->when(
                $this->relationLoaded('orderItems') || array_key_exists('total_sold', $this->resource->getAttributes()) || array_key_exists('order_items_sum_quantity', $this->resource->getAttributes()),
                fn () => $this->total_sold
            ),
            'created_at'          => $this->created_at?->toISOString(),
            'updated_at'          => $this->updated_at?->toISOString(),
        ];
    }
}
