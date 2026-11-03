<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('user.orders', [
            'orders' => Order::query()
                ->where('user_id', $request->user()->id)
                ->with('items')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.color' => ['nullable', 'string', 'max:50'],
            'customer.name' => ['required', 'string', 'max:120'],
            'customer.phone' => ['required', 'string', 'max:30'],
            'customer.address' => ['required', 'string', 'max:500'],
            'payment_method' => ['required', 'in:Cash on delivery'],
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $products = Product::query()
                ->whereIn('id', collect($data['items'])->pluck('id'))
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== collect($data['items'])->pluck('id')->unique()->count()) {
                throw ValidationException::withMessages([
                    'items' => ['One or more selected products are no longer available. Refresh your cart and try again.'],
                ]);
            }

            $quantities = collect($data['items'])->groupBy('id')->map(
                fn ($items) => $items->sum('quantity')
            );

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);
                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["There is not enough stock for {$product->name}. Please update the quantity and try again."],
                    ]);
                }
            }

            $subtotalCents = collect($data['items'])->sum(
                fn (array $item) => (int) round((float) $products->get($item['id'])->price * 100) * $item['quantity']
            );
            $subtotal = $subtotalCents / 100;
            $order = Order::create([
                'user_id' => $request->user()->id,
                'customer_name' => $data['customer']['name'],
                'customer_email' => $request->user()->email,
                'customer_phone' => $data['customer']['phone'],
                'delivery_address' => $data['customer']['address'],
                'payment_method' => $data['payment_method'],
                'status' => 'processing',
                'subtotal' => $subtotal,
                'shipping_fee' => 0,
                'total' => $subtotal,
            ]);

            foreach ($data['items'] as $item) {
                $product = $products->get($item['id']);
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'brand' => $product->brand,
                    'category' => $product->category,
                    'image_path' => $product->image_path,
                    'color' => $item['color'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                ]);
            }

            foreach ($quantities as $productId => $quantity) {
                $products->get($productId)->decrement('stock', $quantity);
            }

            return $order;
        });

        return response()->json([
            'message' => 'Your order has been placed.',
            'order' => [
                'id' => $order->id,
                'number' => $order->order_number,
                'total' => (float) $order->total,
                'status' => $order->status,
            ],
        ], 201);
    }
}
