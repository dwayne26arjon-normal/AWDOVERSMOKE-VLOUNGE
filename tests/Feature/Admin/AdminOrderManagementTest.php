<?php

namespace Tests\Feature\Admin;

use App\Mail\OrderInvoiceMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_order_list_details_and_printable_invoice(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createOrder();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Customer Example');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Customer information')
            ->assertSee('Ordered items')
            ->assertSee('statusConfirmDialog', false)
            ->assertSee('Update order status?')
            ->assertSee('Cloud Berry')
            ->assertSee('₱900.00')
            ->assertSee(route('admin.orders.invoice', $order), false)
            ->assertSee(route('admin.orders.invoice.email', $order), false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.invoice', $order))
            ->assertOk()
            ->assertSee('Invoice '.$order->order_number)
            ->assertSee('Cloud Berry');
    }

    public function test_admin_can_email_invoice_to_customer(): void
    {
        Mail::fake();
        $admin = $this->createAdmin();
        $order = $this->createOrder();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.orders.invoice.email', $order))
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('status', 'Invoice emailed to the customer.');

        Mail::assertSent(OrderInvoiceMail::class, function (OrderInvoiceMail $mail) use ($order): bool {
            return $mail->hasTo($order->customer_email)
                && $mail->order->is($order);
        });
    }

    public function test_cancelling_order_restores_stock_and_reopening_reserves_it_again(): void
    {
        $admin = $this->createAdmin();
        $product = Product::create([
            'name' => 'Cloud Berry',
            'category' => 'E-liquids',
            'price' => 450,
            'stock' => 8,
            'is_active' => true,
        ]);
        $order = $this->createOrder($product);
        $this->assertSame(8, $product->fresh()->stock);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.status', $order), ['status' => 'cancelled'])
            ->assertRedirect(route('admin.orders.show', $order));
        $this->assertSame(10, $product->fresh()->stock);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.status', $order), ['status' => 'processing'])
            ->assertRedirect(route('admin.orders.show', $order));
        $this->assertSame(8, $product->fresh()->stock);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();

        return $admin;
    }

    private function createOrder(?Product $product = null): Order
    {
        $customer = User::factory()->create(['email' => 'customer@example.com']);
        $order = Order::create([
            'user_id' => $customer->id,
            'customer_name' => 'Customer Example',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '09123456789',
            'delivery_address' => '123 Example Street, Quezon City',
            'payment_method' => 'Cash on delivery',
            'status' => 'processing',
            'subtotal' => 900,
            'shipping_fee' => 0,
            'total' => 900,
        ]);
        $product ??= Product::create([
            'name' => 'Cloud Berry',
            'category' => 'E-liquids',
            'price' => 450,
            'stock' => 10,
            'is_active' => true,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'brand' => 'Vape Co',
            'category' => $product->category,
            'quantity' => 2,
            'unit_price' => 450,
        ]);

        return $order;
    }
}
