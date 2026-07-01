<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;
use App\Http\Requests\Review\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewService $reviewService,
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * GET /api/products/{id}/reviews
     */
    #[OA\Get(
        path: '/api/products/{id}/reviews',
        summary: 'Get product reviews',
        description: 'Returns all reviews for a specific product.',
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Product ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of product reviews',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Review')),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Product not found'),
        ]
    )]
    public function index(int $id, Request $request): JsonResponse
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $perPage = (int) $request->input('per_page', 10);
        $reviews = $this->reviewService->listForProduct($id, $perPage);

        return response()->json([
            'success' => true,
            'data'    => ReviewResource::collection($reviews)->resolve(),
            'meta'    => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'total'        => $reviews->total(),
                'average_rating' => $product->average_rating,
            ],
        ]);
    }

    /**
     * POST /api/reviews
     */
    #[OA\Post(
        path: '/api/reviews',
        summary: 'Submit a product review',
        description: 'Submit or update a review for a product. Users can only submit one review per product.',
        tags: ['Reviews'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ReviewRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Review submitted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Review submitted successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Review'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(ReviewRequest $request): JsonResponse
    {
        $product = $this->productRepository->findById((int) $request->input('product_id'));
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $review = $this->reviewService->review(
            $request->user(),
            $product,
            (int) $request->input('rating'),
            $request->input('comment'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully.',
            'data'    => new ReviewResource($review->load('user')),
        ], 201);
    }
}
