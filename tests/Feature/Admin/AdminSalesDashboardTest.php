<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSalesDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_sales_summary_and_seven_day_chart_excluding_cancelled_orders(): void
    {
        Carbon::setTestNow('2026-11-09 12:00:00');

        try {
            $admin = User::factory()->create(['is_admin' => true]);
            $customer = User::factory()->create();

            $this->createOrder($customer, 'processing', 300, now());
            $this->createOrder($customer, 'shipped', 200, now()->subDay());
            $this->createOrder($customer, 'cancelled', 100, now());
            $this->createOrder($customer, 'completed', 400, Carbon::parse('2026-10-31 12:00:00'));

            $this->actingAs($admin, 'admin')
                ->get(route('admin.dashboard'))
                ->assertOk()
                ->assertSee('Sales performance')
                ->assertSee('Download CSV')
                ->assertSee(route('admin.orders.sales.export', ['range' => 'day']), false)
                ->assertSee(route('admin.orders.sales.export', ['range' => 'month']), false)
                ->assertSee(route('admin.orders.sales.export', ['range' => 'year']), false)
                ->assertSee("Today's sales", false)
                ->assertSee("This month's sales", false)
                ->assertSee("This year's sales", false)
                ->assertSee('₱300.00')
                ->assertSee('₱500.00')
                ->assertSee('₱900.00')
                ->assertSee('Cash-on-delivery collections are not tracked here.');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_dashboard_filters_sales_by_day_month_and_year(): void
    {
        Carbon::setTestNow('2026-11-09 12:00:00');

        try {
            $admin = User::factory()->create(['is_admin' => true]);
            $customer = User::factory()->create();

            $this->createOrder($customer, 'processing', 300, Carbon::parse('2026-11-09 10:00:00'));
            $this->createOrder($customer, 'shipped', 200, Carbon::parse('2026-11-01 10:00:00'));
            $this->createOrder($customer, 'completed', 400, Carbon::parse('2026-10-31 10:00:00'));
            $this->createOrder($customer, 'completed', 500, Carbon::parse('2025-12-31 10:00:00'));
            $this->createOrder($customer, 'cancelled', 800, Carbon::parse('2026-11-09 11:00:00'));

            $this->actingAs($admin, 'admin')
                ->get(route('admin.dashboard', ['sales_period' => 'day', 'sales_value' => '2026-11-09']))
                ->assertOk()
                ->assertSee('Sales for November 9, 2026')
                ->assertSee('Sales in 4-hour blocks — November 9, 2026')
                ->assertSee('title="Nov 9, 8 AM–12 PM"', false)
                ->assertSee('8 AM–12 PM')
                ->assertSee('sales-row-value">₱300.00', false)
                ->assertSee('₱300.00')
                ->assertSee('1 order');

            $this->actingAs($admin, 'admin')
                ->get(route('admin.dashboard', ['sales_period' => 'month', 'sales_value' => '2026-11']))
                ->assertOk()
                ->assertSee('Sales for November 2026')
                ->assertSee('Sales by week — November 2026')
                ->assertSee('title="Nov 1–Nov 7"', false)
                ->assertSee('Week 1')
                ->assertSee('₱500.00')
                ->assertSee('2 orders');

            $this->actingAs($admin, 'admin')
                ->get(route('admin.dashboard', ['sales_period' => 'year', 'sales_value' => '2025']))
                ->assertOk()
                ->assertSee('Sales for 2025')
                ->assertSee('Sales by month — 2025')
                ->assertSee('title="December 2025"', false)
                ->assertSee('sales-row-value">₱500.00', false)
                ->assertSee('₱500.00')
                ->assertSee('1 order');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_dashboard_shows_selected_period_sales_by_product_category_excluding_cancelled_orders(): void
    {
        Carbon::setTestNow('2026-11-09 12:00:00');

        try {
            $admin = User::factory()->create(['is_admin' => true]);
            $customer = User::factory()->create();

            $order = $this->createOrder($customer, 'processing', 500, Carbon::parse('2026-11-09 10:00:00'));
            $order->items()->create([
                'product_name' => 'Fruit Juice',
                'brand' => 'Cloud Co',
                'category' => 'Juice',
                'quantity' => 2,
                'unit_price' => 100,
            ]);
            $order->items()->create([
                'product_name' => 'Pod Device',
                'brand' => 'Vape Works',
                'category' => 'Pods',
                'quantity' => 1,
                'unit_price' => 300,
            ]);

            $cancelledOrder = $this->createOrder($customer, 'cancelled', 900, Carbon::parse('2026-11-09 11:00:00'));
            $cancelledOrder->items()->create([
                'product_name' => 'Cancelled Juice',
                'category' => 'Juice',
                'quantity' => 9,
                'unit_price' => 100,
            ]);

            $this->actingAs($admin, 'admin')
                ->get(route('admin.dashboard', ['sales_period' => 'month', 'sales_value' => '2026-11']))
                ->assertOk()
                ->assertSee('Sales by product category')
                ->assertSee('Juice')
                ->assertSee('2 units')
                ->assertSee('aria-label="Juice sales: ₱200.00"', false)
                ->assertSee('Pods')
                ->assertSee('1 unit')
                ->assertSee('aria-label="Pods sales: ₱300.00"', false)
                ->assertSee('Most purchased categories')
                ->assertSee('Top products by units sold')
                ->assertSee('Fruit Juice')
                ->assertSee('Brand: Cloud Co')
                ->assertSee('Fruit Juice, brand Cloud Co: 2 units sold', false)
                ->assertSee('Category share of 3 units purchased')
                ->assertSee('66.7%')
                ->assertSee('33.3%')
                ->assertDontSee('9 units');
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createOrder(User $customer, string $status, int $total, Carbon $createdAt): Order
    {
        $order = Order::create([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '09123456789',
            'delivery_address' => '123 Example Street',
            'payment_method' => 'Cash on delivery',
            'status' => $status,
            'subtotal' => $total,
            'shipping_fee' => 0,
            'total' => $total,
        ]);
        $order->forceFill(['created_at' => $createdAt])->save();

        return $order;
    }
}
