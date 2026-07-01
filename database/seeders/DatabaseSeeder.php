<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🔧 Seeding ShopSphere database...');

            $this->seedUsers();
            $this->command->info('✅ Users seeded');

            $this->seedCategories();
            $this->command->info('✅ Categories seeded');

            $this->seedProducts();
            $this->command->info('✅ Products and images seeded');

            $this->seedWishlists();
            $this->command->info('✅ Wishlists seeded');

            $this->seedReviews();
            $this->command->info('✅ Reviews seeded');

            $this->seedOrders();
            $this->command->info('✅ Orders and items seeded');

            $this->command->info('🎉 All seeders completed successfully!');
        });
    }

    /* ------------------------------------------------------------------ */
    /*  Users                                                               */
    /* ------------------------------------------------------------------ */

    protected function seedUsers(): void
    {
        // 1 Admin
        User::updateOrCreate(
            ['email' => 'admin@shopsphere.test'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),
                'role'     => User::ROLE_ADMIN,
                'status'   => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        // 30 Customers
        User::factory()
            ->count(30)
            ->create(['role' => User::ROLE_CUSTOMER]);
    }

    /* ------------------------------------------------------------------ */
    /*  Categories                                                          */
    /* ------------------------------------------------------------------ */

    protected function seedCategories(): void
    {
        $names = [
            'Electronics', 'Fashion Men', 'Fashion Women', 'Home & Kitchen',
            'Beauty & Personal Care', 'Sports & Outdoors', 'Books', 'Toys & Games',
            'Automotive', 'Grocery', 'Health & Wellness', 'Pet Supplies',
            'Office Products', 'Tools & Home Improvement', 'Garden & Outdoor',
            'Baby Products', 'Music & Instruments', 'Movies & TV Shows', 'Software',
            'Industrial & Scientific',
        ];

        foreach ($names as $name) {
            Category::firstOrCreate(
                ['name' => $name],
                [
                    'description' => fake()->sentence(12),
                    'status'      => true,
                ]
            );
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Products                                                            */
    /* ------------------------------------------------------------------ */

    protected function seedProducts(): void
    {
        $categories = Category::all();

        $products = [
            ['name' => 'Wireless Bluetooth Headphones',   'category' => 'Electronics',       'seed' => 'electronics-headphones-pro', 'price' => [79, 199]],
            ['name' => 'Cotton Crew Neck T-Shirt',        'category' => 'Fashion Men',        'seed' => 'fashion-tshirt-cotton',      'price' => [14, 39]],
            ['name' => 'Floral Summer Dress',             'category' => 'Fashion Women',      'seed' => 'fashion-summer-dress',       'price' => [29, 79]],
            ['name' => 'Stainless Steel French Press',    'category' => 'Home & Kitchen',     'seed' => 'kitchen-french-press',       'price' => [19, 49]],
            ['name' => 'Vitamin C Brightening Serum',     'category' => 'Beauty & Personal Care', 'seed' => 'beauty-vitamin-serum',    'price' => [14, 39]],
            ['name' => 'Premium Non-Slip Yoga Mat',       'category' => 'Sports & Outdoors',  'seed' => 'sports-yoga-mat',            'price' => [19, 49]],
            ['name' => 'Bestseller Fiction Novel',        'category' => 'Books',              'seed' => 'books-fiction-novel',        'price' => [9, 24]],
            ['name' => 'Collectible Action Figure',       'category' => 'Toys & Games',       'seed' => 'toys-action-figure',         'price' => [14, 39]],
            ['name' => 'Ergonomic Office Chair',          'category' => 'Office Products',    'seed' => 'office-ergo-chair',          'price' => [149, 399]],
            ['name' => 'Ceramic Plant Pot Set',           'category' => 'Garden & Outdoor',   'seed' => 'garden-plant-pot',           'price' => [14, 34]],
        ];

        $categoryMap = $categories->keyBy('name');

        foreach ($products as $productData) {
            $category = $categoryMap->get($productData['category']);
            if (!$category) continue;

            $price = fake()->randomFloat(2, $productData['price'][0], $productData['price'][1]);
            $hasDiscount = fake()->boolean(35);

            $product = Product::create([
                'category_id'    => $category->id,
                'name'           => $productData['name'],
                'description'    => fake()->paragraphs(2, true),
                'price'          => $price,
                'discount_price' => $hasDiscount ? round($price * fake()->randomFloat(2, 0.4, 0.9), 2) : null,
                'stock_quantity' => fake()->numberBetween(0, 150),
                'featured'       => fake()->boolean(15),
                'status'         => true,
            ]);

            // Gallery images using descriptive seed
            $imageCount = fake()->numberBetween(1, 4);
            for ($j = 0; $j < $imageCount; $j++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => 'https://picsum.photos/seed/' . $productData['seed'] . '-gallery-' . $j . '/600/600',
                    'sort_order' => $j,
                ]);
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Wishlists                                                           */
    /* ------------------------------------------------------------------ */

    protected function seedWishlists(): void
    {
        $customers = User::where('role', User::ROLE_CUSTOMER)->get();
        $products  = Product::all();

        foreach ($customers->random(20) as $customer) {
            $wished = $products->random(min(8, $products->count()));
            foreach ($wished as $product) {
                Wishlist::firstOrCreate([
                    'user_id'    => $customer->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Reviews                                                             */
    /* ------------------------------------------------------------------ */

    protected function seedReviews(): void
    {
        $customers = User::where('role', User::ROLE_CUSTOMER)->get();
        $products  = Product::inRandomOrder()->limit(80)->get();

        foreach ($products as $product) {
            $reviewers = $customers->random(min(5, $customers->count()));
            foreach ($reviewers as $reviewer) {
                Review::firstOrCreate(
                    ['user_id' => $reviewer->id, 'product_id' => $product->id],
                    [
                        'rating'   => fake()->numberBetween(3, 5),
                        'comment'  => fake()->optional(0.7)->paragraph(),
                        'approved' => true,
                    ]
                );
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Orders                                                              */
    /* ------------------------------------------------------------------ */

    protected function seedOrders(): void
    {
        $customers = User::where('role', User::ROLE_CUSTOMER)->get();
        $products  = Product::all();

        foreach ($customers->random(15) as $customer) {
            $cart = Cart::firstOrCreate(['user_id' => $customer->id]);

            // Each customer gets 1-3 orders
            $orderCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $orderCount; $i++) {
                $items = $products->random(min(3, $products->count()));
                $subtotal = 0;

                $order = Order::create([
                    'user_id'          => $customer->id,
                    'subtotal'         => 0,
                    'tax'              => 0,
                    'shipping_fee'     => 5.00,
                    'discount'         => 0,
                    'total'            => 0,
                    'status'           => fake()->randomElement([
                        Order::STATUS_PENDING,
                        Order::STATUS_PROCESSING,
                        Order::STATUS_SHIPPED,
                        Order::STATUS_DELIVERED,
                    ]),
                    'payment_status'   => Order::PAYMENT_PAID,
                    'payment_method'   => 'cod',
                    'shipping_address' => $customer->address,
                    'phone'            => $customer->phone,
                    'notes'            => fake()->optional(0.2)->sentence(),
                ]);

                foreach ($items as $product) {
                    $qty = fake()->numberBetween(1, 3);
                    $price = $product->discount_price ?? $product->price;
                    $lineTotal = round($qty * $price, 2);
                    $subtotal += $lineTotal;

                    OrderItem::create([
                        'order_id'      => $order->id,
                        'product_id'    => $product->id,
                        'product_name'  => $product->name,
                        'product_sku'   => $product->sku,
                        'product_image' => $product->image,
                        'quantity'      => $qty,
                        'price'         => $price,
                        'subtotal'      => $lineTotal,
                    ]);
                }

                $tax    = round($subtotal * 0.10, 2);
                $total  = $subtotal + $tax + 5.00;

                $order->update([
                    'subtotal' => round($subtotal, 2),
                    'tax'      => $tax,
                    'total'    => round($total, 2),
                ]);
            }
        }
    }
}
