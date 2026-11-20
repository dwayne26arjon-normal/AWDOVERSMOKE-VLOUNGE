<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_number }} | Oversmoke Vlounge</title>
    <style>
        body { max-width: 800px; margin: 40px auto; padding: 0 24px; color: #1a1a1a; font-family: Arial, sans-serif; font-size: 14px; }
        header, .row, .total { display: flex; justify-content: space-between; gap: 24px; }
        header { padding-bottom: 18px; border-bottom: 2px solid #222; }
        h1 { margin: 0 0 8px; font-size: 24px; }
        h2 { margin: 26px 0 10px; font-size: 16px; }
        p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #ddd; text-align: left; }
        th:last-child, td:last-child { text-align: right; }
        .totals { width: min(320px, 100%); margin: 16px 0 0 auto; }
        .row { padding: 7px 0; }
        .total { margin-top: 6px; padding-top: 10px; border-top: 2px solid #222; font-weight: 700; }
        .print-button { margin-top: 24px; padding: 9px 16px; cursor: pointer; }
        @media print { body { margin: 0 auto; padding: 0; }.print-button { display: none; } }
    </style>
</head>
<body>
    <header>
        <div><h1>Oversmoke Vlounge</h1><p>Customer order invoice</p></div>
        <div><strong>Invoice {{ $order->order_number }}</strong><p>{{ $order->created_at->timezone(config('app.timezone'))->format('F j, Y g:i A') }}</p></div>
    </header>
    <section>
        <h2>Bill to / Deliver to</h2>
        <p><strong>{{ $order->customer_name }}</strong></p>
        <p>{{ $order->customer_email }}</p>
        <p>{{ $order->customer_phone }}</p>
        <p>{{ $order->delivery_address }}</p>
    </section>
    <h2>Ordered items</h2>
    <table>
        <thead><tr><th>Product</th><th>Quantity</th><th>Unit price</th><th>Total</th></tr></thead>
        <tbody>
        @foreach ($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}{{ $item->color ? ' · '.$item->color : '' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>₱{{ number_format((float) $item->unit_price, 2) }}</td>
                <td>₱{{ number_format((float) $item->unit_price * $item->quantity, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <section class="totals">
        <div class="row"><span>Subtotal</span><strong>₱{{ number_format((float) $order->subtotal, 2) }}</strong></div>
        <div class="row"><span>Shipping fee</span><strong>₱{{ number_format((float) $order->shipping_fee, 2) }}</strong></div>
        <div class="total"><span>Total</span><span>₱{{ number_format((float) $order->total, 2) }}</span></div>
    </section>
    <p>Payment method: {{ $order->payment_method }}</p>
    <button class="print-button" type="button" onclick="window.print()">Print invoice</button>
</body>
</html>
