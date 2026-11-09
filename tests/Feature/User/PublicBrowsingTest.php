<?php

namespace Tests\Feature\User;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_browse_home_and_product_details(): void
    {
        $product = Product::create([
            'name' => 'Public Product',
            'brand' => 'Vape Co',
            'category' => 'Devices',
            'price' => 1200,
            'stock' => 5,
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertRedirect(route('home'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Public Product')
            ->assertSee('/go/login')
            ->assertSee('/go/register');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Public Product')
            ->assertSee('data-authenticated="false"', false)
            ->assertSee('id="authDialog"', false)
            ->assertSee('/go/login')
            ->assertSee('/go/register');
    }

    public function test_purchase_routes_require_login_and_return_to_requested_page(): void
    {
        $this->get(route('checkout'))
            ->assertRedirect(route('login'));

        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => bcrypt('customer-password'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'customer-password',
        ])->assertRedirect(route('checkout'));
    }
}
