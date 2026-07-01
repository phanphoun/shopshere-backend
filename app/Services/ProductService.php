<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Create a product, persist image and gallery.
     */
    public function create(array $data, ?UploadedFile $mainImage = null, array $galleryImages = []): Product
    {
        return DB::transaction(function () use ($data, $mainImage, $galleryImages) {
            $productData = collect($data)->except('gallery')->all();

            if ($mainImage) {
                $productData['image'] = $this->storeImage($mainImage, 'products');
            }

            $product = $this->productRepository->create($productData);

            foreach ($galleryImages as $i => $image) {
                $path = $this->storeImage($image, 'products/gallery');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $path,
                    'sort_order' => $i,
                ]);
            }

            return $product->fresh(['category', 'images']);
        });
    }

    /**
     * Update an existing product, optionally replacing main image.
     */
    public function update(Product $product, array $data, ?UploadedFile $mainImage = null, array $galleryImages = []): Product
    {
        return DB::transaction(function () use ($product, $data, $mainImage, $galleryImages) {
            $productData = collect($data)->except('gallery')->all();

            if ($mainImage) {
                $this->deleteImageFile($product->image);
                $productData['image'] = $this->storeImage($mainImage, 'products');
            }

            $product = $this->productRepository->update($product, $productData);

            foreach ($galleryImages as $i => $image) {
                $path = $this->storeImage($image, 'products/gallery');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $path,
                    'sort_order' => $product->images()->count() + $i,
                ]);
            }

            return $product->fresh(['category', 'images']);
        });
    }

    /**
     * Delete a product and its files.
     */
    public function delete(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            $this->deleteImageFile($product->image);
            foreach ($product->images as $img) {
                $this->deleteImageFile($img->image);
            }
            return $this->productRepository->delete($product);
        });
    }

    /**
     * Delete a single product image.
     */
    public function deleteImage(ProductImage $image): bool
    {
        $this->deleteImageFile($image->image);
        return (bool) $image->delete();
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                             */
    /* ------------------------------------------------------------------ */

    protected function storeImage(UploadedFile $file, string $folder): string
    {
        return $file->store($folder, 'public');
    }

    protected function deleteImageFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
