# Contributing to ShopSphere

Thank you for considering contributing to ShopSphere! We welcome contributions of all kinds — bug fixes, feature additions, documentation improvements, and more.

---

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Project Architecture](#project-architecture)
- [Coding Standards](#coding-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)
- [Testing](#testing)
- [Reporting Bugs](#reporting-bugs)
- [Feature Requests](#feature-requests)
- [Questions & Support](#questions--support)

---

## 📜 Code of Conduct

This project adheres to a [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code. Please report unacceptable behavior to the project maintainers.

**Our Pledge:**
- Be respectful and inclusive
- Use welcoming language
- Accept constructive criticism gracefully
- Focus on what's best for the community

---

## 🚀 Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- MySQL 8+ (or SQLite for development)
- Git
- Node.js & NPM (optional, for frontend asset building)

### 1. Fork & Clone

```bash
# Fork the repository on GitHub, then clone your fork
git clone https://github.com/your-username/shopsphere.git
cd shopsphere/backend

# Add the original repository as upstream
git remote add upstream https://github.com/original-org/shopsphere.git
```

### 2. Create a Branch

```bash
# Create a branch for your work
git checkout -b feature/your-feature-name
# or
git checkout -b fix/your-bug-fix
```

### 3. Install Dependencies

```bash
composer install
```

### 4. Set Environment

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`:
```env
DB_CONNECTION=mysql
DB_DATABASE=shopsphere
DB_USERNAME=root
DB_PASSWORD=
```

For quick local development, you can use SQLite:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/backend/database.sqlite
```

### 5. Run Migrations & Seeders

```bash
php artisan migrate --seed
```

### 6. (Optional) Link Storage

```bash
php artisan storage:link
```

### 7. Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000/admin` and log in with `admin@shopsphere.test` / `password`.

---

## 🔧 Development Setup

### Running Tests

```bash
# Run the full test suite
php artisan test

# Run a specific test file
php artisan test --filter=ProductTest

# Run tests with coverage (requires Xdebug or PCOV)
php artisan test --coverage
```

### Code Style

We use **Laravel Pint** for automatic code style fixing:

```bash
./vendor/bin/pint

# Check mode (dry run)
./vendor/bin/pint --test
```

### Generating API Documentation

When adding or modifying API endpoints, regenerate the Swagger documentation:

```bash
php artisan l5-swagger:generate
```

---

## 🏗️ Project Architecture

Understanding the architecture is essential before making changes:

```
Route → Controller → Service → Repository → Model
```

### Layers

| Layer | Location | Responsibility |
|---|---|---|
| **Routes** | `routes/api.php`, `routes/web.php` | Define endpoints and middleware |
| **Controllers** | `app/Http/Controllers/Api/`, `app/Http/Controllers/Admin/` | Handle HTTP requests, validate input, return responses |
| **Form Requests** | `app/Http/Requests/` | Validation rules and authorization |
| **Services** | `app/Services/` | Business logic (no HTTP concerns) |
| **Repositories** | `app/Repositories/Eloquent/` | Data access layer (database queries) |
| **Repository Contracts** | `app/Repositories/Contracts/` | Interfaces for repositories |
| **Models** | `app/Models/` | Eloquent models with relationships and scopes |
| **Policies** | `app/Policies/` | Authorization logic |
| **API Resources** | `app/Http/Resources/` | JSON response transformation |
| **Middleware** | `app/Http/Middleware/` | Request filtering (auth, roles, locale) |

### Dependency Injection

All bindings between repository contracts and implementations are registered in `AppServiceProvider`:

```php
$this->app->bind(
    ProductRepositoryInterface::class,
    ProductRepository::class
);
```

When adding a new repository:
1. Create the interface in `Repositories/Contracts/`
2. Create the implementation in `Repositories/Eloquent/`
3. Register the binding in `AppServiceProvider::register()`

---

## 📐 Coding Standards

### General Rules

- **Follow PSR-12** coding standard
- **Type hints** are required for all function parameters and return types
- **Strict typing** — declare `strict_types=1` in all new PHP files
- **Docblocks** — Use PHP attribute-based OpenAPI annotations for controllers; use standard PHPDoc for services and repositories
- **Avoid magic strings** — Use class constants instead (e.g., `User::ROLE_ADMIN`, `Order::STATUS_PENDING`)

### Naming Conventions

| Item | Convention | Example |
|---|---|---|
| Classes | PascalCase | `ProductRepository` |
| Methods/Functions | camelCase | `getOrCreateForUser()` |
| Variables/Properties | camelCase | `$productRepository` |
| Constants | UPPER_SNAKE_CASE | `STATUS_PENDING` |
| Database Tables | snake_case (plural) | `order_items` |
| Database Columns | snake_case | `discount_price` |
| Routes | kebab-case | `/api/products/best-sellers` |
| Route Names | dot-notation snake_case | `admin.orders.update-status` |

### Repository Pattern Guidelines

- **Methods must return types** from the interface contract
- **Use `with()` eager loading** to avoid N+1 queries
- **Use `withCount()`, `withAvg()`, `withSum()`** for aggregated data
- **Wrap multi-model writes** in `DB::transaction()`
- **Use `fresh()` after updates** to return the latest model state

### Controller Guidelines

- **Keep controllers thin** — delegate business logic to services
- **Use Form Requests** for validation (not inline `$request->validate()`)
- **Use API Resources** for consistent JSON responses
- **Return consistent JSON structure**: `{ success, message, data, meta }`

### Service Layer Guidelines

- **One responsibility per service** (e.g., `CartService` handles cart operations)
- **Inject repositories** via constructor dependency injection
- **Do not use `request()`, `response()`, or `auth()` facades** in services — receive data as parameters
- **Use value objects or arrays** for data transfer, not HTTP-related classes

### Database Migration Guidelines

- **One migration per table** — use descriptive class names
- **Add indexes** for columns used in `WHERE`, `ORDER BY`, or `JOIN` clauses
- **Use `foreignId()` for foreign keys** with explicit `constrained()` and `onDelete()` behavior
- **Use `softDeletes()`** for important data tables

---

## ✅ Commit Guidelines

We follow [Conventional Commits](https://www.conventionalcommits.org/) for commit messages:

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

### Types

| Type | Usage |
|---|---|
| `feat` | A new feature |
| `fix` | A bug fix |
| `docs` | Documentation changes |
| `style` | Code style (formatting, no logic change) |
| `refactor` | Code refactoring |
| `perf` | Performance improvement |
| `test` | Adding or fixing tests |
| `chore` | Maintenance, dependencies, etc. |

### Examples

```
feat(api): add order filtering by date range
fix(cart): prevent negative quantities on update
docs(readme): add deployment instructions
test(auth): add test for inactive user login
refactor(repositories): extract filter logic into trait
```

---

## 🔄 Pull Request Process

1. **Create an issue** first for significant changes (new features, refactors) to discuss before implementing.

2. **Keep PRs focused** — one feature or bug fix per pull request. Large changes should be broken into smaller, reviewable PRs.

3. **Write tests** for new functionality. All tests must pass before review.

4. **Update documentation** if you change the API, configuration, or architecture.

5. **Run code style checks** before submitting:

   ```bash
   ./vendor/bin/pint --test
   php artisan test
   ```

6. **Rebase on main** before submitting:

   ```bash
   git fetch upstream
   git rebase upstream/main
   ```

7. **Submit the PR** with a clear title and description referencing the related issue.

### PR Checklist

Before submitting, ensure your PR:

- [ ] Follows the coding standards
- [ ] Includes tests for new functionality
- [ ] All existing tests pass
- [ ] Updates relevant documentation (README, Swagger annotations, etc.)
- [ ] Has no unnecessary whitespace or formatting changes
- [ ] Commits are squashed into logical units
- [ ] Branch is rebased on the latest upstream main

### Review Process

- Maintainers will review your code within a reasonable timeframe
- Address any feedback or requested changes promptly
- Once approved, a maintainer will merge your PR

---

## 🧪 Testing Guidelines

### Writing Tests

- **Use `RefreshDatabase` trait** for feature tests
- **Use model factories** for test data creation
- **Test both success and failure paths** (404, 422, 403 responses)
- **Avoid hitting real external services** — mock or use in-memory storage

### Test Structure

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonStructure(['success', 'data', 'meta']);
    }

    public function test_returns_404_for_missing_product(): void
    {
        $response = $this->getJson('/api/products/99999');

        $response->assertNotFound();
    }
}
```

### Running Tests

```bash
# All tests
php artisan test

# Specific file
php artisan test --filter=CartTest

# Directory
php artisan test tests/Feature/Api/
```

---

## 🐛 Reporting Bugs

When reporting bugs, include:

1. **Description** — What's the issue?
2. **Steps to reproduce** — Minimal reproduction steps
3. **Expected behavior** — What should happen
4. **Actual behavior** — What actually happens
5. **Environment** — PHP version, database, OS

Open an issue on GitHub using the bug report template.

---

## 💡 Feature Requests

Have an idea? We'd love to hear it! Open an issue with:

1. **Problem statement** — What problem does this solve?
2. **Proposed solution** — How would you implement it?
3. **Alternatives considered** — Any other approaches considered
4. **Additional context** — Screenshots, examples, etc.

---

## ❓ Questions & Support

- **Documentation** — Check the [README.md](README.md) first
- **Issues** — Use GitHub issues for bugs and feature requests
- **API Docs** — Visit `/documentation` when running the application

---

## 🎉 Thank You

Your contributions make open-source great. Every bug report, documentation fix, and new feature helps make ShopSphere better for everyone. ❤️
