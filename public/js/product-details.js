(function () {
    const product = document.getElementById('product');
    const quantityOutput = document.getElementById('quantity');
    const subtotal = document.getElementById('subtotal');
    const buyNowDialog = document.getElementById('buyNowDialog');
    const authDialog = document.getElementById('authDialog');
    const quantityValue = Number(quantityOutput.textContent);
    const unitPrice = Number(product.dataset.price);
    let quantity = quantityValue;
    let color = document.getElementById('selectedColor').textContent;

    function formatPrice(price) {
        return new Intl.NumberFormat('en-PH').format(price);
    }

    function syncCartCount() {
        const cart = JSON.parse(localStorage.getItem('oversmoke-cart') || '[]');
        document.getElementById('cartCount').textContent = cart.length;
    }

    const imagePreview = document.getElementById('productImagePreview');
    const previewImage = imagePreview.querySelector('img');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const supportsHover = window.matchMedia('(hover: hover) and (pointer: fine)');

    if (previewImage && supportsHover.matches && ! reducedMotion.matches) {
        function updatePreview(event) {
            const bounds = imagePreview.getBoundingClientRect();
            const x = (event.clientX - bounds.left) / bounds.width;
            const y = (event.clientY - bounds.top) / bounds.height;
            const rotateX = (0.5 - y) * 32;
            const rotateY = (x - 0.5) * 40;

            imagePreview.style.setProperty('--tilt-x', `${rotateX.toFixed(2)}deg`);
            imagePreview.style.setProperty('--tilt-y', `${rotateY.toFixed(2)}deg`);
            imagePreview.style.setProperty('--pointer-x', `${(x * 100).toFixed(2)}%`);
            imagePreview.style.setProperty('--pointer-y', `${(y * 100).toFixed(2)}%`);
            imagePreview.classList.add('is-interactive');
        }

        imagePreview.addEventListener('pointerenter', updatePreview);
        imagePreview.addEventListener('pointermove', updatePreview);

        imagePreview.addEventListener('pointerleave', () => {
            imagePreview.style.setProperty('--tilt-x', '0deg');
            imagePreview.style.setProperty('--tilt-y', '0deg');
            imagePreview.style.setProperty('--pointer-x', '50%');
            imagePreview.style.setProperty('--pointer-y', '50%');
            imagePreview.classList.remove('is-interactive');
        });
    }

    syncCartCount();
    window.addEventListener('pageshow', syncCartCount);
    window.addEventListener('storage', event => {
        if (event.key === 'oversmoke-cart') syncCartCount();
    });

    function updateQuantity(nextQuantity) {
        quantity = Math.max(1, nextQuantity);
        quantityOutput.textContent = quantity;
        subtotal.textContent = formatPrice(unitPrice * quantity);
    }

    function productImage() {
        const imageUrl = product.dataset.image;
        if (!imageUrl) return null;

        const parsedUrl = new URL(imageUrl, window.location.origin);
        return parsedUrl.origin === window.location.origin && parsedUrl.pathname.startsWith('/storage/')
            ? parsedUrl.href
            : null;
    }

    document.getElementById('decreaseQuantity').addEventListener('click', () => updateQuantity(quantity - 1));
    document.getElementById('increaseQuantity').addEventListener('click', () => updateQuantity(quantity + 1));

    document.querySelectorAll('.color-option').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.color-option').forEach(option => {
                option.classList.remove('selected');
                option.setAttribute('aria-pressed', 'false');
            });
            button.classList.add('selected');
            button.setAttribute('aria-pressed', 'true');
            color = button.dataset.color;
            document.getElementById('selectedColor').textContent = color;
        });
    });

    function addToCart() {
        if (product.dataset.authenticated !== 'true') {
            document.getElementById('authTitle').textContent = 'Sign in to add to cart';
            document.getElementById('authMessage').textContent =
                'Log in or create an account to add this item to your cart.';
            authDialog.showModal();
            return;
        }

        const cart = JSON.parse(localStorage.getItem('oversmoke-cart') || '[]');
        const matchingItem = cart.find(item => item.id === Number(product.dataset.id) && item.color === color);

        if (matchingItem) {
            matchingItem.quantity += quantity;
        } else {
            cart.push({
                id: Number(product.dataset.id),
                name: product.dataset.name,
                brand: product.dataset.brand,
                price: unitPrice,
                quantity,
                color,
                image: productImage()
            });
        }

        localStorage.setItem('oversmoke-cart', JSON.stringify(cart));
        syncCartCount();
        document.getElementById('statusMessage').textContent = 'Added to cart.';
    }

    document.getElementById('addToCart').addEventListener('click', addToCart);
    document.getElementById('buyNow').addEventListener('click', () => {
        if (product.dataset.authenticated !== 'true') {
            document.getElementById('authTitle').textContent = 'Sign in to purchase';
            document.getElementById('authMessage').textContent =
                'Log in or create an account before purchasing this item.';
            authDialog.showModal();
            return;
        }

        document.getElementById('confirmMessage').textContent =
            `Proceed to checkout with ${quantity} × ${product.dataset.name} in ${color}?`;
        buyNowDialog.showModal();
    });

    document.getElementById('closeAuthDialog').addEventListener('click', () => authDialog.close());
    document.getElementById('cancelBuyNow').addEventListener('click', () => buyNowDialog.close());
    document.getElementById('confirmBuyNow').addEventListener('click', () => {
        const item = {
            id: Number(product.dataset.id),
            name: product.dataset.name,
            brand: product.dataset.brand,
            price: unitPrice,
            quantity,
            color,
            image: productImage()
        };

        sessionStorage.setItem('oversmoke-checkout-items', JSON.stringify([item]));
        sessionStorage.setItem('oversmoke-checkout-source', 'buy-now');
        window.location.href = '/checkout';
    });
})();
