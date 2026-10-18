<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout & Rewards</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f6fb;
            color: #172033;
        }

        header {
            background: #062a54;
            color: white;
            padding: 22px;
            text-align: center;
        }

        main {
            max-width: 1100px;
            margin: 25px auto;
            padding: 0 16px;
        }

        .panel {
            background: white;
            padding: 22px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 12px #0000000d;
        }

        h2 {
            margin-top: 0;
            color: #075bd8;
        }

        .products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
        }

        .product {
            border: 1px solid #dce4ef;
            padding: 15px;
            border-radius: 8px;
        }

        .product h3 {
            margin-top: 0;
        }

        .muted {
            color: #68758a;
            font-size: 14px;
        }

        button {
            border: none;
            border-radius: 6px;
            padding: 10px 14px;
            cursor: pointer;
            background: #075bd8;
            color: white;
            margin: 3px;
        }

        button:hover {
            opacity: 0.9;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .danger {
            background: #c62828;
        }

        .secondary {
            background: #53657d;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 14px 0;
            border-bottom: 1px solid #e5eaf2;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item input {
            width: 75px;
            padding: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
        }

        .item-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
        }

        .total {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }

        #message {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            display: none;
        }

        pre {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            background: #101c2e;
            color: #d8e7ff;
            padding: 15px;
            border-radius: 8px;
            min-height: 60px;
        }

        @media (max-width: 600px) {
            .cart-item {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
<header>
    <h1>Checkout & Rewards Service</h1>
    <p>Product catalog and shopping cart</p>
</header>

<main>
    <div id="message"></div>

    <section class="panel">
        <h2>1. Shopping Cart</h2>

        <button id="createCartBtn" onclick="createCart()">
            Create New Cart
        </button>

        <button class="secondary" onclick="refreshCart()">
            Refresh Cart
        </button>

        <p id="cartInfo" class="muted">No cart selected.</p>
        <div id="cartItems">Create a cart to begin shopping.</div>
        <div id="cartTotal" class="total"></div>
    </section>

    <section class="panel">
        <h2>2. Product Catalog</h2>
        <div id="products" class="products">Loading products...</div>
    </section>

    <section class="panel">
        <h2>3. Latest API Response</h2>
        <pre id="apiResponse">No API requests yet.</pre>
    </section>
</main>

<script>
    let currentCartId = null;
    let currentCartStatus = null;

    function showMessage(message, isError = false) {
        const element = document.getElementById('message');

        element.style.display = 'block';
        element.style.background = isError ? '#ffebee' : '#e8f5e9';
        element.style.color = isError ? '#b71c1c' : '#1b5e20';
        element.textContent = message;
    }

    async function apiRequest(url, method = 'GET', body = null) {
        const options = {
            method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        };

        if (body !== null) {
            options.body = JSON.stringify(body);
        }

        const response = await fetch(url, options);
        const data = await response.json().catch(() => ({
            message: 'The server returned an invalid response.'
        }));

        document.getElementById('apiResponse').textContent =
            JSON.stringify({
                status: response.status,
                response: data
            }, null, 2);

        if (!response.ok) {
            const error = new Error(
                data.message || 'Request failed.'
            );

            error.status = response.status;
            error.data = data;
            throw error;
        }

        return data;
    }

    function formatMoney(cents) {
        return '₹' + (Number(cents) / 100).toFixed(2);
    }

    async function loadProducts() {
        const container = document.getElementById('products');

        try {
            const result = await apiRequest('/api/products');
            container.innerHTML = '';

            result.data.forEach(product => {
                const card = document.createElement('div');
                card.className = 'product';

                const name = document.createElement('h3');
                name.textContent = product.name;

                const price = document.createElement('p');
                price.textContent = 'Price: ' + formatMoney(product.price_cents);

                const stock = document.createElement('p');
                stock.className = 'muted';
                stock.textContent = 'Available stock: ' + product.stock;

                const button = document.createElement('button');
                button.textContent = 'Add to Cart';
                button.disabled = product.stock < 1 ||
                    currentCartStatus !== 'open';
                button.onclick = () => addItem(product.id, product.stock);

                card.append(name, price, stock, button);
                container.appendChild(card);
            });
        } catch (error) {
            container.textContent = error.message;
            showMessage(error.message, true);
        }
    }

    async function createCart() {
        try {
            const result = await apiRequest('/api/carts', 'POST');

            currentCartId = result.data.id;
            currentCartStatus = result.data.status;

            showMessage('Cart #' + currentCartId + ' created.');
            await refreshCart();
            await loadProducts();
        } catch (error) {
            showMessage(error.message, true);
        }
    }

    async function addItem(productId, stock) {
        if (!currentCartId) {
            showMessage('Please create a cart first.', true);
            return;
        }

        if (currentCartStatus !== 'open') {
            showMessage('This cart is no longer open.', true);
            return;
        }

        const quantity = Number(
            prompt('Enter quantity to add (1 to ' + stock + '):', '1')
        );

        if (!Number.isInteger(quantity) || quantity < 1) {
            showMessage('Enter a valid positive whole number.', true);
            return;
        }

        try {
            await apiRequest(
                `/api/carts/${currentCartId}/items`,
                'POST',
                {
                    product_id: productId,
                    quantity: quantity
                }
            );

            showMessage('Product added to cart.');
            await refreshCart();
        } catch (error) {
            showMessage(error.message, true);
        }
    }

    async function refreshCart() {
        if (!currentCartId) {
            showMessage('Please create a cart first.', true);
            return;
        }

        try {
            const result = await apiRequest(
                `/api/carts/${currentCartId}`
            );

            const cart = result.data;
            currentCartStatus = cart.status;

            document.getElementById('cartInfo').textContent =
                `Cart #${cart.id} | Status: ${cart.status}`;

            const container = document.getElementById('cartItems');
            container.innerHTML = '';

            if (cart.items.length === 0) {
                container.textContent = 'Your cart is empty.';
            }

            cart.items.forEach(item => {
                const row = document.createElement('div');
                row.className = 'cart-item';

                const details = document.createElement('div');

                const name = document.createElement('strong');
                name.textContent = item.name;

                const price = document.createElement('div');
                price.className = 'muted';
                price.textContent =
                    `${formatMoney(item.unit_price_cents)} each`;

                const subtotal = document.createElement('div');
                subtotal.textContent =
                    'Subtotal: ' + formatMoney(item.line_total_cents);

                details.append(name, price, subtotal);

                const actions = document.createElement('div');
                actions.className = 'item-actions';

                const quantityInput = document.createElement('input');
                quantityInput.type = 'number';
                quantityInput.min = '1';
                quantityInput.step = '1';
                quantityInput.value = item.quantity;
                quantityInput.setAttribute(
                    'aria-label',
                    'Quantity for ' + item.name
                );

                const updateButton = document.createElement('button');
                updateButton.textContent = 'Update';
                updateButton.disabled = currentCartStatus !== 'open';
                updateButton.onclick = () =>
                    updateItem(item.product_id, quantityInput.value);

                const removeButton = document.createElement('button');
                removeButton.textContent = 'Remove';
                removeButton.className = 'danger';
                removeButton.disabled = currentCartStatus !== 'open';
                removeButton.onclick = () =>
                    removeItem(item.product_id);

                actions.append(quantityInput, updateButton, removeButton);
                row.append(details, actions);
                container.appendChild(row);
            });

            document.getElementById('cartTotal').textContent =
                'Cart Total: ' + formatMoney(cart.total_cents);

            await loadProducts();
        } catch (error) {
            showMessage(error.message, true);
        }
    }

    async function updateItem(productId, quantityValue) {
        const quantity = Number(quantityValue);

        if (!Number.isInteger(quantity) || quantity < 1) {
            showMessage('Quantity must be a whole number greater than zero.', true);
            return;
        }

        try {
            /*
             * The current GET cart response identifies items by product_id.
             * We first locate the cart item ID, then use it in the PUT URL.
             */
            const result = await apiRequest(
                `/api/carts/${currentCartId}`
            );

            const item = result.data.items.find(
                entry => entry.product_id === productId
            );

            if (!item) {
                showMessage('Cart item not found. Refresh the cart.', true);
                return;
            }

            // The API must expose each cart item's database ID for this route.
            if (!item.id) {
                showMessage(
                    'The cart API must return each item ID before quantity updates can work.',
                    true
                );
                return;
            }

            await apiRequest(
                `/api/carts/${currentCartId}/items/${item.id}`,
                'PUT',
                { quantity: quantity }
            );

            showMessage('Quantity updated.');
            await refreshCart();
        } catch (error) {
            showMessage(error.message, true);
        }
    }

    async function removeItem(productId) {
        if (!confirm('Remove this product from your cart?')) {
            return;
        }

        try {
            const result = await apiRequest(
                `/api/carts/${currentCartId}`
            );

            const item = result.data.items.find(
                entry => entry.product_id === productId
            );

            if (!item || !item.id) {
                showMessage(
                    'The cart API must return the cart item ID before removal can work.',
                    true
                );
                return;
            }

            await apiRequest(
                `/api/carts/${currentCartId}/items/${item.id}`,
                'DELETE'
            );

            showMessage('Product removed from cart.');
            await refreshCart();
        } catch (error) {
            showMessage(error.message, true);
        }
    }

    loadProducts();
</script>
</body>
</html>