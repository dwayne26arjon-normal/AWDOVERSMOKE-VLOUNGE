<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }} | OversmokeVlounge</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/product-details.css') }}?v={{ filemtime(public_path('css/product-details.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/account-menu.css') }}?v={{ filemtime(public_path('css/account-menu.css')) }}">
</head>
<body>
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
        <a class="back-link" href="{{ url('/home') }}">← <span>Back to shop</span></a>
        <p class="eyebrow">PRODUCT DETAILS</p>
        <section class="product-panel" id="product"
                 data-id="{{ $product->id }}"
                 data-name="{{ $product->name }}"
                 data-brand="{{ $product->brand }}"
                 data-price="{{ $product->price }}"
                 data-image="{{ $product->image_path ? asset('storage/' . $product->image_path) : '' }}">
            <div class="product-visual" id="productImagePreview">
                @if ($product->image_path)
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}">
                    <span class="image-preview-hint">Move your cursor to preview</span>
                @else
                    <div class="device-placeholder" aria-hidden="true"></div>
                @endif
            </div>

            <div class="product-info">
                @if ($product->stock === 0)
                    <span class="badge">Out of stock</span>
                @elseif ($product->stock <= 10)
                    <span class="badge">Low stock</span>
                @endif
                <p class="category">{{ $product->category }}</p>
                <h1>{{ $product->name }}</h1>
                <p class="brand">{{ $product->brand }}</p>
                <p class="price">₱ <span id="unitPrice">{{ number_format((float) $product->price) }}</span></p>
                <p class="description">{{ $product->description ?: 'A quality ' . strtolower($product->category) . ' product' . ($product->brand ? ' from ' . $product->brand : '') . '. Choose a color and quantity before adding it to your cart.' }}</p>

                <fieldset class="color-picker">
                    <legend>Color <span id="selectedColor">Black</span></legend>
                    <div class="color-options">
                        @foreach (['Black', 'Blue', 'Yellow', 'Red', 'Gray', 'White', 'Green', 'Violet'] as $color)
                            <button type="button" class="color-option{{ $loop->first ? ' selected' : '' }}" data-color="{{ $color }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $color }}</button>
                        @endforeach
                    </div>
                </fieldset>

                <div class="purchase-row">
                    <div class="quantity-control">
                        <span>Quantity</span>
                        <div class="quantity-input">
                            <button type="button" id="decreaseQuantity" aria-label="Decrease quantity">−</button>
                            <output id="quantity" aria-live="polite">1</output>
                            <button type="button" id="increaseQuantity" aria-label="Increase quantity">+</button>
                        </div>
                    </div>
                    <p class="subtotal">Subtotal <strong>₱ <span id="subtotal">{{ number_format((float) $product->price) }}</span></strong></p>
                </div>

                <div class="actions">
                    <button type="button" class="add-to-cart" id="addToCart">Add to Cart</button>
                    <button type="button" class="buy-now" id="buyNow">Buy Now</button>
                </div>
                <p class="status-message" id="statusMessage" role="status" aria-live="polite"></p>
            </div>
        </section>
    </main>
    <dialog class="confirm-dialog" id="buyNowDialog" aria-labelledby="confirmTitle" aria-describedby="confirmMessage">
        <div class="confirm-icon" aria-hidden="true">?</div>
        <p class="confirm-eyebrow">READY TO CHECK OUT?</p>
        <h2 id="confirmTitle">Confirm your purchase</h2>
        <p id="confirmMessage"></p>
        <div class="confirm-actions">
            <button type="button" class="confirm-cancel" id="cancelBuyNow">Cancel</button>
            <button type="button" class="confirm-proceed" id="confirmBuyNow">Continue to checkout</button>
        </div>
    </dialog>
    <script src="{{ asset('js/product-details.js') }}?v={{ filemtime(public_path('js/product-details.js')) }}"></script>
    <script src="{{ asset('js/account-menu.js') }}?v={{ filemtime(public_path('js/account-menu.js')) }}"></script>
</body>
</html>
