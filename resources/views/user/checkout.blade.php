<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout | OversmokeVlounge</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/checkout.css') }}?v={{ filemtime(public_path('css/checkout.css')) }}">
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
            <a href="{{ route('cart') }}">Cart</a>
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
        <a class="back-link" href="{{ route('cart') }}">← <span>Back to cart</span></a>
        <h1>Order Summary</h1>

        <section class="address-panel" aria-labelledby="deliveryHeading">
            <div class="address-icon" aria-hidden="true">⌖</div>
            <div class="address-content">
                <h2 id="deliveryHeading">Delivery details</h2>
                <p class="account-name">{{ auth()->user()->name }}</p>
                <p class="account-email">{{ auth()->user()->email }}</p>
                <p class="address-preview" id="addressPreview">{{ auth()->user()->phone && auth()->user()->address ? auth()->user()->phone . ' · ' . auth()->user()->address : 'Add your phone number and delivery address.' }}</p>
            </div>
            <button type="button" class="edit-button" id="editDetails">Edit</button>
        </section>

        <form id="checkoutForm" class="details-form" hidden>
            <div class="field">
                <label for="customerName">Full name</label>
                <input id="customerName" name="customerName" type="text" value="{{ auth()->user()->name }}" autocomplete="name" required maxlength="120">
            </div>
            <div class="field">
                <label for="phone">Phone number</label>
                <input id="phone" name="phone" type="tel" value="{{ auth()->user()->phone }}" autocomplete="tel" required maxlength="30" placeholder="e.g. 09XXXXXXXXX">
            </div>
            <div class="field field-wide">
                <label for="address">Delivery address</label>
                <textarea id="address" name="address" autocomplete="street-address" rows="3" required maxlength="500" placeholder="House / unit, street, barangay, city, province, postal code">{{ auth()->user()->address }}</textarea>
            </div>
            <div class="field">
                <label for="paymentMethod">Payment method</label>
                <select id="paymentMethod" name="paymentMethod">
                    <option value="Cash on delivery">Cash on delivery</option>
                </select>
            </div>
            <p class="form-actions"><button type="submit">Save delivery details</button></p>
        </form>

        <section class="order-panel" aria-labelledby="itemsHeading">
            <h2 id="itemsHeading">Items to purchase</h2>
            <div class="order-items" id="orderItems"></div>
            <p class="empty-state" id="emptyState" hidden>No items selected for checkout. <a href="{{ route('cart') }}">Return to cart</a></p>
            <div class="order-total">
                <span>Total price</span>
                <strong>₱ <span id="orderTotal">0</span></strong>
            </div>
        </section>

        <p class="notice" id="checkoutMessage" role="status" aria-live="polite">
            Confirming creates an order in your account. No online payment is processed; payment is cash on delivery.
        </p>
        <div class="page-actions">
            <a class="cancel-button" href="{{ route('cart') }}">Cancel</a>
            <button class="confirm-button" id="confirmPurchase" type="button" disabled>Confirm Purchase</button>
        </div>
    </main>
    <section class="success-splash" id="successSplash" role="status" aria-live="polite" hidden>
        <div class="success-card">
            <div class="success-check" aria-hidden="true">✓</div>
            <p class="success-eyebrow">ORDER CONFIRMED</p>
            <h2>Successfully purchased!</h2>
            <p>Your order <strong id="successOrderId"></strong> has been placed.</p>
            <p class="success-note">Your order is saved to your account. Payment is cash on delivery.</p>
            <a href="{{ route('orders') }}">View your orders</a>
        </div>
    </section>
    <script src="{{ asset('js/checkout.js') }}?v={{ filemtime(public_path('js/checkout.js')) }}"></script>
    <script src="{{ asset('js/account-menu.js') }}?v={{ filemtime(public_path('js/account-menu.js')) }}"></script>
</body>
</html>
