<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    #[OA\Post(
        path: '/api/newsletter/subscribe',
        summary: 'Subscribe to newsletter',
        description: 'Subscribe an email address to the ShopSphere newsletter.',
        tags: ['Newsletter'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/NewsletterRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Subscribed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Subscribed to newsletter successfully'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $exists = NewsletterSubscriber::where('email', $data['email'])->exists();

        if (!$exists) {
            NewsletterSubscriber::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => $exists ? 'Already subscribed!' : 'Subscribed successfully!',
        ]);
    }
}
