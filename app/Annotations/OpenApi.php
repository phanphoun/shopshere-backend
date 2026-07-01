<?php

namespace App\Annotations;

use OpenApi\Attributes as OA;

/**
 * Main API definition for ShopSphere E-Commerce Platform.
 *
 * This file serves as the central OpenAPI specification for the
 * ShopSphere REST API. It defines the API metadata, security schemes,
 * reusable schemas, and endpoint groupings (tags).
 */
#[OA\Info(
    version: '1.0.0',
    title: 'ShopSphere API',
    description: 'ShopSphere is a production-ready e-commerce platform. This API provides endpoints for product browsing, shopping cart management, wishlist, order processing, and user authentication.',
    termsOfService: '',
    contact: new OA\Contact(
        email: ''
    ),
    license: new OA\License(
        name: 'MIT',
        url: 'https://opensource.org/licenses/MIT'
    )
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local Development Server'
)]
#[OA\Server(
    url: 'https://api.shopsphere.com',
    description: 'Production Server'
)]
#[OA\Tag(
    name: 'Authentication',
    description: 'Register, login, logout, and manage your account'
)]
#[OA\Tag(
    name: 'Categories',
    description: 'Browse product categories'
)]
#[OA\Tag(
    name: 'Products',
    description: 'Browse, search, and discover products'
)]
#[OA\Tag(
    name: 'Cart',
    description: 'Manage your shopping cart'
)]
#[OA\Tag(
    name: 'Wishlist',
    description: 'Manage your wishlist'
)]
#[OA\Tag(
    name: 'Orders',
    description: 'Place orders and view order history'
)]
#[OA\Tag(
    name: 'Reviews',
    description: 'Read and write product reviews'
)]
#[OA\Tag(
    name: 'Newsletter',
    description: 'Subscribe to the newsletter'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
    description: 'Enter your Sanctum token. Get it from POST /api/login or /api/register.'
)]
class OpenApi
{
    // This class serves only as a container for OpenAPI attributes.
    // No methods needed.
}

/**
 * Standard API Response wrapper.
 */
#[OA\Schema(
    schema: 'ApiResponse',
    description: 'Standard API response wrapper',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Operation completed successfully'),
        new OA\Property(property: 'data', description: 'Response payload', nullable: true),
    ],
    type: 'object'
)]
class ApiResponse
{
}

/**
 * Pagination metadata.
 */
#[OA\Schema(
    schema: 'PaginationMeta',
    description: 'Pagination metadata for paginated responses',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'last_page', type: 'integer', example: 10),
        new OA\Property(property: 'per_page', type: 'integer', example: 20),
        new OA\Property(property: 'total', type: 'integer', example: 200),
        new OA\Property(property: 'from', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'to', type: 'integer', example: 20, nullable: true),
    ],
    type: 'object'
)]
class PaginationMeta
{
}

/**
 * Category schema.
 */
#[OA\Schema(
    schema: 'Category',
    description: 'Product category',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
        new OA\Property(property: 'slug', type: 'string', example: 'electronics'),
        new OA\Property(property: 'description', type: 'string', example: 'All electronic products', nullable: true),
        new OA\Property(property: 'image_url', type: 'string', format: 'uri', example: 'http://localhost:8000/storage/categories/category.jpg', nullable: true),
        new OA\Property(property: 'products_count', type: 'integer', example: 25),
    ],
    type: 'object'
)]
class Category
{
}

/**
 * Product schema.
 */
#[OA\Schema(
    schema: 'Product',
    description: 'Product with details',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Wireless Bluetooth Headphones'),
        new OA\Property(property: 'slug', type: 'string', example: 'wireless-bluetooth-headphones'),
        new OA\Property(property: 'sku', type: 'string', example: 'SKU-001'),
        new OA\Property(property: 'description', type: 'string', example: 'High-quality wireless headphones with noise cancellation'),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 99.99),
        new OA\Property(property: 'discount_price', type: 'number', format: 'float', example: 79.99, nullable: true),
        new OA\Property(property: 'final_price', type: 'number', format: 'float', example: 79.99),
        new OA\Property(property: 'has_discount', type: 'boolean', example: true),
        new OA\Property(property: 'discount_percent', type: 'integer', example: 20),
        new OA\Property(property: 'category_id', type: 'integer', example: 1),
        new OA\Property(property: 'image_url', type: 'string', format: 'uri', example: 'http://localhost:8000/storage/products/product.jpg'),
        new OA\Property(property: 'images', type: 'array', items: new OA\Items(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'url', type: 'string', format: 'uri'),
            ],
            type: 'object'
        )),
        new OA\Property(property: 'stock_quantity', type: 'integer', example: 50),
        new OA\Property(property: 'in_stock', type: 'boolean', example: true),
        new OA\Property(property: 'featured', type: 'boolean', example: true),
        new OA\Property(property: 'status', type: 'boolean', example: true),
        new OA\Property(property: 'average_rating', type: 'number', format: 'float', example: 4.5, nullable: true),
        new OA\Property(property: 'reviews_count', type: 'integer', example: 12),
        new OA\Property(property: 'category', ref: '#/components/schemas/Category'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object'
)]
class Product
{
}

/**
 * Cart Item schema.
 */
#[OA\Schema(
    schema: 'CartItem',
    description: 'Item in a shopping cart',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'quantity', type: 'integer', example: 2),
        new OA\Property(property: 'unit_price', type: 'number', format: 'float', example: 99.99),
        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 199.98),
        new OA\Property(property: 'product', ref: '#/components/schemas/Product'),
    ],
    type: 'object'
)]
class CartItem
{
}

/**
 * Cart schema.
 */
#[OA\Schema(
    schema: 'Cart',
    description: 'Shopping cart with items and summary',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/CartItem')),
        new OA\Property(property: 'items_count', type: 'integer', example: 3),
        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 299.97),
    ],
    type: 'object'
)]
class Cart
{
}

/**
 * Order Item schema.
 */
#[OA\Schema(
    schema: 'OrderItem',
    description: 'Item in an order',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'product_name', type: 'string', example: 'Wireless Bluetooth Headphones'),
        new OA\Property(property: 'quantity', type: 'integer', example: 2),
        new OA\Property(property: 'unit_price', type: 'number', format: 'float', example: 99.99),
        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 199.98),
        new OA\Property(property: 'product', ref: '#/components/schemas/Product'),
    ],
    type: 'object'
)]
class OrderItem
{
}

/**
 * Order schema.
 */
#[OA\Schema(
    schema: 'Order',
    description: 'Customer order',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'order_number', type: 'string', example: 'ORD-20240601-001'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'processing', 'shipped', 'delivered', 'cancelled'], example: 'pending'),
        new OA\Property(property: 'payment_method', type: 'string', enum: ['cod'], example: 'cod'),
        new OA\Property(property: 'payment_status', type: 'string', enum: ['pending', 'paid', 'failed'], example: 'pending'),
        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 199.98),
        new OA\Property(property: 'tax', type: 'number', format: 'float', example: 20.00),
        new OA\Property(property: 'shipping_fee', type: 'number', format: 'float', example: 10.00),
        new OA\Property(property: 'total', type: 'number', format: 'float', example: 229.98),
        new OA\Property(property: 'shipping_name', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'shipping_phone', type: 'string', example: '+85512345678'),
        new OA\Property(property: 'shipping_address', type: 'string', example: '123 Street'),
        new OA\Property(property: 'shipping_city', type: 'string', example: 'Phnom Penh'),
        new OA\Property(property: 'shipping_state', type: 'string', example: 'Phnom Penh', nullable: true),
        new OA\Property(property: 'shipping_zip', type: 'string', example: '12000', nullable: true),
        new OA\Property(property: 'shipping_country', type: 'string', example: 'Cambodia'),
        new OA\Property(property: 'notes', type: 'string', example: 'Leave at the door', nullable: true),
        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/OrderItem')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object'
)]
class Order
{
}

/**
 * User schema.
 */
#[OA\Schema(
    schema: 'User',
    description: 'Registered user',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
        new OA\Property(property: 'phone', type: 'string', example: '+85512345678', nullable: true),
        new OA\Property(property: 'avatar_url', type: 'string', format: 'uri', example: 'http://localhost:8000/storage/avatars/avatar.jpg', nullable: true),
        new OA\Property(property: 'role', type: 'string', enum: ['customer', 'admin'], example: 'customer'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object'
)]
class User
{
}

/**
 * Review schema.
 */
#[OA\Schema(
    schema: 'Review',
    description: 'Product review',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'rating', type: 'integer', example: 5, minimum: 1, maximum: 5),
        new OA\Property(property: 'comment', type: 'string', example: 'Great product! Highly recommended.', nullable: true),
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object'
)]
class Review
{
}

/**
 * Wishlist schema.
 */
#[OA\Schema(
    schema: 'Wishlist',
    description: 'User wishlist entry',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'product', ref: '#/components/schemas/Product'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ],
    type: 'object'
)]
class Wishlist
{
}

/**
 * Login Request schema.
 */
#[OA\Schema(
    schema: 'LoginRequest',
    description: 'Login credentials',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'customer@shopsphere.test'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
    ],
    type: 'object'
)]
class LoginRequest
{
}

/**
 * Register Request schema.
 */
#[OA\Schema(
    schema: 'RegisterRequest',
    description: 'Registration data',
    required: ['name', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'phone', type: 'string', example: '+85512345678'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password'),
    ],
    type: 'object'
)]
class RegisterRequest
{
}

/**
 * Error Response schema.
 */
#[OA\Schema(
    schema: 'ErrorResponse',
    description: 'Error response',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Validation failed'),
        new OA\Property(property: 'errors', type: 'object', example: ['email' => ['The email field is required.']], nullable: true),
    ],
    type: 'object'
)]
class ErrorResponse
{
}

/**
 * Profile Update Request schema.
 */
#[OA\Schema(
    schema: 'UpdateProfileRequest',
    description: 'Profile update data',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'phone', type: 'string', example: '+85512345678'),
        new OA\Property(property: 'avatar', type: 'string', format: 'binary', description: 'Avatar image file (jpg, png, webp, max 2MB)'),
    ],
    type: 'object'
)]
class UpdateProfileRequest
{
}

/**
 * Change Password Request schema.
 */
#[OA\Schema(
    schema: 'ChangePasswordRequest',
    description: 'Password change data',
    required: ['current_password', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'current_password', type: 'string', format: 'password', example: 'old_password'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'new_password'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'new_password'),
    ],
    type: 'object'
)]
class ChangePasswordRequest
{
}

/**
 * Add to Cart Request schema.
 */
#[OA\Schema(
    schema: 'AddCartItemRequest',
    description: 'Add item to cart',
    required: ['product_id', 'quantity'],
    properties: [
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'quantity', type: 'integer', example: 2, minimum: 1),
    ],
    type: 'object'
)]
class AddCartItemRequest
{
}

/**
 * Update Cart Item Request schema.
 */
#[OA\Schema(
    schema: 'UpdateCartItemRequest',
    description: 'Update cart item quantity',
    required: ['product_id', 'quantity'],
    properties: [
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'quantity', type: 'integer', example: 3, minimum: 1),
    ],
    type: 'object'
)]
class UpdateCartItemRequest
{
}

/**
 * Checkout Request schema.
 */
#[OA\Schema(
    schema: 'CheckoutRequest',
    description: 'Checkout / place order data',
    required: ['shipping_name', 'shipping_phone', 'shipping_address', 'shipping_city', 'shipping_country', 'payment_method'],
    properties: [
        new OA\Property(property: 'shipping_name', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'shipping_phone', type: 'string', example: '+85512345678'),
        new OA\Property(property: 'shipping_address', type: 'string', example: '123 Main Street'),
        new OA\Property(property: 'shipping_city', type: 'string', example: 'Phnom Penh'),
        new OA\Property(property: 'shipping_state', type: 'string', example: 'Phnom Penh'),
        new OA\Property(property: 'shipping_zip', type: 'string', example: '12000'),
        new OA\Property(property: 'shipping_country', type: 'string', example: 'Cambodia'),
        new OA\Property(property: 'payment_method', type: 'string', enum: ['cod'], example: 'cod'),
        new OA\Property(property: 'notes', type: 'string', example: 'Leave at the door'),
    ],
    type: 'object'
)]
class CheckoutRequest
{
}

/**
 * Review Request schema.
 */
#[OA\Schema(
    schema: 'ReviewRequest',
    description: 'Review submission data',
    required: ['product_id', 'rating'],
    properties: [
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'rating', type: 'integer', example: 5, minimum: 1, maximum: 5),
        new OA\Property(property: 'comment', type: 'string', example: 'Excellent quality!'),
    ],
    type: 'object'
)]
class ReviewRequest
{
}

/**
 * Newsletter Subscribe Request schema.
 */
#[OA\Schema(
    schema: 'NewsletterRequest',
    description: 'Newsletter subscription data',
    required: ['email'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
    ],
    type: 'object'
)]
class NewsletterRequest
{
}

/**
 * Common response components.
 */
#[OA\Response(
    response: 'ValidationError',
    description: 'Validation error response',
    content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
)]
class ValidationErrorResponse
{
}
