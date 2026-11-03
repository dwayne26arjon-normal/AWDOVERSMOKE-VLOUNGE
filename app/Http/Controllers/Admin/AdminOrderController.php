<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderInvoiceMail;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AdminOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->withCount('items')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load('items'),
            'statuses' => ['processing', 'shipped', 'completed', 'cancelled'],
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['processing', 'shipped', 'completed', 'cancelled'])],
        ]);

        DB::transaction(function () use ($order, $data) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $items = $lockedOrder->items;
            $quantities = $items->whereNotNull('product_id')
                ->groupBy('product_id')
                ->map(fn ($productItems) => $productItems->sum('quantity'));

            if ($lockedOrder->status !== 'cancelled' && $data['status'] === 'cancelled') {
                foreach ($quantities as $productId => $quantity) {
                    Product::query()->whereKey($productId)->increment('stock', $quantity);
                }
            } elseif ($lockedOrder->status === 'cancelled' && $data['status'] !== 'cancelled') {
                $products = Product::query()
                    ->whereIn('id', $quantities->keys())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($quantities as $productId => $quantity) {
                    $product = $products->get($productId);
                    if (! $product || ! $product->is_active || $product->stock < $quantity) {
                        throw ValidationException::withMessages([
                            'status' => ['A product in this order does not have enough active stock to reopen it.'],
                        ]);
                    }
                }

                foreach ($quantities as $productId => $quantity) {
                    $products->get($productId)->decrement('stock', $quantity);
                }
            }

            $lockedOrder->update($data);
        });

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order status updated.');
    }

    public function printInvoice(Order $order): View
    {
        return view('admin.orders.invoice', ['order' => $order->load('items')]);
    }

    public function emailInvoice(Order $order): RedirectResponse
    {
        try {
            Mail::to($order->customer_email)->send(new OrderInvoiceMail($order->load('items')));
        } catch (TransportExceptionInterface $exception) {
            Log::error('Order invoice email delivery failed.', [
                'order_id' => $order->id,
                'exception' => $exception::class,
            ]);

            return back()->withErrors([
                'invoice' => 'The invoice could not be emailed. Check the mail settings and try again.',
            ]);
        }

        return redirect()->route('admin.orders.show', $order)->with('status', 'Invoice emailed to the customer.');
    }
}
