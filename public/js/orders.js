(function () {
    const ordersList = document.getElementById('ordersList');
    const emptyState = document.getElementById('emptyOrders');
    const formatter = new Intl.NumberFormat('en-PH');
    const visibleItemLimit = 3;
    const userId = document.body.dataset.userId;
    const ordersKey = `oversmoke-orders-user-${userId}`;
    const cartKey = `oversmoke-cart-user-${userId}`;

    function render() {
        const orders = JSON.parse(localStorage.getItem(ordersKey) || '[]');
        const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
        document.getElementById('cartCount').textContent = cart.length;
        ordersList.replaceChildren();
        emptyState.hidden = orders.length > 0;
        const orderCount = document.getElementById('orderCount');
        orderCount.textContent = `${orders.length} ${orders.length === 1 ? 'order' : 'orders'}`;

        orders.forEach(order => {
            const card = document.createElement('article');
            card.className = 'order-card';

            const header = document.createElement('header');
            header.className = 'order-header';
            const reference = document.createElement('div');
            reference.className = 'order-reference';
            const orderId = document.createElement('strong');
            orderId.textContent = order.id;
            const orderDate = document.createElement('span');
            orderDate.className = 'order-date';
            orderDate.textContent = new Date(order.placedAt).toLocaleString();
            reference.append(orderId, orderDate);
            const status = document.createElement('span');
            status.className = 'order-status';
            status.textContent = 'Confirmed';
            header.append(reference, status);

            const products = document.createElement('div');
            products.className = 'order-products';
            const orderItems = Array.isArray(order.items) ? order.items : [];
            const itemSummary = document.createElement('p');
            itemSummary.className = 'order-item-summary';
            const totalUnits = orderItems.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
            itemSummary.textContent = `${orderItems.length} ${orderItems.length === 1 ? 'product' : 'products'} · ${totalUnits} ${totalUnits === 1 ? 'item' : 'items'}`;
            products.append(itemSummary);

            orderItems.forEach((item, index) => {
                const row = document.createElement('div');
                row.className = 'order-product';
                if (orderItems.length > visibleItemLimit && index >= visibleItemLimit) {
                    row.hidden = true;
                }

                const thumbnail = document.createElement('div');
                thumbnail.className = 'product-thumbnail';
                thumbnail.setAttribute('aria-hidden', 'true');
                const filename = typeof item.image === 'string' ? item.image.split('/').pop() : '';
                if (filename && /^[a-zA-Z0-9._-]+$/.test(filename)) {
                    const image = document.createElement('img');
                    image.src = `/images/products/${filename}`;
                    image.alt = '';
                    image.addEventListener('error', () => {
                        image.remove();
                        thumbnail.classList.add('product-thumbnail-placeholder');
                        thumbnail.append(document.createElement('span'));
                    }, { once: true });
                    thumbnail.append(image);
                } else {
                    thumbnail.classList.add('product-thumbnail-placeholder');
                    thumbnail.append(document.createElement('span'));
                }

                const info = document.createElement('div');
                info.className = 'product-info';
                const name = document.createElement('strong');
                name.textContent = item.name;
                const variant = document.createElement('span');
                variant.textContent = `${item.brand} · ${item.color || 'Default'} · Qty ${item.quantity}`;
                info.append(name, variant);
                const product = document.createElement('div');
                product.className = 'order-product-info';
                product.append(thumbnail, info);
                const price = document.createElement('strong');
                price.className = 'product-total';
                price.textContent = `₱ ${formatter.format(item.price * item.quantity)}`;
                row.append(product, price);
                products.append(row);
            });

            if (orderItems.length > visibleItemLimit) {
                const toggleItems = document.createElement('button');
                toggleItems.type = 'button';
                toggleItems.className = 'toggle-order-items';
                toggleItems.setAttribute('aria-expanded', 'false');
                const hiddenCount = orderItems.length - visibleItemLimit;
                toggleItems.textContent = `Show ${hiddenCount} more ${hiddenCount === 1 ? 'product' : 'products'}`;
                toggleItems.addEventListener('click', () => {
                    const expanded = toggleItems.getAttribute('aria-expanded') === 'true';
                    products.querySelectorAll('.order-product').forEach((row, index) => {
                        if (index >= visibleItemLimit) row.hidden = expanded;
                    });
                    toggleItems.setAttribute('aria-expanded', String(!expanded));
                    toggleItems.textContent = expanded ? `Show ${hiddenCount} more ${hiddenCount === 1 ? 'product' : 'products'}` : 'Show fewer products';
                });
                products.append(toggleItems);
            }

            const footer = document.createElement('footer');
            footer.className = 'order-footer';
            const delivery = document.createElement('span');
            delivery.className = 'delivery-info';
            delivery.textContent = `${order.delivery.name} · ${order.delivery.phone} · ${order.delivery.address} · ${order.delivery.paymentMethod}`;
            const total = document.createElement('strong');
            total.className = 'order-total';
            total.textContent = `Total ₱ ${formatter.format(order.total)}`;
            footer.append(delivery, total);

            card.append(header, products, footer);
            ordersList.append(card);
        });
    }

    render();
    window.addEventListener('pageshow', render);
    window.addEventListener('storage', event => {
        if (event.key === ordersKey || event.key === cartKey) render();
    });
})();
