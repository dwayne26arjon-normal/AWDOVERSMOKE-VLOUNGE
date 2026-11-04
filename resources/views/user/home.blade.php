<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home | OversmokeVlounge</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ filemtime(public_path('css/home.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/account-menu.css') }}?v={{ filemtime(public_path('css/account-menu.css')) }}">
</head>
<body>

<div class="smoke a"></div>
<div class="smoke b"></div>

<div class="layout">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="sidebar">
        <div class="logo">
            <img src="{{ asset('images/oversmokev-mascot.png') }}" alt="" onerror="this.style.display='none'">
            <span>OVERSMOKE</span>
        </div>

        <div class="filters-head">
            <span>FILTERS</span>
            <button class="clear" id="clearFilters">Clear All</button>
        </div>

        <details class="filter-drawer" open>
            <summary>
                <span>Refine products</span>
                <span class="filter-drawer-hint">Category, brand and price</span>
            </summary>
            <div class="filter-groups">
                <div class="group">
                    <h4>Categories</h4>
                    @foreach ($categories as $category)
                        <label class="check"><input type="checkbox" class="f-cat" value="{{ $category }}"> {{ $category }}</label>
                    @endforeach
                </div>

                <div class="group">
                    <h4>Brand</h4>
                    <div class="brand-search"><input type="text" id="brandSearch" placeholder="Search Brand.."></div>
                    @foreach ($brands as $brand)
                        <label class="check brand-item{{ $loop->index >= 5 ? ' extra-brand' : '' }}"><input type="checkbox" class="f-brand" value="{{ $brand }}"> {{ $brand }}</label>
                    @endforeach
                    @if ($brands->count() > 5)
                        <button class="show-more" id="showMore" type="button" aria-expanded="false">Show More ⌄</button>
                    @else
                        <button class="show-more" id="showMore" type="button" aria-expanded="false" hidden>Show More ⌄</button>
                    @endif
                </div>

                <div class="group">
                    <h4>Price Range</h4>
                    <div class="price-row">
                        <div><label for="minPrice">Min. Price</label><input type="number" id="minPrice" min="0" step="1" value="0"></div>
                        <div><label for="maxPrice">Max. Price</label><input type="number" id="maxPrice" min="0" step="1" value="{{ (int) ceil($maximumPrice) }}" data-default="{{ (int) ceil($maximumPrice) }}"></div>
                    </div>
                </div>
            </div>
        </details>
    </aside>

    {{-- ===== MAIN ===== --}}
    <div class="main">
        <header class="topbar">
            <nav class="nav">
                <a href="{{ url('/home') }}" class="active">Home</a>
                <a href="{{ route('orders') }}">Orders</a>
                <a href="{{ route('cart') }}">Cart</a>
            </nav>

            <div class="search">
                <input type="text" id="search" placeholder="Search..">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            </div>

            <div class="top-actions">
                <a href="{{ route('cart') }}" class="cart" aria-label="Cart">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                    <span class="cart-count" id="cartCount">0</span>
                </a>
                <x-user.account-menu />
            </div>
        </header>

        <section class="banner" style="background-image: linear-gradient(90deg, rgba(10,6,30,.85), rgba(10,6,30,.2)), url('{{ asset('images/banner.png') }}');">
            <small>New Arrivals</small>
            <h2>Featured</h2>
            <p>Discover Sorted and Quality Products.</p>
            <a href="#products" class="shop">Shop Now →</a>
        </section>

        {{-- Ito ang nag-i-scroll --}}
        <main class="content" id="products">
            <div class="toolbar">
                <select class="sort" id="sort">
                    <option value="pop">Sort by: Popularity</option>
                    <option value="low">Price: Low to High</option>
                    <option value="high">Price: High to Low</option>
                    <option value="az">Name: A to Z</option>
                </select>
                <button class="view-btn active" id="gridBtn" aria-label="Grid view">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                </button>
                <button class="view-btn" id="listBtn" aria-label="List view">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                </button>
            </div>

            <div class="grid" id="grid">
                @foreach ($products as $i => $product)
                    <article class="card"
                             data-id="{{ $product->id }}"
                             data-index="{{ $i }}"
                             data-name="{{ $product->name }}"
                             data-brand="{{ $product->brand }}"
                             data-category="{{ $product->category }}"
                             data-price="{{ $product->price }}">
                        <div class="img">
                            @if ($product->stock === 0)
                                <span class="badge">Out of stock</span>
                            @elseif ($product->stock <= 10)
                                <span class="badge">Low stock</span>
                            @endif
                            @if ($product->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}">
                            @else
                                <div class="placeholder"></div>
                            @endif
                        </div>
                        <div class="info">
                            <h3><a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a></h3>
                            <div class="brand">{{ $product->brand }}</div>
                            <div class="price">₱ {{ number_format((float) $product->price) }}</div>
                            <a class="add" href="{{ route('products.show', $product->id) }}">🛒 View details</a>
                        </div>
                    </article>
                @endforeach
            </div>
            <p class="empty" id="empty">No products found.</p>

            <div class="marquee">
                <div class="marquee-track">
                    <span>✦ AUTHENTIC PRODUCTS</span><span>✦ FAST DELIVERY</span><span>✦ BEST PRICES</span><span>✦ OVERSMOKE LOUNGE</span>
                    <span>✦ AUTHENTIC PRODUCTS</span><span>✦ FAST DELIVERY</span><span>✦ BEST PRICES</span><span>✦ OVERSMOKE LOUNGE</span>
                </div>
            </div>

            <section class="features">
                <div class="feature"><div class="ico">💨</div><h3>100% Authentic</h3><p>Original na products mula sa trusted brands.</p></div>
                <div class="feature"><div class="ico">🚀</div><h3>Fast Delivery</h3><p>Mabilis na pag-ship, diretso sa pinto mo.</p></div>
                <div class="feature"><div class="ico">💜</div><h3>Best Price</h3><p>Sulit na presyo, may mga promo pa.</p></div>
            </section>

            <footer class="footer">
                © {{ date('Y') }} <b>OVERSMOKE LOUNGE</b>. All rights reserved.
            </footer>
        </main>
    </div>
</div>

<script src="{{ asset('js/home.js') }}?v={{ filemtime(public_path('js/home.js')) }}"></script>
<script src="{{ asset('js/account-menu.js') }}?v={{ filemtime(public_path('js/account-menu.js')) }}"></script>
</body>
</html>