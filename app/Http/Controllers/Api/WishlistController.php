<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;
use App\Http\Resources\WishlistResource;
use App\Models\Product;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistService $wishlistService
    ) {}

    /**
     * GET /api/wishlist
     */
    #[OA\Get(
        path: '/api/wishlist',
        summary: 'Get user wishlist',
        description: "Returns all products in the authenticated user's wishlist.",
        tags: ['Wishlist'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Wishlist items',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Wishlist')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $items = $this->wishlistService->getForUser($request->user());

        return response()->json([
            'success' => true,
            'data'    => WishlistResource::collection($items),
        ]);
    }

    /**
     * POST /api/wishlist/{product}
     */
    #[OA\Post(
        path: '/api/wishlist/{product}',
        summary: 'Toggle wishlist item',
        description: 'Add a product to the wishlist if not present, or remove it if already present.',
        tags: ['Wishlist'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'product', in: 'path', required: true, description: 'Product ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Wishlist toggled',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Product added to wishlist'),
                        new OA\Property(property: 'data', properties: [
                            new OA\Property(property: 'is_wishlisted', type: 'boolean', example: true),
                        ], type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function toggle(Product $product, Request $request): JsonResponse
    {
        $result = $this->wishlistService->toggle($request->user(), $product);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data'    => $result,
        ]);
    }

    /**
     * DELETE /api/wishlist/{product}
     */
    #[OA\Delete(
        path: '/api/wishlist/{product}',
        summary: 'Remove from wishlist',
        description: 'Remove a specific product from the wishlist.',
        tags: ['Wishlist'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'product', in: 'path', required: true, description: 'Product ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Item removed from wishlist',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Product removed from wishlist'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Wishlist item not found'),
        ]
    )]
    public function destroy(Product $product, Request $request): JsonResponse
    {
        $this->wishlistService->remove($request->user(), $product);

        return response()->json([
            'success' => true,
            'message' => 'Removed from wishlist.',
        ]);
    }
}
