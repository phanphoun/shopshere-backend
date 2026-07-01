<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;
use App\Http\Requests\Product\ProductFilterRequest;
use App\Http\Resources\ProductResource;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * GET /api/products
     */
    #[OA\Get(
        path: '/api/products',
        summary: 'List products with filters',
        description: 'Returns a paginated list of products. Supports filtering, sorting, and searching.',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', description: 'Search by product name', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'category_id', in: 'query', description: 'Filter by category ID', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'min_price', in: 'query', description: 'Minimum price filter', schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'max_price', in: 'query', description: 'Maximum price filter', schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'featured', in: 'query', description: 'Filter featured products (1 or 0)', schema: new OA\Schema(type: 'integer', enum: [0, 1])),
            new OA\Parameter(name: 'in_stock', in: 'query', description: 'Filter in-stock products (1 or 0)', schema: new OA\Schema(type: 'integer', enum: [0, 1])),
            new OA\Parameter(name: 'sort', in: 'query', description: 'Sort order', schema: new OA\Schema(type: 'string', enum: ['latest', 'oldest', 'price_asc', 'price_desc', 'name_asc', 'name_desc'])),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Items per page', schema: new OA\Schema(type: 'integer', default: 20)),
            new OA\Parameter(name: 'page', in: 'query', description: 'Page number', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of products',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Products retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                    ]
                )
            ),
        ]
    )]
    public function index(ProductFilterRequest $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', config('shopsphere.pagination.per_page', 15));
        $filters = $request->validated();

        $products = $this->productRepository->paginate($perPage, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data'    => ProductResource::collection($products)->resolve(),
            'meta'    => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    /**
     * GET /api/products/{id}
     */
    #[OA\Get(
        path: '/api/products/{id}',
        summary: 'Get product details',
        description: 'Returns detailed product information including gallery images, category, and rating.',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Product ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Product retrieved successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Product'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * GET /api/products/featured
     */
    #[OA\Get(
        path: '/api/products/featured',
        summary: 'Get featured products',
        description: 'Returns a list of featured products.',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', description: 'Number of products to return', schema: new OA\Schema(type: 'integer', default: 8)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of featured products',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
                    ]
                )
            ),
        ]
    )]
    public function featured(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 10), 50);
        $products = $this->productRepository->getFeatured($limit);

        return response()->json([
            'success' => true,
            'data'    => ProductResource::collection($products)->resolve(),
        ]);
    }

    /**
     * GET /api/products/latest
     */
    #[OA\Get(
        path: '/api/products/latest',
        summary: 'Get latest products',
        description: 'Returns the most recently added products.',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', description: 'Number of products to return', schema: new OA\Schema(type: 'integer', default: 8)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of latest products',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
                    ]
                )
            ),
        ]
    )]
    public function latest(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 10), 50);
        $products = $this->productRepository->getLatest($limit);

        return response()->json([
            'success' => true,
            'data'    => ProductResource::collection($products)->resolve(),
        ]);
    }

    /**
     * GET /api/products/best-sellers
     */
    #[OA\Get(
        path: '/api/products/best-sellers',
        summary: 'Get best-selling products',
        description: 'Returns the top-selling products based on order history.',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', description: 'Number of products to return', schema: new OA\Schema(type: 'integer', default: 8)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of best-selling products',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
                    ]
                )
            ),
        ]
    )]
    public function bestSellers(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 10), 50);
        $products = $this->productRepository->getBestSellers($limit);

        return response()->json([
            'success' => true,
            'data'    => ProductResource::collection($products)->resolve(),
        ]);
    }

    /**
     * GET /api/products/search?q=...
     */
    #[OA\Get(
        path: '/api/products/search',
        summary: 'Search products',
        description: 'Search for products by name, description, or other attributes.',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: true, description: 'Search query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Items per page', schema: new OA\Schema(type: 'integer', default: 20)),
            new OA\Parameter(name: 'page', in: 'query', description: 'Page number', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated search results',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Search results.'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                    ]
                )
            ),
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:1|max:200']);

        $products = $this->productRepository->paginate(
            config('shopsphere.pagination.per_page', 15),
            ['search' => $request->input('q')]
        );

        return response()->json([
            'success' => true,
            'message' => 'Search results.',
            'data'    => ProductResource::collection($products)->resolve(),
            'meta'    => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'total'        => $products->total(),
                'query'        => $request->input('q'),
            ],
        ]);
    }

    /**
     * GET /api/products/category/{slug}
     */
    #[OA\Get(
        path: '/api/products/category/{slug}',
        summary: 'Get products by category',
        description: 'Returns products belonging to a specific category.',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Category slug', schema: new OA\Schema(type: 'string'), example: 'electronics'),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Items per page', schema: new OA\Schema(type: 'integer', default: 20)),
            new OA\Parameter(name: 'page', in: 'query', description: 'Page number', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated products in category',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function byCategory(string $slug, Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', config('shopsphere.pagination.per_page', 15));
        $filters = array_filter($request->only(['search', 'min_price', 'max_price', 'sort', 'featured', 'in_stock', 'page']));

        try {
            $products = $this->productRepository->getByCategory($slug, $perPage, $filters);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => ProductResource::collection($products)->resolve(),
            'meta'    => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'total'        => $products->total(),
            ],
        ]);
    }
}
