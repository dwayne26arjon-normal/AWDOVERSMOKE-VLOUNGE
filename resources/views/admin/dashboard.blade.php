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

    <section class="sales-section" aria-labelledby="salesHeading">
        <div class="sales-heading">
            <div>
                <h2 id="salesHeading">Sales performance</h2>
                <p>Order totals excluding cancelled orders. Cash-on-delivery collections are not tracked here.</p>
            </div>
            <a class="table-action" href="{{ route('admin.orders.index') }}">View orders</a>
        </div>
        <form class="sales-filter" method="GET" action="{{ route('admin.dashboard') }}">
            <label for="salesPeriod">Show sales by</label>
            <select id="salesPeriod" name="sales_period">
                <option value="day" @selected($salesPeriod === 'day')>Day</option>
                <option value="month" @selected($salesPeriod === 'month')>Month</option>
                <option value="year" @selected($salesPeriod === 'year')>Year</option>
            </select>
            <input
                id="salesValue"
                name="sales_value"
                type="{{ ['day' => 'date', 'month' => 'month', 'year' => 'number'][$salesPeriod] }}"
                value="{{ $salesValue }}"
                @if ($salesPeriod === 'year') min="2000" max="{{ now()->year }}" @endif
                data-current-date="{{ now()->format('Y-m-d') }}"
                data-current-month="{{ now()->format('Y-m') }}"
                data-current-year="{{ now()->format('Y') }}"
                aria-label="Select sales {{ $salesPeriod }}"
                required
            >
            <button type="submit" class="sales-filter-button">Apply filter</button>
        </form>
        <div class="selected-sales">
            <div>
                <span class="selected-sales-label">Sales for {{ $selectedPeriodLabel }}</span>
                <strong>₱{{ number_format((float) $selectedSales, 2) }}</strong>
            </div>
            <span class="selected-sales-orders">{{ number_format($selectedOrderCount) }} {{ \Illuminate\Support\Str::plural('order', $selectedOrderCount) }}</span>
        </div>
        <div class="sales-summary">
            <article class="sales-stat">
                <span>Today's sales</span>
                <strong>₱{{ number_format($salesToday, 2) }}</strong>
            </article>
            <article class="sales-stat">
                <span>This month's sales</span>
                <strong>₱{{ number_format($salesThisMonth, 2) }}</strong>
            </article>
            <article class="sales-stat">
                <span>This year's sales</span>
                <strong>₱{{ number_format((float) $salesThisYear, 2) }}</strong>
            </article>
        </div>
        <div class="sales-chart-heading">
            <div>
                <h3>{{ $salesChartTitle }}</h3>
                <span>{{ $salesChartDescription }}</span>
            </div>
            <span class="sales-chart-legend"><i aria-hidden="true"></i> Sales</span>
        </div>
        <div class="sales-chart sales-chart-{{ $salesPeriod }}" role="list" aria-label="{{ $salesChartDescription }}">
            @foreach ($salesChart as $slot)
                <div class="sales-row" role="listitem" title="{{ $slot['date'] }}">
                    <span class="sales-row-label">{{ $slot['label'] }}</span>
                    <div class="sales-bar-track" role="img" aria-label="₱{{ number_format($slot['sales'], 2) }} sales">
                        <span class="sales-bar" style="width: {{ $slot['sales'] > 0 ? max(1, $slot['sales'] / $maxChartSales * 100) : 0 }}%"></span>
                    </div>
                    <span class="sales-row-value">₱{{ number_format($slot['sales'], 2) }}</span>
                    <span class="sales-row-orders">{{ $slot['orders'] }} {{ \Illuminate\Support\Str::plural('order', $slot['orders']) }}</span>
                </div>
            @endforeach
        </div>
        <p class="sales-chart-note">Longer bars mean higher sales. Cancelled orders are not included.</p>
        <div class="category-sales">
            <div class="category-sales-heading">
                <div>
                    <h3>Sales by product category</h3>
                    <p>For {{ $selectedPeriodLabel }} · cancelled orders excluded</p>
                </div>
                <span>Category · Units · Sales</span>
            </div>
            @if ($categorySales->isEmpty())
                <p class="category-sales-empty">No product sales recorded for this period yet.</p>
            @else
                <div class="category-sales-list">
                    @foreach ($categorySales as $category)
                        <div class="category-sales-row">
                            <span class="category-sales-name">{{ $category->category_name }}</span>
                            <span class="category-sales-units">{{ number_format((int) $category->units_sold) }} {{ \Illuminate\Support\Str::plural('unit', (int) $category->units_sold) }}</span>
                            <div class="category-sales-track" role="img" aria-label="{{ $category->category_name }} sales: ₱{{ number_format((float) $category->sales_total, 2) }}">
                                <span style="width: {{ max(1, (float) $category->sales_total / $maxCategorySales * 100) }}%"></span>
                            </div>
                            <strong class="category-sales-amount">₱{{ number_format((float) $category->sales_total, 2) }}</strong>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="popular-categories">
            <div class="category-sales-heading">
                <div>
                    <h3>Most purchased categories</h3>
                    <p>Based on units in orders for {{ $selectedPeriodLabel }} · cancelled orders excluded</p>
                </div>
            </div>
            @if ($totalUnitsSold === 0)
                <p class="category-sales-empty">No item purchases recorded for this period yet.</p>
            @else
                <div class="popular-categories-content">
                    <div class="donut-chart-panel">
                        <h4>Units by category</h4>
                        <div class="category-donut" role="img" aria-label="Category share of {{ $totalUnitsSold }} units purchased"
                             style="background: conic-gradient(from -90deg, @foreach ($categoryPurchases as $category) {{ $category['color'] }} {{ number_format($category['start'], 2, '.', '') }}% {{ number_format($category['end'], 2, '.', '') }}%{{ $loop->last ? '' : ',' }} @endforeach);">
                            <div class="category-donut-center">
                                <strong>{{ number_format($totalUnitsSold) }}</strong>
                                <span>units sold</span>
                            </div>
                        </div>
                        <div class="popular-category-legend">
                            @foreach ($categoryPurchases as $category)
                                <div class="popular-category-item">
                                    <span class="popular-category-key" style="background: {{ $category['color'] }}"></span>
                                    <span class="popular-category-name">{{ $category['name'] }}</span>
                                    <strong>{{ number_format($category['units']) }}</strong>
                                    <span class="popular-category-share">{{ number_format($category['share'], 1) }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="top-products-chart">
                        <h4>Top products by units sold</h4>
                        @foreach ($topPurchasedProducts as $product)
                            <div class="top-product-row">
                                <div class="top-product-copy">
                                    <strong>{{ $product->product_name }}</strong>
                                    <span>Brand: {{ $product->brand ?: 'Not recorded' }}</span>
                                </div>
                                <div class="top-product-bar-track" role="img" aria-label="{{ $product->product_name }}, brand {{ $product->brand ?: 'not recorded' }}: {{ number_format((int) $product->units_sold) }} units sold">
                                    <span style="width: {{ max(1, (int) $product->units_sold / $maxProductUnitsSold * 100) }}%"></span>
                                </div>
                                <strong class="top-product-units">{{ number_format((int) $product->units_sold) }} <small>units</small></strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
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
                                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="">
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
        .sales-section { margin-top: 15px; padding: 16px; border: 1px solid #2b2634; border-radius: 7px; background: linear-gradient(145deg, #191622, #121018); }
        .sales-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid #2b2634; }
        .sales-heading h2 { margin: 0; font-size: 13px; }
        .sales-heading p { margin: 5px 0 0; color: #817b8a; font-size: 9px; }
        .sales-filter { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 12px; }
        .sales-filter label { color: #aaa3b4; font-size: 9px; }
        .sales-filter select, .sales-filter input { min-height: 32px; padding: 0 9px; border: 1px solid #393244; border-radius: 5px; color: #eee9f5; background: #14111b; font: inherit; font-size: 10px; }
        .sales-filter input { color-scheme: dark; }
        .sales-filter-button { min-height: 32px; padding: 0 12px; border: 1px solid #7655df; border-radius: 5px; color: #fff; background: #6232e8; font: inherit; font-size: 10px; cursor: pointer; }
        .sales-filter-button:hover { background: #7655f2; }
        .selected-sales { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 10px; padding: 12px; border: 1px solid rgba(124,92,240,.25); border-radius: 6px; background: rgba(124,92,240,.07); }
        .selected-sales > div { display: grid; gap: 4px; }
        .selected-sales-label, .selected-sales-orders { color: #aaa3b4; font-size: 9px; }
        .selected-sales strong { color: #d7ceff; font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 500; }
        .sales-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; padding: 12px 0; }
        .sales-stat { display: grid; gap: 4px; }
        .sales-stat span { color: #938d9d; font-size: 9px; }
        .sales-stat strong { overflow: hidden; color: #eee9ff; font-family: 'Outfit', sans-serif; font-size: clamp(16px, 2vw, 22px); font-weight: 500; text-overflow: ellipsis; white-space: nowrap; }
        .sales-chart { display: grid; gap: 10px; margin-top: 12px; padding: 14px; border: 1px solid #2b2634; border-radius: 6px; background: rgba(8,7,12,.24); }
        .sales-row { display: grid; grid-template-columns: minmax(105px, 150px) minmax(50px, 1fr) minmax(85px, 110px) minmax(48px, 70px); align-items: center; gap: 12px; min-height: 20px; }
        .sales-row-label { overflow: hidden; color: #d3ccdf; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
        .sales-bar-track { height: 12px; overflow: hidden; border-radius: 999px; background: #292431; }
        .sales-bar { display: block; height: 100%; min-width: 0; border-radius: inherit; background: linear-gradient(90deg, #7045ec, #a78bfa); transition: width .25s ease; }
        .sales-row-value { color: #f0ebff; font-size: 10px; font-weight: 600; text-align: right; white-space: nowrap; }
        .sales-row-orders { color: #8d8698; font-size: 9px; text-align: right; white-space: nowrap; }
        .sales-chart-heading { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 12px; }
        .sales-chart-heading h3 { margin: 0; color: #e2dcef; font-size: 11px; font-weight: 600; }
        .sales-chart-heading span { color: #8d8698; font-size: 9px; }
        .sales-chart-legend { display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; }
        .sales-chart-legend i { width: 8px; height: 8px; border-radius: 2px; background: #8b6cff; }
        .sales-chart-note { margin: 0; color: #777181; font-size: 9px; }
        .category-sales { margin-top: 16px; padding-top: 14px; border-top: 1px solid #2b2634; }
        .category-sales-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .category-sales-heading h3 { margin: 0; color: #e2dcef; font-size: 11px; font-weight: 600; }
        .category-sales-heading p, .category-sales-heading > span { margin: 4px 0 0; color: #8d8698; font-size: 9px; }
        .category-sales-heading > span { text-align: right; }
        .category-sales-list { display: grid; gap: 9px; margin-top: 12px; }
        .category-sales-row { display: grid; grid-template-columns: minmax(100px, 1fr) minmax(55px, 90px) minmax(70px, 1.4fr) minmax(85px, 110px); align-items: center; gap: 12px; min-height: 22px; }
        .category-sales-name { overflow: hidden; color: #ddd7e8; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
        .category-sales-units { color: #9992a4; font-size: 9px; white-space: nowrap; }
        .category-sales-track { height: 10px; overflow: hidden; border-radius: 999px; background: #292431; }
        .category-sales-track span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #287d9b, #5bc4c8); }
        .category-sales-amount { color: #e5f8f6; font-size: 10px; font-weight: 600; text-align: right; white-space: nowrap; }
        .category-sales-empty { margin: 12px 0 2px; color: #8d8698; font-size: 10px; }
        .popular-categories { margin-top: 16px; padding-top: 14px; border-top: 1px solid #2b2634; }
        .popular-categories-content { display: grid; grid-template-columns: minmax(220px, .85fr) minmax(300px, 1.15fr); align-items: start; gap: 24px; margin-top: 14px; }
        .donut-chart-panel, .top-products-chart { min-width: 0; }
        .donut-chart-panel h4, .top-products-chart h4 { margin: 0 0 12px; color: #aaa3b4; font-size: 9px; font-weight: 600; }
        .category-donut { display: grid; width: 140px; height: 140px; flex: 0 0 140px; place-items: center; border-radius: 50%; }
        .category-donut-center { display: grid; width: 88px; height: 88px; align-content: center; justify-items: center; border-radius: 50%; background: #15121c; }
        .category-donut-center strong { color: #f0ebff; font-family: 'Outfit', sans-serif; font-size: 23px; font-weight: 500; line-height: 1.1; }
        .category-donut-center span { margin-top: 4px; color: #9992a4; font-size: 9px; }
        .popular-category-legend { display: grid; width: min(320px, 100%); gap: 8px; margin-top: 12px; }
        .popular-category-item { display: grid; grid-template-columns: 10px minmax(0, 1fr) 40px 48px; align-items: center; gap: 9px; min-height: 24px; }
        .popular-category-key { width: 9px; height: 9px; border-radius: 3px; }
        .popular-category-name { overflow: hidden; color: #d3ccdf; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
        .popular-category-item strong { color: #eee9ff; font-size: 10px; text-align: right; }
        .popular-category-share { color: #9992a4; font-size: 9px; text-align: right; }
        .top-products-chart { padding-left: 20px; border-left: 1px solid #2b2634; }
        .top-product-row { display: grid; grid-template-columns: minmax(105px, 1fr) minmax(70px, 1.2fr) minmax(54px, 70px); align-items: center; gap: 10px; margin-top: 13px; }
        .top-product-copy { display: grid; min-width: 0; gap: 3px; }
        .top-product-copy strong { overflow: hidden; color: #e8e2f0; font-size: 9px; text-overflow: ellipsis; white-space: nowrap; }
        .top-product-copy span { overflow: hidden; color: #8d8698; font-size: 8px; text-overflow: ellipsis; white-space: nowrap; }
        .top-product-bar-track { height: 12px; overflow: hidden; border-radius: 999px; background: #292431; }
        .top-product-bar-track span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #287d9b, #5bc4c8); }
        .top-product-units { color: #e5f8f6; font-size: 9px; text-align: right; white-space: nowrap; }
        .top-product-units small { color: #8d8698; font-size: 8px; font-weight: 400; }
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
        @media (max-width: 900px) { .popular-categories-content { grid-template-columns: 1fr; }.top-products-chart { padding: 14px 0 0; border-top: 1px solid #2b2634; border-left: 0; }.donut-chart-panel { display: grid; grid-template-columns: 150px minmax(0, 1fr); align-items: center; column-gap: 16px; }.donut-chart-panel h4 { grid-column: 1 / -1; }.popular-category-legend { margin-top: 0; } }
        @media (max-width: 600px) { .sales-chart { gap: 9px; padding: 12px 9px; }.sales-row { grid-template-columns: minmax(76px, 105px) minmax(35px, 1fr) minmax(72px, 90px); gap: 7px; }.sales-row-label, .sales-row-value { font-size: 9px; }.sales-row-orders { display: none; }.category-sales-row { grid-template-columns: minmax(75px, 1fr) minmax(45px, 65px) minmax(65px, 90px); gap: 7px; }.category-sales-track { display: none; }.category-sales-name, .category-sales-amount { font-size: 9px; }.category-sales-units { font-size: 8px; }.category-donut { width: 120px; height: 120px; }.category-donut-center { width: 76px; height: 76px; }.category-donut-center strong { font-size: 20px; }.popular-category-item { grid-template-columns: 9px minmax(0, 1fr) 32px 42px; gap: 6px; }.top-product-row { grid-template-columns: minmax(90px, 1fr) minmax(45px, 1.2fr) minmax(48px, 58px); gap: 7px; } }
        @media (max-width: 420px) { .overview-card { min-height: 88px; padding: 10px; }.overview-label { font-size: 9px; }.overview-note { font-size: 8px; }.sales-section { padding: 12px; }.sales-summary { gap: 6px; }.sales-stat strong { font-size: 14px; }.sales-row { grid-template-columns: minmax(70px, 82px) minmax(30px, 1fr) minmax(67px, 78px); gap: 6px; }.sales-row-label, .sales-row-value { font-size: 8px; }.sales-filter { align-items: stretch; }.sales-filter label { width: 100%; }.sales-filter select, .sales-filter input { flex: 1 1 90px; min-width: 0; }.sales-filter-button { flex: 1 1 100%; }.category-sales-heading > span { display: none; }.category-sales-row { grid-template-columns: minmax(65px, 1fr) minmax(40px, 55px) minmax(62px, 70px); gap: 5px; }.category-sales-name, .category-sales-amount { font-size: 8px; }.donut-chart-panel { grid-template-columns: 1fr; justify-items: center; }.donut-chart-panel h4 { justify-self: start; width: 100%; }.category-donut { width: 128px; height: 128px; flex-basis: 128px; }.category-donut-center { width: 80px; height: 80px; }.popular-category-legend { margin-top: 12px; }.top-product-row { grid-template-columns: minmax(75px, 1fr) minmax(35px, .8fr) minmax(45px, 52px); gap: 5px; } }
    </style>
@endpush

@push('scripts')
    <script>
        const salesPeriod = document.getElementById('salesPeriod');
        const salesValue = document.getElementById('salesValue');
        const salesInputTypes = { day: 'date', month: 'month', year: 'number' };
        const salesValueDefaults = {
            day: salesValue.dataset.currentDate,
            month: salesValue.dataset.currentMonth,
            year: salesValue.dataset.currentYear
        };

        salesPeriod.addEventListener('change', () => {
            salesValue.type = salesInputTypes[salesPeriod.value];
            salesValue.value = salesValueDefaults[salesPeriod.value];
            salesValue.setAttribute('aria-label', `Select sales ${salesPeriod.value}`);
            if (salesPeriod.value === 'year') {
                salesValue.min = '2000';
                salesValue.max = salesValue.dataset.currentYear;
            } else {
                salesValue.removeAttribute('min');
                salesValue.removeAttribute('max');
            }
        });
    </script>
@endpush
