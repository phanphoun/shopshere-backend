<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected CategoryRepositoryInterface $categoryRepository,
        protected ProductService $productService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search'      => $request->input('search'),
            'category_id' => $request->input('category_id'),
            'status'      => $request->has('status') ? filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
            'featured'    => $request->has('featured') ? filter_var($request->input('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
            'sort'        => $request->input('sort', 'latest'),
        ];

        $products = $this->productRepository->paginate(15, array_filter($filters, fn ($v) => $v !== null && $v !== ''));
        $categories = $this->categoryRepository->getActive();

        return view('admin.products.index', compact('products', 'categories', 'filters'));
    }

    public function create(): View
    {
        $categories = $this->categoryRepository->getActive();
        return view('admin.products.create', compact('categories'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->productService->create(
            $request->validated(),
            $request->file('image'),
            $request->file('gallery', []) ?? []
        );

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $product->load('images');
        $categories = $this->categoryRepository->getActive();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->update(
            $product,
            $request->validated(),
            $request->file('image'),
            $request->file('gallery', []) ?? []
        );

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->delete($product);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function destroyImage(ProductImage $image): RedirectResponse
    {
        $this->productService->deleteImage($image);

        return back()->with('success', 'Image deleted.');
    }
}
