<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;
use App\Http\Resources\CategoryResource;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * GET /api/categories
     */
    #[OA\Get(
        path: '/api/categories',
        summary: 'List all categories',
        description: 'Returns a list of all active product categories.',
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of categories',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Categories retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Category')),
                    ]
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        $categories = $this->categoryRepository->getActive();

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully.',
            'data'    => CategoryResource::collection($categories),
        ]);
    }

    /**
     * GET /api/categories/{slug}
     */
    #[OA\Get(
        path: '/api/categories/{slug}',
        summary: 'Get category by slug',
        description: 'Returns a single category by its URL slug.',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'electronics'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Category'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function show(string $slug): JsonResponse
    {
        $category = $this->categoryRepository->findBySlug($slug);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new CategoryResource($category),
        ]);
    }
}
