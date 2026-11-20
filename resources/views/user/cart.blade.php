<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cart | OversmokeVlounge</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/cart.css') }}?v={{ filemtime(public_path('css/cart.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/account-menu.css') }}?v={{ filemtime(public_path('css/account-menu.css')) }}">
</head>
<body data-user-id="{{ auth('web')->id() }}">
    <div class="smoke"></div>
    <header class="topbar">
        <a class="logo" href="{{ url('/home') }}">
            <img src="{{ asset('images/oversmokev-mascot.png') }}" alt="">
            <span>OVERSMOKE</span>
        </a>
        <nav>
            <a href="{{ url('/home') }}">Home</a>
            <a href="{{ route('orders') }}">Orders</a>
            <a class="active" href="{{ route('cart') }}" aria-current="page">Cart</a>
        </nav>
        <div class="topbar-actions">
        <a class="cart-link" href="{{ route('cart') }}" aria-label="Cart">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
            <span class="cart-count" id="cartCount">0</span>
        </a>
        <x-user.account-menu />
        </div>
    </header>

    <main class="page">
        <a class="back-link" href="{{ url('/home') }}">← <span>Continue shopping</span></a>
        <h1>Shopping Cart</h1>
        <section class="cart-panel" aria-label="Shopping cart">
            <div class="cart-heading">
                <label class="select-all"><input type="checkbox" id="selectAll"> Select all</label>
                <span>Unit Price</span>
                <span>Quantity</span>
                <span>Total Price</span>
                <span>Actions</span>
            </div>
            <div id="cartItems" class="cart-items"></div>
            <div class="empty-cart" id="emptyCart" hidden>
                <p>Your cart is empty.</p>
                <a href="{{ url('/home#products') }}">Browse products</a>
            </div>
            <div class="cart-footer">
                <p id="cartMessage" role="status" aria-live="polite"></p>
                <div class="checkout-summary">
                    <span>Selected subtotal (<span id="selectedCount">0</span> items)</span>
                    <strong>₱ <span id="cartTotal">0</span></strong>
                    <button id="checkout" type="button" disabled>Check Out</button>
                </div>
            </div>
        </section>

        <section class="removed-panel" id="removedPanel" aria-labelledby="removedHeading" hidden>
            <div class="removed-heading">
                <h2 id="removedHeading">Recently removed</h2>
                <span>Removed from your cart</span>
            </div>
            <div class="removed-items" id="removedItems"></div>
        </section>
    </main>
    <script src="{{ asset('js/cart.js') }}?v={{ filemtime(public_path('js/cart.js')) }}"></script>
    <script src="{{ asset('js/account-menu.js') }}?v={{ filemtime(public_path('js/account-menu.js')) }}"></script>
</body>
</html>
