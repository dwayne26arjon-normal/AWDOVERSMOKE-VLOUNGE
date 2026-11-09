<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_with_name_and_age_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Alex Customer',
            'email' => 'alex@example.com',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
            'age_confirmation' => '1',
            'terms_accepted' => '1',
        ]);

        $response->assertRedirect('/go/login');
        $this->assertDatabaseHas('users', [
            'name' => 'Alex Customer',
            'email' => 'alex@example.com',
        ]);
        $this->assertTrue(Hash::check(
            'secure-password-123',
            User::query()->where('email', 'alex@example.com')->firstOrFail()->password,
        ));
    }

    public function test_customer_registration_requires_age_confirmation(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Alex Customer',
            'email' => 'alex@example.com',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ]);

        $response->assertRedirect('/register')
            ->assertSessionHasErrors('age_confirmation');
        $this->assertDatabaseMissing('users', ['email' => 'alex@example.com']);
    }

    public function test_customer_registration_requires_terms_acceptance(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Alex Customer',
            'email' => 'alex@example.com',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
            'age_confirmation' => '1',
        ]);

        $response->assertRedirect('/register')
            ->assertSessionHasErrors('terms_accepted');
        $this->assertDatabaseMissing('users', ['email' => 'alex@example.com']);
    }

    public function test_terms_and_privacy_policy_pages_are_available(): void
    {
        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Terms & Conditions')
            ->assertSee('cash on delivery');

        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('does not collect online payment card details');
    }

    public function test_login_page_links_to_terms_and_privacy_policy(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(route('terms'))
            ->assertSee(route('privacy'));
    }
}
