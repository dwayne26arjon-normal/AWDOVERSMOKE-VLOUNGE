<?php

namespace Tests\Feature\User;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_updates_create_a_customer_notification_that_can_be_read(): void
    {
        $customer = User::factory()->create();
        $customer->refresh();
        $admin = $this->createAdmin();
        $order = $this->createOrder($customer);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.status', $order), ['status' => 'shipped'])
            ->assertRedirect(route('admin.orders.show', $order));
        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.status', $order), ['status' => 'shipped'])
            ->assertRedirect(route('admin.orders.show', $order));
        $this->assertDatabaseCount('notifications', 1);

        $notification = $customer->notifications()->firstOrFail();
        $this->assertSame(OrderStatusUpdated::class, $notification->type);
        $this->assertSame($order->order_number, $notification->data['order_number']);
        $this->assertSame('shipped', $notification->data['status']);

        $this->actingAs($customer, 'web')
            ->getJson(route('user.notifications.index'))
            ->assertOk()
            ->assertJsonPath('enabled', true)
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.message', 'Your order status is now Shipped.');

        $this->actingAs($customer, 'web')
            ->patchJson(route('user.notifications.read', $notification->id))
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_customers_can_turn_order_notifications_off_and_on(): void
    {
        $customer = User::factory()->create();
        $admin = $this->createAdmin();
        $order = $this->createOrder($customer);

        $this->actingAs($customer, 'web')
            ->patchJson(route('user.notifications.settings'), ['enabled' => false])
            ->assertOk()
            ->assertJsonPath('enabled', false);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.status', $order), ['status' => 'shipped'])
            ->assertRedirect();
        $this->assertDatabaseCount('notifications', 0);

        $this->actingAs($customer, 'web')
            ->patchJson(route('user.notifications.settings'), ['enabled' => true])
            ->assertOk()
            ->assertJsonPath('enabled', true);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.status', $order), ['status' => 'completed'])
            ->assertRedirect();

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_a_customer_cannot_mark_another_customers_notification_as_read(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $admin = $this->createAdmin();
        $order = $this->createOrder($customer);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.status', $order), ['status' => 'shipped']);
        $notificationId = $customer->notifications()->value('id');

        $this->actingAs($otherCustomer, 'web')
            ->patchJson(route('user.notifications.read', $notificationId))
            ->assertNotFound();

        $this->assertNull($customer->notifications()->firstOrFail()->read_at);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();

        return $admin;
    }

    private function createOrder(User $customer): Order
    {
        return Order::create([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '09123456789',
            'delivery_address' => '123 Example Street, Quezon City',
            'payment_method' => 'Cash on delivery',
            'status' => 'processing',
            'subtotal' => 900,
            'shipping_fee' => 0,
            'total' => 900,
        ]);
    }
}
