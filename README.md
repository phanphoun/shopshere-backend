# 🛍️ ShopSphere — Production-ready E-Commerce Platform

> A full-featured, production-ready e-commerce backend built with **Laravel 12**, featuring a RESTful API with Sanctum authentication, an admin panel with Blade templates, repository pattern, and comprehensive test coverage.

[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## ✨ Features

### 🛒 Customer-facing API
- **Product Catalog** — Browsing, search, filtering by price/category/featured, with pagination and sorting
- **Shopping Cart** — Add/update/remove items, automatic inventory validation, tax & shipping calculation
- **Wishlist** — Toggle products on/off a personal wishlist
- **Order Management** — Checkout with stock validation, order history with detailed breakdown
- **Reviews & Ratings** — Star ratings with comments (one review per product per user)
- **Newsletter** — Email subscription
- **Multi-locale** — Supports English (`en`) and Khmer (`km`) via `X-App-Locale` header

### 🔐 Authentication
- **Sanctum Token-based API Auth** — Register, login, logout with token-based authentication
- **Role-based Access Control** — Admin (`admin`) and Customer (`customer`) roles with policy enforcement
- **Profile Management** — Update profile, upload avatar, change password

### 🧑‍💼 Admin Panel
- **Dashboard** — Revenue, order counts, monthly sales charts, top-selling products, recent orders
- **Product CRUD** — Full product management with main image & gallery images, auto-generated slugs & SKUs
- **Category Management** — CRUD with image upload, product count tracking
- **Order Management** — View/update order statuses (pending → processing → shipped → delivered), generate invoices
- **User Management** — View/edit/delete users with role & status filtering
- **Self-protection** — Cannot delete your own admin account

### 🏗️ Architecture
- **Repository Pattern** — Clean separation of data access through interfaces and Eloquent implementations
- **Service Layer** — Business logic encapsulated in dedicated service classes
- **Policy-based Authorization** — Granular access control for models
- **API Resources** — Consistent JSON responses via Laravel API resources
- **L5-Swagger** — Auto-generated OpenAPI documentation at `/documentation`

---

## 🧰 Tech Stack

| Layer | Technology |
|---|---|
| **Framework** | Laravel 12 |
| **Language** | PHP 8.2 |
| **API Auth** | Laravel Sanctum |
| **Database** | MySQL / SQLite |
| **Cache / Queue** | Redis / Database |
| **Frontend (Admin)** | Blade + Bootstrap 5 |
| **API Docs** | L5-Swagger (OpenAPI 3.0) |
| **Testing** | PHPUnit 11 |
| **Code Style** | Laravel Pint |
| **Session** | Database-driven |

---

## 📁 Project Structure

```
app/
├── Annotations/             # OpenAPI/Swagger shared schemas
├── Console/Commands/        # Artisan commands
├── Http/
│   ├── Controllers/
│   │   ├── Admin/           # Admin panel controllers (Blade views)
│   │   └── Api/             # REST API controllers (JSON responses)
│   ├── Middleware/          # Role checks, locale, etc.
│   ├── Requests/            # Form request validation
│   └── Resources/           # API resource transformers
├── Models/                  # Eloquent models
├── Policies/                # Authorization policies
├── Providers/               # Service providers
├── Repositories/
│   ├── Contracts/           # Repository interfaces
│   └── Eloquent/            # Eloquent implementations
└── Services/                # Business logic layer

config/
├── shopsphere.php           # App-specific settings (tax, shipping, etc.)
├── l5-swagger.php           # Swagger documentation config
└── ...                      # Laravel standard config files

database/
├── migrations/              # Database migrations (14 tables)
├── factories/               # Model factories for testing
└── seeders/                 # Database seeders (demo data)

routes/
├── api.php                  # API routes
├── web.php                  # Admin panel routes
└── console.php              # Artisan console routes

tests/
└── Feature/
    ├── Api/                 # API feature tests
    └── Admin/               # Admin feature tests

resources/views/
└── admin/                   # Admin panel Blade templates
```

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer 2.x
- MySQL 8+ (or SQLite for development)
- Node.js & NPM (for building assets, optional)
- Redis (optional, for cache/queue)

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/your-org/shopsphere.git
cd shopsphere/backend

# 2. Install PHP dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Configure your database in .env
#    See Configuration section below

# 6. Run migrations and seeders
php artisan migrate --seed

# 7. (Optional) Create symlink for public storage
php artisan storage:link

# 8. Start the development server
php artisan serve
```

Your application will be available at `http://localhost:8000`.

- **Admin Panel:** `http://localhost:8000/admin`
- **API Documentation:** `http://localhost:8000/documentation`

### Default Admin Credentials

After seeding, you can log in with:

| Email | Password | Role |
|---|---|---|
| `admin@shopsphere.test` | `password` | Admin |
| *(30 customer accounts)* | *(auto-generated)* | Customer |

---

## ⚙️ Configuration

Key environment variables in `.env`:

```env
# Application
APP_NAME=ShopSphere
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_LOCALE=en
APP_KEY=

# Database (MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopsphere
DB_USERNAME=root
DB_PASSWORD=

# Or use SQLite (no server needed)
# DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

# Authentication
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173

# Frontend (for CORS)
FRONTEND_URL=http://localhost:3000

# ShopSphere Settings
TAX_RATE=10              # Percentage
SHIPPING_FEE=5.00        # Flat rate
CURRENCY=USD
CURRENCY_SYMBOL=$
```

### Application-specific Config (`config/shopsphere.php`)

| Key | Default | Description |
|---|---|---|
| `tax_rate` | `10` | Tax percentage applied to subtotal |
| `shipping_fee` | `5.00` | Flat shipping fee |
| `currency` | `USD` | Currency code |
| `currency_symbol` | `$` | Currency symbol |
| `frontend_url` | — | Frontend URL for CORS |
| `pagination.per_page` | `15` | Default items per page |
| `pagination.max_per_page` | `100` | Maximum items per page |

---

## 🌐 API Endpoints

Full interactive documentation is available at `/documentation` (using Swagger UI). Below is a summary:

### Public Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/categories` | List all active categories |
| `GET` | `/api/categories/{slug}` | Get category by slug |
| `GET` | `/api/products` | List products (with filters) |
| `GET` | `/api/products/featured` | Featured products |
| `GET` | `/api/products/latest` | Latest products |
| `GET` | `/api/products/best-sellers` | Best-selling products |
| `GET` | `/api/products/search?q=` | Search products |
| `GET` | `/api/products/category/{slug}` | Products by category |
| `GET` | `/api/products/{id}` | Product details |
| `GET` | `/api/products/{id}/reviews` | Product reviews |
| `POST` | `/api/newsletter/subscribe` | Subscribe to newsletter |
| `GET` | `/api/stats` | Site statistics |

### Authentication Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/register` | Create account (returns token) |
| `POST` | `/api/login` | Login (returns token) |

### Authenticated Endpoints (requires `Authorization: Bearer <token>`)

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/logout` | Revoke current token |
| `GET` | `/api/profile` | Get user profile |
| `PUT` | `/api/profile` | Update profile (multipart) |
| `PUT` | `/api/change-password` | Change password |
| `GET` | `/api/cart` | Get shopping cart |
| `POST` | `/api/cart/add` | Add item to cart |
| `PUT` | `/api/cart/update` | Update cart item quantity |
| `DELETE` | `/api/cart/remove/{id}` | Remove cart item |
| `DELETE` | `/api/cart/clear` | Clear cart |
| `GET` | `/api/wishlist` | Get wishlist |
| `POST` | `/api/wishlist/{product}` | Toggle wishlist item |
| `DELETE` | `/api/wishlist/{product}` | Remove from wishlist |
| `POST` | `/api/checkout` | Place order |
| `GET` | `/api/orders` | Order history |
| `GET` | `/api/orders/{id}` | Order details |
| `POST` | `/api/reviews` | Submit a review |

### Query Parameters for Products

| Parameter | Type | Description |
|---|---|---|
| `search` | string | Search by name/description/SKU |
| `category_id` | integer | Filter by category |
| `min_price` | float | Minimum price |
| `max_price` | float | Maximum price |
| `featured` | bool (0/1) | Filter featured products |
| `in_stock` | bool (0/1) | Filter in-stock products |
| `sort` | string | `latest` (default), `oldest`, `price_asc`, `price_desc`, `name_asc`, `name_desc` |
| `per_page` | integer | Items per page (max 100) |
| `page` | integer | Page number |

### Locale Support

Set the `X-App-Locale` header to switch language:

```bash
curl -H "X-App-Locale: km" http://localhost:8000/api/categories
```

---

## 🗄️ Database Schema

The application uses 14 tables:

| Table | Purpose |
|---|---|
| `users` | Customers & admins (roles: `admin`, `customer`) |
| `categories` | Product categories (soft-deletes) |
| `products` | Product catalog (soft-deletes) |
| `product_images` | Product gallery images |
| `wishlists` | User wishlist items |
| `carts` | User shopping carts (1:1 with users) |
| `cart_items` | Cart line items |
| `orders` | Customer orders with status tracking |
| `order_items` | Order line items (snapshot of product data) |
| `reviews` | Product reviews & ratings |
| `newsletter_subscribers` | Email newsletter subscriptions |
| `sessions` | Session storage |
| `personal_access_tokens` | Sanctum API tokens |
| `password_reset_tokens` | Password reset tokens |

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature

# Run tests with coverage (requires Xdebug/PCOV)
php artisan test --coverage
```

The test suite covers:
- **Auth** — Registration, login, logout, multi-device token handling
- **Products** — Listing, filtering, searching, detail view, featured, 404 handling, limit capping
- **Cart** — Add, update, remove, clear, stock validation
- **Checkout** — Full order placement flow, stock decrement, cart cleanup
- **Order History** — User order listing, order details, authorization
- **Wishlist** — Add, toggle, remove items
- **Profile** — View, update profile, change password
- **Admin** — Product admin CRUD operations

---

## 🎨 Admin Panel

The admin panel is built with Laravel Blade + Bootstrap 5 and is accessible at `/admin`.

**Features:**
- **Dashboard** — KPIs with monthly sales chart and top products
- **Products** — CRUD with image management gallery, stock tracking
- **Categories** — CRUD with image upload, protected from deletion when products exist
- **Orders** — Status workflow management, invoice PDF view
- **Users** — View/edit profiles, role/status management, delete protection

---

## 🔧 Development Commands

```bash
# List all available Artisan commands
php artisan list

# Create database tables (fresh)
php artisan migrate:fresh

# Seed database with demo data
php artisan db:seed

# Fresh migrate and seed (one command)
php artisan migrate:fresh --seed

# Generate Swagger documentation
php artisan l5-swagger:generate

# Run code style fixer
./vendor/bin/pint

# Trim products to 10 (custom command)
php artisan shopsphere:trim-products
```

---

## 🤝 Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

---

## 📄 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

---

## 🏗️ Architecture Overview

```
┌─────────────┐     ┌──────────────┐     ┌───────────────┐     ┌──────────┐
│   Routes    │────▶│  Controllers │────▶│   Services    │────▶│Repositories│
│ (api/web)   │     │  (Api/Admin) │     │ (Business     │     │(Data      │
│             │     │  (Thin)      │     │  Logic)       │     │ Access)   │
└─────────────┘     └──────────────┘     └───────────────┘     └────┬─────┘
                                                                    │
                                                                    ▼
                                                              ┌──────────┐
                                                              │  Models  │
                                                              │(Eloquent)│
                                                              └──────────┘
```

### Key Design Decisions

- **Repository Pattern** — All database queries go through repository interfaces bound to Eloquent implementations in `AppServiceProvider`. This decouples data access from business logic and makes testing/swapping storage easy.
- **Service Layer** — Business logic lives in `Services/`, not in controllers or models. Controllers are thin — they validate input, call services, and return responses.
- **Database Transactions** — All write operations that span multiple models (checkout, product creation) use `DB::transaction()` for data integrity.
- **Soft Deletes** — Users, products, categories, and orders use soft deletes to prevent accidental data loss.
- **Auto-generated Slugs & SKUs** — Products auto-generate unique slugs and SKUs on creation via model boot events.
- **Order Snapshotting** — Order items snapshot product name, SKU, image, and price at time of purchase so historical data remains accurate even if products change.
