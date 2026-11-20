<h1>Oversmoke Vlounge order invoice</h1>
<p>Hello {{ $order->customer_name }},</p>
<p>Here is the invoice for your order <strong>{{ $order->order_number }}</strong>, placed {{ $order->created_at->timezone(config('app.timezone'))->format('F j, Y') }}.</p>
<h2>Items</h2>
<table width="100%" cellpadding="8" cellspacing="0" border="1">
    <thead><tr><th align="left">Product</th><th>Qty</th><th align="right">Total</th></tr></thead>
    <tbody>
    @foreach ($order->items as $item)
        <tr>
            <td>{{ $item->product_name }}{{ $item->color ? ' · '.$item->color : '' }}</td>
            <td align="center">{{ $item->quantity }}</td>
            <td align="right">₱{{ number_format((float) $item->unit_price * $item->quantity, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<p>Subtotal: ₱{{ number_format((float) $order->subtotal, 2) }}</p>
<p>Shipping fee: ₱{{ number_format((float) $order->shipping_fee, 2) }}</p>
<p><strong>Total: ₱{{ number_format((float) $order->total, 2) }}</strong></p>
<p>Delivery address: {{ $order->delivery_address }}</p>
<p>Payment method: {{ $order->payment_method }}</p>
