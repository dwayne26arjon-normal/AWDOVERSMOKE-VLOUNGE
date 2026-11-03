<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_products_and_inventory(): void
    {
        $admin = $this->createAdmin();
        Product::create([
            'name' => 'Pod Device',
            'brand' => 'Vape Co',
            'category' => 'Devices',
            'price' => 1200,
            'stock' => 8,
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pod Device')
            ->assertSee('Stock attention');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Pod Device')
            ->assertSee('PRODUCT NAME');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.inventory.index', ['stock' => 'low']))
            ->assertOk()
            ->assertSee('Pod Device')
            ->assertSee('Low stock');
    }

    public function test_admin_inventory_shows_product_counts_and_filters_by_category_and_stock(): void
    {
        $admin = $this->createAdmin();
        Product::create([
            'name' => 'Berry Cloud',
            'category' => 'E-liquids',
            'price' => 450,
            'stock' => 9,
            'is_active' => true,
        ]);
        Product::create([
            'name' => 'Mint Cloud',
            'category' => 'E-liquids',
            'price' => 450,
            'stock' => 2,
            'is_active' => true,
        ]);
        Product::create([
            'name' => 'Pod Device',
            'category' => 'Devices',
            'price' => 1200,
            'stock' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.inventory.index', [
                'category' => 'E-liquids',
                'sort' => 'low',
            ]))
            ->assertOk()
            ->assertSee('TOTAL PRODUCTS')
            ->assertSee('IN STOCK')
            ->assertSee('LOW STOCKS')
            ->assertSee('OUT OF STOCK')
            ->assertSeeInOrder(['Mint Cloud', 'Berry Cloud'])
            ->assertDontSee('Pod Device');
    }

    public function test_admin_product_catalog_shows_classifications_and_filters_by_category(): void
    {
        $admin = $this->createAdmin();
        Product::create([
            'name' => 'Berry Cloud',
            'category' => 'E-liquids',
            'price' => 450,
            'stock' => 5,
            'is_active' => true,
        ]);
        Product::create([
            'name' => 'Pod Device',
            'category' => 'Devices',
            'price' => 1200,
            'stock' => 30,
            'is_active' => false,
        ]);
        Product::create([
            'name' => 'Empty Pod',
            'category' => 'Devices',
            'price' => 900,
            'stock' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index', ['category' => 'E-liquids']))
            ->assertOk()
            ->assertSee('PRODUCT NAME')
            ->assertSee('CATEGORY')
            ->assertSee('STOCK')
            ->assertSee('STATUS')
            ->assertSee('Low stock')
            ->assertSee('E-liquids')
            ->assertDontSee('Pod Device');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Out of stock')
            ->assertSee('In stock')
            ->assertSee('Hidden');
    }

    public function test_product_created_by_admin_appears_in_customer_store(): void
    {
        $admin = $this->createAdmin();
        $customer = User::factory()->create();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.store'), [
                'name' => 'Admin Added Berry',
                'brand' => 'Vape Co',
                'category' => 'E-liquids',
                'description' => 'Berry flavor.',
                'price' => '450',
                'stock' => 20,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->actingAs($customer, 'web')
            ->get('/home')
            ->assertOk()
            ->assertSee('Admin Added Berry')
            ->assertSee('Vape Co');
    }

    public function test_admin_can_create_update_and_delete_products(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $this->actingAs($admin, 'admin');

        $this->post(route('admin.products.store'), [
            'name' => 'Cloud Pod',
            'brand' => 'Vape Co',
            'category' => 'Pod Systems',
            'description' => 'A sample product description.',
            'price' => '1499.50',
            'stock' => 25,
            'is_active' => '1',
            'image' => UploadedFile::fake()->createWithContent(
                'cloud-pod.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/l3sAAAAASUVORK5CYII=')
            ),
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Cloud Pod')->firstOrFail();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => true,
            'stock' => 25,
        ]);
        Storage::disk('public')->assertExists($product->image_path);

        $this->put(route('admin.products.update', $product), [
            'name' => 'Cloud Pod Plus',
            'brand' => 'Vape Co',
            'category' => 'Pod Systems',
            'description' => 'Updated product details.',
            'price' => '1599.00',
            'stock' => 12,
            'is_active' => '1',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Cloud Pod Plus',
            'stock' => 12,
        ]);

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing($product->image_path);
    }

    public function test_customer_cannot_manage_admin_products(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.products.index'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->post(route('admin.products.store'), [
                'name' => 'Unauthorized product',
                'category' => 'Other',
                'price' => 1,
                'stock' => 1,
            ])
            ->assertForbidden();
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();

        return $admin;
    }
}
