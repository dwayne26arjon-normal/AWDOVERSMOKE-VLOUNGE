<?php

namespace Tests\Feature\User;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_place_an_order_using_database_prices_and_stock(): void
    {
        $customer = User::factory()->create(['email' => 'customer@example.com']);
        $product = Product::create([
            'name' => 'Cloud Berry',
            'brand' => 'Vape Co',
            'category' => 'E-liquids',
            'price' => 450,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->postJson(route('orders.store'), [
                'items' => [
                    ['id' => $product->id, 'quantity' => 1, 'color' => 'Violet', 'price' => 1],
                    ['id' => $product->id, 'quantity' => 1, 'color' => 'Blue', 'price' => 1],
                ],
                'customer' => [
                    'name' => 'Delivery Customer',
                    'phone' => '09123456789',
                    'address' => '123 Example Street, Quezon City',
                ],
                'payment_method' => 'Cash on delivery',
            ])
            ->assertCreated()
            ->assertJsonPath('order.number', 'OS-000001')
            ->assertJsonPath('order.total', 900);

        $order = Order::with('items')->firstOrFail();
        $this->assertSame($customer->id, $order->user_id);
        $this->assertSame('customer@example.com', $order->customer_email);
        $this->assertSame('Cloud Berry', $order->items->first()->product_name);
        $this->assertSame('450.00', $order->items->first()->unit_price);
        $this->assertCount(2, $order->items);
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame('0.00', $order->shipping_fee);
    }

    public function test_customer_order_is_rejected_if_inventory_is_insufficient(): void
    {
        $customer = User::factory()->create();
        $product = Product::create([
            'name' => 'Limited Pod',
            'category' => 'Devices',
            'price' => 1200,
            'stock' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->postJson(route('orders.store'), [
                'items' => [['id' => $product->id, 'quantity' => 2]],
                'customer' => [
                    'name' => 'Delivery Customer',
                    'phone' => '09123456789',
                    'address' => '123 Example Street',
                ],
                'payment_method' => 'Cash on delivery',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_customer_order_reserves_the_selected_variant_stock(): void
    {
        $customer = User::factory()->create();
        $product = Product::create([
            'name' => 'Berry Juice',
            'category' => 'Juices',
            'price' => 450,
            'stock' => 7,
            'is_active' => true,
        ]);
        $berry = $product->variants()->create(['name' => 'Berry Ice', 'stock' => 5]);
        $mint = $product->variants()->create(['name' => 'Cool Mint', 'stock' => 2]);

        $payload = [
            'items' => [['id' => $product->id, 'quantity' => 3, 'color' => 'Berry Ice']],
            'customer' => [
                'name' => 'Delivery Customer',
                'phone' => '09123456789',
                'address' => '123 Example Street',
            ],
            'payment_method' => 'Cash on delivery',
        ];

        $this->actingAs($customer)
            ->postJson(route('orders.store'), $payload)
            ->assertCreated();

        $this->assertSame(2, $berry->fresh()->stock);
        $this->assertSame(2, $mint->fresh()->stock);
        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame('Berry Ice', Order::firstOrFail()->items->first()->color);

        $payload['items'][0]['quantity'] = 3;
        $this->actingAs($customer)
            ->postJson(route('orders.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items');

        $this->assertSame(2, $berry->fresh()->stock);
    }

    public function test_customer_orders_page_shows_only_that_customers_database_orders(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $attributes = [
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '09123456789',
            'delivery_address' => '123 Example Street',
            'payment_method' => 'Cash on delivery',
            'subtotal' => 450,
            'shipping_fee' => 0,
            'total' => 450,
        ];
        $ownOrder = Order::create($attributes + ['user_id' => $customer->id]);
        Order::create($attributes + ['user_id' => $otherCustomer->id]);

        $this->actingAs($customer)
            ->get(route('orders'))
            ->assertOk()
            ->assertSee($ownOrder->order_number)
            ->assertDontSee('OS-000002')
            ->assertSee('Saved to your account');
    }

    public function test_customer_order_updates_return_only_their_current_statuses(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $attributes = [
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '09123456789',
            'delivery_address' => '123 Example Street',
            'payment_method' => 'Cash on delivery',
            'subtotal' => 450,
            'shipping_fee' => 0,
            'total' => 450,
        ];
        $ownOrder = Order::create($attributes + ['user_id' => $customer->id]);
        Order::create($attributes + ['user_id' => $otherCustomer->id]);

        $response = $this->actingAs($customer)
            ->getJson(route('orders.updates'))
            ->assertOk();

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertJsonCount(1, 'orders')
            ->assertJsonPath('orders.0.id', $ownOrder->id)
            ->assertJsonPath('orders.0.status', 'processing');

        $ownOrder->update(['status' => 'shipped']);

        $this->actingAs($customer)
            ->getJson(route('orders.updates'))
            ->assertJsonPath('orders.0.status', 'shipped');
    }

    public function test_customer_order_page_displays_manila_purchase_time(): void
    {
        $this->assertSame('Asia/Manila', config('app.timezone'));
        $customer = User::factory()->create();
        $order = Order::create([
            'user_id' => $customer->id,
            'customer_name' => 'Test Customer',
            'customer_email' => $customer->email,
            'customer_phone' => '09123456789',
            'delivery_address' => '123 Example Street',
            'payment_method' => 'Cash on delivery',
            'subtotal' => 450,
            'shipping_fee' => 0,
            'total' => 450,
        ]);
        $order->created_at = Carbon::parse('2026-11-20 09:00:00', 'Asia/Manila');
        $order->save();

        $this->actingAs($customer)
            ->get(route('orders'))
            ->assertOk()
            ->assertSee('Placed Nov 20, 2026 · 9:00 AM')
            ->assertSee($order->order_number);
    }
}
