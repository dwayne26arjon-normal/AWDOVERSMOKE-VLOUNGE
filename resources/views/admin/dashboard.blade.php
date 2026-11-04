@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Dashboard</h1>
            <p>Your store overview at a glance.</p>
        </div>
        <a class="primary-button" href="{{ route('admin.products.create') }}">+ Add product</a>
    </div>

    <section class="overview-grid" aria-label="Store overview">
        <article class="overview-card">
            <span class="overview-label">Total products</span>
            <strong>{{ number_format($productCount) }}</strong>
            <span class="overview-note">Products in your catalog</span>
        </article>
        <article class="overview-card">
            <span class="overview-label">Stock units</span>
            <strong>{{ number_format($inventoryUnits) }}</strong>
            <span class="overview-note">Units currently on hand</span>
        </article>
        <article class="overview-card">
            <span class="overview-label">Low stock products</span>
            <strong class="number-warning">{{ number_format($lowStockCount) }}</strong>
            <span class="overview-note">Products with 1–10 units</span>
        </article>
        <article class="overview-card">
            <span class="overview-label">Out of stock</span>
            <strong class="number-danger">{{ number_format($outOfStockCount) }}</strong>
            <span class="overview-note">Products needing restock</span>
        </article>
    </section>

    <section class="dashboard-panels">
        <article class="panel dashboard-panel recent-products-panel">
            <div class="panel-heading">
                <div>
                    <h2 class="panel-title">Recently added products</h2>
                    <p class="panel-subtitle">Your latest catalog updates</p>
                </div>
                <a class="table-action" href="{{ route('admin.products.index') }}">View all</a>
            </div>

            @if ($recentProducts->isEmpty())
                <div class="empty-state">Your product catalog is empty. Add your first product to get started.</div>
            @else
                @php($maxRecentStock = max(1, (int) $recentProducts->max('stock')))
                <div class="dashboard-product-list">
                    @foreach ($recentProducts as $product)
                        <a class="dashboard-product" href="{{ route('admin.products.edit', $product) }}">
                            <span class="dashboard-product-image">
                                @if ($product->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="">
                                @else
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}
                                @endif
                            </span>
                            <span class="dashboard-product-info">
                                <span class="product-name">{{ $product->name }}</span>
                                <span class="product-meta">{{ $product->brand ?: $product->category }}</span>
                                <span class="stock-track"><span style="width: {{ min(100, (int) round($product->stock / $maxRecentStock * 100)) }}%"></span></span>
                            </span>
                            <span class="dashboard-product-stock">{{ number_format($product->stock) }} <small>in stock</small></span>
                        </a>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel dashboard-panel stock-report-panel">
            <div class="panel-heading">
                <div>
                    <h2 class="panel-title">Inventory report</h2>
                    <p class="panel-subtitle">Current stock and retail value</p>
                </div>
                <a class="table-action" href="{{ route('admin.inventory.index') }}">Inventory</a>
            </div>

            <div class="inventory-total">{{ number_format($inventoryUnits) }}</div>
            <div class="inventory-total-label">total units in inventory</div>
            <div class="inventory-value-row">
                <span>Estimated inventory value</span>
                <strong>₱{{ number_format((float) $inventoryValue, 2) }}</strong>
            </div>

            <div class="stock-attention-heading">
                <h3>Stock attention</h3>
                <a class="table-action" href="{{ route('admin.inventory.index', ['stock' => 'low']) }}">View low stock</a>
            </div>
            @if ($lowStockProducts->isEmpty())
                <p class="stock-clear">Everything is stocked. No products need attention.</p>
            @else
                <div class="stock-attention-list">
                    @foreach ($lowStockProducts as $product)
                        <a class="stock-attention-item" href="{{ route('admin.products.edit', $product) }}">
                            <span class="product-name">{{ $product->name }}</span>
                            <span class="badge {{ $product->stock === 0 ? 'danger' : 'warning' }}">{{ $product->stock === 0 ? 'Out of stock' : $product->stock . ' left' }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </article>
    </section>
@endsection

@push('styles')
    <style>
        .page-content { max-width: 1440px; }
        .page-heading { align-items: center; margin-bottom: 22px; }
        .page-heading h1 { font-size: 21px; }
        .overview-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .overview-card { display: flex; min-height: 100px; flex-direction: column; align-items: center; justify-content: center; padding: 14px; border: 1px solid #2b2634; border-radius: 7px; background: linear-gradient(145deg, #191622, #121018); text-align: center; }
        .overview-label { color: #b7b1c0; font-size: 10px; }
        .overview-card strong { margin-top: 5px; font-family: 'Outfit', sans-serif; font-size: clamp(24px, 3vw, 36px); font-weight: 500; line-height: 1.15; }
        .overview-card .number-warning { color: #e8c35f; }
        .overview-card .number-danger { color: #ff7888; }
        .overview-note { margin-top: 5px; color: #777181; font-size: 9px; }
        .dashboard-panels { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(300px, .95fr); gap: 14px; margin-top: 15px; }
        .dashboard-panel { min-width: 0; padding: 16px; }
        .panel-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding-bottom: 11px; border-bottom: 1px solid #2b2634; }
        .panel-title { font-size: 12px; }
        .panel-subtitle { margin: 5px 0 0; color: #817b8a; font-size: 9px; }
        .dashboard-product-list { display: grid; }
        .dashboard-product { display: flex; min-height: 78px; align-items: center; gap: 11px; border-bottom: 1px solid rgba(43, 38, 52, .66); text-decoration: none; }
        .dashboard-product:last-child { border-bottom: 0; }
        .dashboard-product-image { display: grid; width: 58px; height: 58px; flex: 0 0 58px; place-items: center; overflow: hidden; border: 1px solid #322c3e; border-radius: 5px; background: #15121c; color: #8d829f; font-size: 15px; }
        .dashboard-product-image img { width: 100%; height: 100%; object-fit: cover; }
        .dashboard-product-info { min-width: 0; flex: 1; }
        .dashboard-product-info .product-name { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 10px; }
        .dashboard-product-info .product-meta { margin-top: 3px; font-size: 9px; }
        .stock-track { display: block; height: 6px; margin-top: 7px; overflow: hidden; border-radius: 8px; background: #272331; }
        .stock-track span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #6232e8, #0789ff); }
        .dashboard-product-stock { flex: 0 0 auto; color: #b8b2c1; font-size: 10px; text-align: right; }
        .dashboard-product-stock small { display: block; margin-top: 3px; color: #777181; font-size: 8px; }
        .inventory-total { margin-top: 17px; font-family: 'Outfit', sans-serif; font-size: 35px; font-weight: 500; text-align: center; }
        .inventory-total-label { margin-top: 2px; color: #817b8a; font-size: 9px; text-align: center; }
        .inventory-value-row { display: flex; justify-content: space-between; gap: 12px; margin-top: 15px; padding: 10px 0; border-top: 1px solid #2b2634; border-bottom: 1px solid #2b2634; color: #938d9d; font-size: 9px; }
        .inventory-value-row strong { color: #ece8f2; font-size: 10px; font-weight: 600; }
        .stock-attention-heading { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 13px; }
        .stock-attention-heading h3 { margin: 0; font-size: 10px; font-weight: 600; }
        .stock-attention-list { display: grid; margin-top: 6px; }
        .stock-attention-item { display: flex; min-height: 31px; align-items: center; justify-content: space-between; gap: 10px; border-top: 1px solid rgba(43, 38, 52, .55); text-decoration: none; }
        .stock-attention-item .product-name { overflow: hidden; font-size: 9px; text-overflow: ellipsis; white-space: nowrap; }
        .stock-attention-item .badge { flex: 0 0 auto; min-height: 17px; font-size: 8px; }
        .stock-clear { margin: 12px 0 2px; color: #8c8696; font-size: 9px; }
        @media (max-width: 900px) { .overview-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 700px) { .dashboard-panels { grid-template-columns: 1fr; } }
        @media (max-width: 420px) { .overview-card { min-height: 88px; padding: 10px; }.overview-label { font-size: 9px; }.overview-note { font-size: 8px; } }
    </style>
@endpush
