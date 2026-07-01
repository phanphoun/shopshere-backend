<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * GET /api/cart
     */
    #[OA\Get(
        path: '/api/cart',
        summary: 'Get current cart',
        description: "Returns the authenticated user's shopping cart with items and totals.",
        tags: ['Cart'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Current cart',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Cart retrieved successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Cart'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $data = $this->cartService->getCart($request->user());

        return response()->json([
            'success' => true,
            'data'    => [
                'cart'    => new CartResource($data['cart']),
                'summary' => $data['summary'],
            ],
        ]);
    }

    /**
     * POST /api/cart/add
     */
    #[OA\Post(
        path: '/api/cart/add',
        summary: 'Add item to cart',
        description: 'Add a product to the shopping cart or increase its quantity if already present.',
        tags: ['Cart'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AddCartItemRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Item added to cart',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Item added to cart'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function add(AddCartItemRequest $request): JsonResponse
    {
        $data = $this->cartService->addItem(
            $request->user(),
            (int) $request->input('product_id'),
            (int) $request->input('quantity'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart.',
            'data'    => [
                'cart'    => new CartResource($data['cart']),
                'summary' => $data['summary'],
            ],
        ]);
    }

    /**
     * PUT /api/cart/update
     */
    #[OA\Put(
        path: '/api/cart/update',
        summary: 'Update cart item quantity',
        description: 'Update the quantity of a product in the cart.',
        tags: ['Cart'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateCartItemRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Cart updated successfully'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateCartItemRequest $request): JsonResponse
    {
        $data = $this->cartService->updateItem(
            $request->user(),
            (int) $request->input('product_id'),
            (int) $request->input('quantity'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart updated.',
            'data'    => [
                'cart'    => new CartResource($data['cart']),
                'summary' => $data['summary'],
            ],
        ]);
    }

    /**
     * DELETE /api/cart/remove/{id}
     */
    #[OA\Delete(
        path: '/api/cart/remove/{id}',
        summary: 'Remove item from cart',
        description: 'Remove a specific item from the shopping cart.',
        tags: ['Cart'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Cart item ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Item removed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Item removed from cart'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Cart item not found'),
        ]
    )]
    public function remove(int $id, Request $request): JsonResponse
    {
        $data = $this->cartService->removeItem($request->user(), $id);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
            'data'    => [
                'cart'    => new CartResource($data['cart']),
                'summary' => $data['summary'],
            ],
        ]);
    }

    /**
     * DELETE /api/cart/clear
     */
    #[OA\Delete(
        path: '/api/cart/clear',
        summary: 'Clear cart',
        description: 'Remove all items from the shopping cart.',
        tags: ['Cart'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart cleared',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Cart cleared successfully'),
                    ]
                )
            ),
        ]
    )]
    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clear($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared.',
        ]);
    }
}
