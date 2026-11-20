<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Notifications\AdminPasswordResetCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_reset_password_with_an_email_code(): void
    {
        Notification::fake();
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => 'old-password-123',
            'is_admin' => true,
        ]);

        $this->get(route('admin.password.request'))
            ->assertOk()
            ->assertSee('Forgot your password?');

        $this->post(route('admin.password.email'), ['email' => $owner->email])
            ->assertRedirect(route('admin.password.code', ['email' => $owner->email]));

        $code = null;
        Notification::assertSentTo(
            $owner,
            AdminPasswordResetCode::class,
            function (AdminPasswordResetCode $notification) use (&$code): bool {
                $code = $notification->code;

                return true;
            }
        );

        $this->post(route('admin.password.code.verify'), [
            'email' => $owner->email,
            'code' => $code,
        ])->assertRedirect(route('admin.password.reset'));

        $this->post(route('admin.password.store'), [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('admin.login'));

        $this->assertTrue(Hash::check('new-password-123', $owner->fresh()->password));
    }

    public function test_customer_cannot_receive_an_admin_password_reset_code(): void
    {
        Notification::fake();
        $customer = User::factory()->create(['email' => 'customer@example.com']);

        $this->post(route('admin.password.email'), ['email' => $customer->email])
            ->assertRedirect(route('admin.password.code', ['email' => $customer->email]));

        Notification::assertNothingSent();
    }
}
