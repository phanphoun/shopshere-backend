<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['throttle:api', 'locale'])->group(function () {
    Route::get('social/auth/redirect/{driver}', [SocialAuthController::class, 'redirect'])->whereIn('driver', ['google']);
    Route::get('social/auth/callback/{driver}', [SocialAuthController::class, 'callback'])->whereIn('driver', ['google']);
    Route::redirect('docs', 'documentation', 301);

    // Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{slug}', [CategoryController::class, 'show']);

    // Products
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/featured', [ProductController::class, 'featured']);
    Route::get('products/latest', [ProductController::class, 'latest']);
    Route::get('products/best-sellers', [ProductController::class, 'bestSellers']);
    Route::get('products/search', [ProductController::class, 'search']);
    Route::get('products/category/{slug}', [ProductController::class, 'byCategory']);
    Route::get('products/{id}', [ProductController::class, 'show']);

    // Reviews
    Route::get('products/{id}/reviews', [ReviewController::class, 'index']);

    // Newsletter
    Route::post('newsletter/subscribe', [NewsletterController::class, 'subscribe']);

    // Site Stats
    Route::get('stats', function () {
        return response()->json([
            'success' => true,
            'data'    => [
                'total_products'  => \App\Models\Product::count(),
                'total_customers' => \App\Models\User::where('role', \App\Models\User::ROLE_CUSTOMER)->count(),
                'total_categories' => \App\Models\Category::count(),
            ],
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('throttle:auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Telegram Bot Webhook (public - called by Telegram)
|--------------------------------------------------------------------------
*/

Route::post('telegram/webhook', [App\Http\Controllers\Api\TelegramController::class, 'webhook'])
    ->middleware('throttle:api');

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::match(['PUT', 'POST'], 'profile', [AuthController::class, 'updateProfile']);
    Route::put('change-password', [AuthController::class, 'changePassword']);

    // Cart
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart/add', [CartController::class, 'add']);
    Route::put('cart/update', [CartController::class, 'update']);
    Route::delete('cart/remove/{id}', [CartController::class, 'remove']);
    Route::delete('cart/clear', [CartController::class, 'clear']);

    // Wishlist
    Route::get('wishlist', [WishlistController::class, 'index']);
    Route::post('wishlist/{product}', [WishlistController::class, 'toggle']);
    Route::delete('wishlist/{product}', [WishlistController::class, 'destroy']);

    // Checkout & Orders
    Route::post('checkout', [CheckoutController::class, 'place']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'show']);

    // Reviews
    Route::post('reviews', [ReviewController::class, 'store']);

    // Telegram
    Route::get('telegram/token', [App\Http\Controllers\Api\TelegramController::class, 'generateToken']);
    Route::post('telegram/connect', [App\Http\Controllers\Api\TelegramController::class, 'connect']);
    Route::delete('telegram/disconnect', [App\Http\Controllers\Api\TelegramController::class, 'disconnect']);
    Route::get('telegram/status', [App\Http\Controllers\Api\TelegramController::class, 'status']);

    // Support
    Route::post('support/start', [App\Http\Controllers\Api\SupportController::class, 'start']);
    Route::get('support/messages/{id}', [App\Http\Controllers\Api\SupportController::class, 'messages']);
    Route::post('support/send', [App\Http\Controllers\Api\SupportController::class, 'send']);
    Route::get('support/conversations', [App\Http\Controllers\Api\SupportController::class, 'list']);
});
