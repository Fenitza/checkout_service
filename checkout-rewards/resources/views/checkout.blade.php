
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102a56">
    <title>Checkout & Rewards | Commerce Console</title>

    <style>
        :root {
            --navy: #102a56;
            --blue: #2563eb;
            --blue-light: #eff6ff;
            --ink: #17233b;
            --muted: #718096;
            --line: #e5eaf2;
            --surface: #ffffff;
            --background: #f5f7fb;
            --green: #15803d;
            --red: #b91c1c;
            --radius: 16px;
            --shadow: 0 8px 28px rgba(20, 40, 80, .055);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--background);
            color: var(--ink);
            font-family: Inter, -apple-system, BlinkMacSystemFont,
                "Segoe UI", sans-serif;
            font-size: 14px;
            line-height: 1.55;
        }

        button, input, select { font: inherit; }
        button { cursor: pointer; }
        button:disabled { opacity: .48; cursor: not-allowed; }

        button:focus-visible, input:focus-visible, select:focus-visible {
            outline: 3px solid #93c5fd;
            outline-offset: 2px;
        }

        .topbar {
            background: var(--navy);
            color: white;
            padding: 19px 24px;
        }

        .topbar-inner, .page, .footer-inner {
            width: min(1240px, calc(100% - 40px));
            margin: auto;
        }

        .topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #2e63c9;
            font-weight: 800;
            font-size: 19px;
        }

        .brand h1 { font-size: 17px; margin: 0; }
        .brand p { margin: 2px 0 0; color: #c4d4ef; font-size: 12px; }

        .top-status {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #dbeafe;
            font-size: 12px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4ade80;
        }

        .page { padding: 30px 0 44px; }

        .welcome {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
        }

        .eyebrow {
            color: var(--blue);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 11px;
            font-weight: 800;
        }

        .welcome h2 {
            margin: 5px 0 6px;
            font-size: clamp(25px, 4vw, 32px);
            letter-spacing: -1px;
        }

        .welcome p { margin: 0; color: var(--muted); }

        .button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            border: 1px solid transparent;
            border-radius: 9px;
            padding: 10px 15px;
            font-weight: 700;
            transition: background .15s, transform .15s;
        }

        .button:not(:disabled):active { transform: translateY(1px); }
        .primary { background: var(--blue); color: white; }
        .primary:hover:not(:disabled) { background: #1d4ed8; }
        .secondary { background: white; color: var(--ink); border-color: #d9e1ed; }
        .secondary:hover:not(:disabled) { background: #f8fafc; }
        .danger { background: #fff1f2; color: var(--red); border-color: #ffe0e4; }
        .small { padding: 7px 10px; font-size: 12px; }

        .notice {
            display: none;
            margin-bottom: 20px;
            padding: 13px 16px;
            border: 1px solid transparent;
            border-radius: 11px;
        }

        .notice.success { display: block; color: #166534; background: #f0fdf4; border-color: #bbf7d0; }
        .notice.error { display: block; color: #991b1b; background: #fef2f2; border-color: #fecaca; }
        .notice.info { display: block; color: #1e40af; background: #eff6ff; border-color: #bfdbfe; }

        .metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 15px;
            margin-bottom: 22px;
        }

        .metric, .panel {
            background: var(--surface);
            border: 1px solid #e9edf4;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .metric { padding: 18px; }
        .metric-label { color: var(--muted); font-size: 12px; }
        .metric-value { font-size: 24px; font-weight: 800; margin-top: 5px; letter-spacing: -.6px; }
        .metric-note { font-size: 11px; color: var(--muted); margin-top: 2px; }

        .workspace {
            display: grid;
            grid-template-columns: minmax(0, 1.55fr) minmax(310px, .85fr);
            align-items: start;
            gap: 20px;
        }

        .left-column, .right-column {
            display: grid;
            gap: 20px;
            min-width: 0;
        }

        .panel { padding: 22px; }

        .panel-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
        }

        .panel-heading h3 { margin: 0; font-size: 17px; letter-spacing: -.3px; }
        .panel-heading p { color: var(--muted); margin: 4px 0 0; font-size: 12px; }

        .count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 25px;
            height: 25px;
            padding: 0 7px;
            border-radius: 8px;
            background: var(--blue-light);
            color: var(--blue);
            font-weight: 800;
            font-size: 12px;
        }

        .field-label {
            display: block;
            margin-bottom: 6px;
            color: #4b5870;
            font-size: 12px;
            font-weight: 700;
        }

        .field, select {
            width: 100%;
            min-height: 41px;
            padding: 9px 11px;
            border: 1px solid #dbe2ec;
            border-radius: 9px;
            background: white;
            color: var(--ink);
        }

        .cart-toolbar {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto auto;
            align-items: end;
            gap: 9px;
            margin-bottom: 17px;
        }

        .cart-status {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
            color: var(--muted);
            font-size: 12px;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 9px;
            font-size: 11px;
            font-weight: 800;
            text-transform: capitalize;
            background: #f1f5f9;
            color: #475569;
        }

        .pill.open, .pill.available { background: #dcfce7; color: #166534; }
        .pill.checked_out, .pill.redeemed { background: #e2e8f0; color: #475569; }

        .cart-line {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            padding: 16px 0;
            border-top: 1px solid var(--line);
        }

        .item-name { font-weight: 750; }
        .item-description { margin-top: 3px; color: var(--muted); font-size: 12px; }
        .item-subtotal { margin-top: 6px; font-weight: 800; }

        .item-controls {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            flex-wrap: wrap;
        }

        .quantity {
            width: 65px;
            min-height: 34px;
            padding: 5px 7px;
            border: 1px solid #dbe2ec;
            border-radius: 7px;
        }

        .empty {
            padding: 28px 12px;
            text-align: center;
            color: var(--muted);
        }

        .empty-icon {
            display: grid;
            place-items: center;
            width: 48px;
            height: 48px;
            margin: 0 auto 10px;
            border-radius: 15px;
            background: #f1f5f9;
            font-size: 22px;
        }

        .catalog-tools { margin-bottom: 15px; }

        .products {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .product {
            display: flex;
            flex-direction: column;
            padding: 15px;
            border: 1px solid var(--line);
            border-radius: 13px;
            min-width: 0;
            transition: border-color .15s, box-shadow .15s;
        }

        .product:hover { border-color: #b9cdfa; box-shadow: 0 5px 16px #2563eb0c; }

        .product-icon {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            margin-bottom: 13px;
            border-radius: 12px;
            background: #f0f5ff;
            font-size: 21px;
        }

        .product h4 { margin: 0; font-size: 14px; overflow-wrap: anywhere; }
        .product-price { margin: 6px 0 2px; font-size: 18px; font-weight: 800; }
        .stock { font-size: 11px; color: var(--muted); margin-bottom: 13px; }
        .stock.low { color: #b45309; }
        .stock.none { color: var(--red); }

        .product-actions {
            display: grid;
            grid-template-columns: 65px minmax(0, 1fr);
            gap: 7px;
            margin-top: auto;
        }

        .product-actions input {
            width: 100%;
            min-width: 0;
            border: 1px solid #dbe2ec;
            border-radius: 8px;
            padding: 8px;
        }

        .product-actions button { padding: 8px 5px; font-size: 12px; }

        .summary {
            border-top: 1px solid var(--line);
            padding-top: 15px;
            margin-top: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 9px 0;
            color: #536178;
        }

        .summary-row strong { color: var(--ink); }
        .summary-row.discount strong { color: var(--green); }

        .summary-row.grand-total {
            border-top: 1px dashed #dbe2ec;
            padding-top: 13px;
            margin-top: 14px;
            font-size: 17px;
            font-weight: 800;
            color: var(--ink);
        }

        .coupon-box {
            margin-top: 17px;
            padding: 14px;
            border-radius: 12px;
            background: #f8faff;
            border: 1px solid #e0e9ff;
        }

        .coupon-box label { display: block; font-weight: 750; margin-bottom: 8px; }
        .coupon-help { font-size: 11px; color: var(--muted); margin-top: 7px; }

        .checkout-button { width: 100%; margin-top: 15px; padding: 13px; font-size: 14px; }
        .subtle-button { border: 0; background: transparent; color: var(--blue); padding: 5px 0; font-weight: 700; }

        .coupon-list { display: grid; gap: 10px; }
        .coupon-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid var(--line);
            border-radius: 11px;
            padding: 12px;
        }

        .coupon-name { font-weight: 750; }
        .coupon-note { color: var(--muted); font-size: 11px; }
        .coupon-percent { color: var(--green); font-weight: 800; font-size: 17px; }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .report-stat {
            padding: 12px;
            background: #f8fafc;
            border: 1px solid #edf1f6;
            border-radius: 10px;
            min-width: 0;
        }

        .report-stat span { display: block; color: var(--muted); font-size: 11px; }
        .report-stat strong { display: block; margin-top: 5px; font-size: 17px; overflow-wrap: anywhere; }

        details { margin-top: 18px; }
        summary { cursor: pointer; color: #536178; font-weight: 750; font-size: 12px; }
        pre {
            margin: 10px 0 0;
            padding: 13px;
            max-height: 300px;
            overflow: auto;
            background: #101c2e;
            color: #d8e7ff;
            border-radius: 10px;
            font-size: 11px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .footer { color: var(--muted); text-align: center; padding: 20px; font-size: 11px; }

        .loading { opacity: .65; pointer-events: none; }

        @media (max-width: 900px) {
            .workspace { grid-template-columns: minmax(0, 1fr); }
            .right-column { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; }
            .checkout-panel { grid-row: span 2; }
        }

        @media (max-width: 650px) {
            .topbar { padding: 16px 0; }
            .topbar-inner, .page, .footer-inner { width: min(100% - 26px, 1240px); }
            .top-status { display: none; }
            .page { padding-top: 23px; }
            .welcome { align-items: flex-start; flex-direction: column; }
            .metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
            .metric { padding: 14px; }
            .metric-value { font-size: 21px; }
            .panel { padding: 16px; }
            .right-column { grid-template-columns: minmax(0, 1fr); }
            .cart-toolbar { grid-template-columns: minmax(0, 1fr) auto; }
            .cart-toolbar .cart-id-field { grid-column: 1 / -1; }
            .products { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
            .product { padding: 12px; }
            .product-actions { grid-template-columns: 52px minmax(0, 1fr); }
            .product-price { font-size: 16px; }
        }

        @media (max-width: 390px) {
            .products { grid-template-columns: minmax(0, 1fr); }
            .cart-line { grid-template-columns: minmax(0, 1fr); }
            .item-controls { justify-content: flex-start; }
        }
    </style>
</head>

<body>
<header class="topbar">
    <div class="topbar-inner">
        <div class="brand">
            <div class="brand-mark">CR</div>
            <div>
                <h1>Commerce Console</h1>
                <p>Checkout & Rewards Service</p>
            </div>
        </div>
        <div class="top-status">
            <span class="status-dot"></span>
            API workspace
        </div>
    </div>
</header>

<main class="page">
    <section class="welcome">
        <div>
            <div class="eyebrow">Store management</div>
            <h2>Checkout dashboard</h2>
            <p>Manage your cart, apply rewards, and review sales activity.</p>
        </div>
        <button class="button primary" id="newCartButton" type="button">
            + Create new cart
        </button>
    </section>

    <div id="message" class="notice" role="status" aria-live="polite"></div>

    <section class="metrics" aria-label="Sales overview">
        <article class="metric">
            <div class="metric-label">Total orders</div>
            <div class="metric-value" id="metricOrders">—</div>
            <div class="metric-note">Successful checkouts</div>
        </article>
        <article class="metric">
            <div class="metric-label">Gross revenue</div>
            <div class="metric-value" id="metricGross">—</div>
            <div class="metric-note">Before discounts</div>
        </article>
        <article class="metric">
            <div class="metric-label">Discounts given</div>
            <div class="metric-value" id="metricDiscount">—</div>
            <div class="metric-note">Coupon savings</div>
        </article>
        <article class="metric">
            <div class="metric-label">Net revenue</div>
            <div class="metric-value" id="metricNet">—</div>
            <div class="metric-note">After discounts</div>
        </article>
    </section>

    <div class="workspace">
        <div class="left-column">
            <section class="panel">
                <div class="panel-heading">
                    <div>
                        <h3>Shopping cart</h3>
                        <p>Review items and adjust quantities before checkout.</p>
                    </div>
                    <span class="count-badge" id="cartCount">0</span>
                </div>

                <div class="cart-toolbar">
                    <div class="cart-id-field">
                        <label class="field-label" for="cartIdInput">Existing cart ID</label>
                        <input class="field" id="cartIdInput" type="number"
                               min="1" placeholder="Enter cart ID">
                    </div>
                    <button class="button secondary" id="loadCartButton" type="button">
                        Load cart
                    </button>
                    <button class="button secondary" id="refreshCartButton" type="button">
                        Refresh
                    </button>
                </div>

                <div class="cart-status">
                    <span id="cartStatusPill" class="pill">No cart</span>
                    <span id="cartDescription">Create a cart or load an existing one.</span>
                </div>

                <div id="cartItems">
                    <div class="empty">
                        <div class="empty-icon">🛒</div>
                        <strong>Your cart is waiting</strong>
                        <div>Create a cart to start shopping.</div>
                    </div>
                </div>

                <div class="summary">
                    <div class="summary-row">
                        <span>Items subtotal</span>
                        <strong id="subtotalValue">₹0.00</strong>
                    </div>
                    <div class="summary-row discount">
                        <span>Coupon discount</span>
                        <strong id="discountValue">− ₹0.00</strong>
                    </div>
                    <div class="summary-row grand-total">
                        <span>Estimated total</span>
                        <strong id="totalValue">₹0.00</strong>
                    </div>
                    <p class="coupon-help">
                        The final amount is confirmed by the checkout API.
                    </p>
                </div>
            </section>

            <section class="panel">
                <div class="panel-heading">
                    <div>
                        <h3>Product catalog</h3>
                        <p>Select products to add them to your active cart.</p>
                    </div>
                    <button class="subtle-button" id="reloadProductsButton" type="button">
                        Reload products ↻
                    </button>
                </div>
                <div class="catalog-tools">
                    <input class="field" id="productSearch" type="search"
                           placeholder="Search products..." aria-label="Search products">
                </div>
                <div id="products" class="products">
                    <div class="empty">Loading products…</div>
                </div>
            </section>
        </div>

        <aside class="right-column">
            <section class="panel checkout-panel">
                <div class="panel-heading">
                    <div>
                        <h3>Complete checkout</h3>
                        <p>Apply an eligible reward coupon, then place the order.</p>
                    </div>
                </div>

                <div class="coupon-box">
                    <label for="couponSelect">Reward coupon</label>
                    <select id="couponSelect">
                        <option value="">No coupon</option>
                    </select>
                    <div class="coupon-help" id="couponHelp">
                        No coupon selected.
                    </div>
                    <button class="subtle-button" id="reloadCouponsButton" type="button">
                        Refresh available coupons ↻
                    </button>
                </div>

                <button class="button primary checkout-button"
                        id="checkoutButton" type="button" disabled>
                    Place order →
                </button>

                <p class="coupon-help">
                    Repeated requests use the same key while a checkout attempt
                    is being retried. No real payment is collected.
                </p>

                <div id="lastOrder" hidden></div>
            </section>

            <section class="panel">
                <div class="panel-heading">
                    <div>
                        <h3>Rewards</h3>
                        <p>Track coupon availability and redemption.</p>
                    </div>
                </div>
                <div class="report-grid">
                    <div class="report-stat">
                        <span>Generated</span>
                        <strong id="couponGenerated">—</strong>
                    </div>
                    <div class="report-stat">
                        <span>Available</span>
                        <strong id="couponAvailable">—</strong>
                    </div>
                    <div class="report-stat">
                        <span>Redeemed</span>
                        <strong id="couponRedeemed">—</strong>
                    </div>
                    <div class="report-stat">
                        <span>Reward rule</span>
                        <strong>Every 5 orders*</strong>
                    </div>
                </div>
                <p class="coupon-help">
                    *Displayed rule is the current seed setting. Change it here
                    if you configure a different milestone.
                </p>
            </section>

            <section class="panel">
                <div class="panel-heading">
                    <div>
                        <h3>Sales by product</h3>
                        <p>Based on saved order-item snapshots.</p>
                    </div>
                    <button class="subtle-button" id="refreshReportButton" type="button">
                        Refresh ↻
                    </button>
                </div>
                <div id="productReport" class="coupon-list">
                    <div class="empty">Loading report…</div>
                </div>
            </section>
        </aside>
    </div>

    <details>
        <summary>Developer tools — latest API response</summary>
        <pre id="apiResponse">No API requests yet.</pre>
    </details>
</main>

<footer class="footer">
    <div class="footer-inner">Checkout & Rewards Service · Laravel API demo</div>
</footer>

<script>
    const state = {
        cartId: null,
        cartStatus: null,
        cart: null,
        products: [],
        coupons: [],
        report: null,
        checkoutKey: null,
        busy: false
    };

    const $ = id => document.getElementById(id);

    function formatMoney(cents) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR'
        }).format(Number(cents || 0) / 100);
    }

    function showMessage(message, type = 'success') {
        const element = $('message');
        element.textContent = message;
        element.className = 'notice ' + type;
    }

    function clearMessage() {
        $('message').className = 'notice';
        $('message').textContent = '';
    }

    function setBusy(busy) {
        state.busy = busy;
        document.body.classList.toggle('loading', busy);
        $('newCartButton').disabled = busy;
        $('loadCartButton').disabled = busy;
        $('refreshCartButton').disabled = busy;
        $('checkoutButton').disabled =
            busy || !state.cartId ||
            state.cartStatus !== 'open' ||
            !state.cart ||
            !state.cart.items ||
            state.cart.items.length === 0;
    }

    function unwrap(payload) {
        return payload && payload.data !== undefined
            ? payload.data
            : payload;
    }

    async function apiRequest(url, method = 'GET', body = null) {
        const options = {
            method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        };

        if (body !== null) options.body = JSON.stringify(body);

        let response;

        try {
            response = await fetch(url, options);
        } catch (networkError) {
            throw new Error('Could not reach the Laravel server. Check that it is running.');
        }

        const data = await response.json().catch(() => ({
            message: 'The server returned a non-JSON response.'
        }));

        $('apiResponse').textContent = JSON.stringify({
            method,
            url,
            status: response.status,
            response: data
        }, null, 2);

        if (!response.ok) {
            let message = data.message || 'Request failed.';

            if (data.errors) {
                const firstError = Object.values(data.errors).flat()[0];
                if (firstError) message = firstError;
            }

            const error = new Error(message);
            error.status = response.status;
            error.data = data;
            throw error;
        }

        return data;
    }

    function makeIdempotencyKey() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        return 'checkout-' + Date.now() + '-' +
            Math.random().toString(36).slice(2, 12);
    }

    function productIcon(name) {
        const value = name.toLowerCase();

        if (value.includes('mouse')) return '🖱️';
        if (value.includes('keyboard')) return '⌨️';
        if (value.includes('cable')) return '🔌';
        if (value.includes('stand')) return '💻';
        if (value.includes('webcam') || value.includes('camera')) return '📷';

        return '📦';
    }

    function updateSummary() {
        const cart = state.cart;
        const items = cart && Array.isArray(cart.items) ? cart.items : [];

        const subtotal = Number(cart?.total_cents || 0);
        const selected = $('couponSelect').value;
        const coupon = state.coupons.find(item => String(item.id) === selected);

        // Preview only. The backend remains the source of truth.
        const previewDiscount = coupon
            ? Math.floor(subtotal * Number(coupon.discount_percent) / 100)
            : 0;

        $('subtotalValue').textContent = formatMoney(subtotal);
        $('discountValue').textContent = '− ' + formatMoney(previewDiscount);
        $('totalValue').textContent = formatMoney(
            Math.max(0, subtotal - previewDiscount)
        );
        $('cartCount').textContent = items.reduce(
            (sum, item) => sum + Number(item.quantity || 0), 0
        );

        $('checkoutButton').disabled =
            state.busy || !state.cartId ||
            state.cartStatus !== 'open' || items.length === 0;

        $('couponHelp').textContent = coupon
            ? `${coupon.discount_percent}% discount selected. Final amount is calculated by the server.`
            : 'Checkout without a coupon.';

        if (state.cartStatus !== 'open') {
            $('couponSelect').disabled = true;
        } else {
            $('couponSelect').disabled = false;
        }
    }

    function renderProducts() {
        const container = $('products');
        const search = $('productSearch').value.trim().toLowerCase();

        const products = state.products.filter(product =>
            product.name.toLowerCase().includes(search)
        );

        container.replaceChildren();

        if (!products.length) {
            container.textContent = search
                ? 'No products match your search.'
                : 'No products are available.';
            return;
        }

        products.forEach(product => {
            const card = document.createElement('article');
            card.className = 'product';

            const icon = document.createElement('div');
            icon.className = 'product-icon';
            icon.textContent = productIcon(product.name);

            const name = document.createElement('h4');
            name.textContent = product.name;

            const price = document.createElement('div');
            price.className = 'product-price';
            price.textContent = formatMoney(product.price_cents);

            const stock = document.createElement('div');
            stock.className = 'stock';
            stock.textContent = Number(product.stock) < 1
                ? 'Out of stock'
                : `${product.stock} available`;

            if (Number(product.stock) < 1) stock.classList.add('none');
            else if (Number(product.stock) <= 3) stock.classList.add('low');

            const actions = document.createElement('div');
            actions.className = 'product-actions';

            const quantity = document.createElement('input');
            quantity.type = 'number';
            quantity.min = '1';
            quantity.max = String(product.stock);
            quantity.step = '1';
            quantity.value = '1';
            quantity.setAttribute('aria-label', 'Quantity for ' + product.name);

            const add = document.createElement('button');
            add.type = 'button';
            add.className = 'button primary';
            add.textContent = 'Add';
            add.disabled = Number(product.stock) < 1 ||
                state.cartStatus !== 'open' || state.busy;

            add.addEventListener('click', () => {
                const qty = Number(quantity.value);

                if (!Number.isInteger(qty) || qty < 1) {
                    showMessage('Enter a positive whole-number quantity.', 'error');
                    return;
                }

                if (qty > Number(product.stock)) {
                    showMessage('Requested quantity exceeds the displayed stock.', 'error');
                    return;
                }

                addItem(product.id, qty);
            });

            actions.append(quantity, add);
            card.append(icon, name, price, stock, actions);
            container.appendChild(card);
        });
    }

    async function loadProducts() {
        try {
            const payload = await apiRequest('/api/products');
            state.products = unwrap(payload) || [];
            renderProducts();
        } catch (error) {
            $('products').textContent = error.message;
            showMessage(error.message, 'error');
        }
    }

    async function createCart() {
        clearMessage();
        setBusy(true);

        try {
            const payload = await apiRequest('/api/carts', 'POST');
            const cart = unwrap(payload);

            state.cartId = cart.id;
            state.cartStatus = cart.status;
            state.checkoutKey = null;
            $('cartIdInput').value = cart.id;

            await refreshCart(false);
            showMessage(`Cart #${cart.id} created successfully.`);
        } catch (error) {
            showMessage(error.message, 'error');
        } finally {
            setBusy(false);
            renderProducts();
        }
    }

    async function loadCartFromInput() {
        const id = Number($('cartIdInput').value);

        if (!Number.isInteger(id) || id < 1) {
            showMessage('Enter a valid cart ID.', 'error');
            return;
        }

        state.cartId = id;
        state.checkoutKey = null;
        await refreshCart();
    }

    async function addItem(productId, quantity) {
        if (!state.cartId) {
            showMessage('Create a cart or load an existing cart first.', 'error');
            return;
        }

        if (state.cartStatus !== 'open') {
            showMessage('This cart is already checked out.', 'error');
            return;
        }

        clearMessage();
        setBusy(true);

        try {
            await apiRequest(`/api/carts/${state.cartId}/items`, 'POST', {
                product_id: productId,
                quantity
            });

            // Cart changed, so a future checkout must use a new key.
            state.checkoutKey = null;
            await refreshCart(false);
            showMessage('Product added to your cart.');
        } catch (error) {
            showMessage(error.message, 'error');
        } finally {
            setBusy(false);
            renderProducts();
        }
    }

    function renderCart(cart) {
        state.cart = cart;
        state.cartId = cart.id;
        state.cartStatus = cart.status;

        $('cartIdInput').value = cart.id;
        $('cartStatusPill').textContent = cart.status.replace('_', ' ');
        $('cartStatusPill').className = 'pill ' + cart.status;
        $('cartDescription').textContent =
            cart.status === 'open'
                ? 'Your cart is ready for checkout.'
                : 'This cart has already been checked out.';

        const container = $('cartItems');
        container.replaceChildren();

        const items = Array.isArray(cart.items) ? cart.items : [];

        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'empty';

            const icon = document.createElement('div');
            icon.className = 'empty-icon';
            icon.textContent = '🛒';

            const title = document.createElement('strong');
            title.textContent = 'Your cart is empty';

            const note = document.createElement('div');
            note.textContent = 'Add a product from the catalog below.';

            empty.append(icon, title, note);
            container.appendChild(empty);
        }

        items.forEach(item => {
            const row = document.createElement('div');
            row.className = 'cart-line';

            const info = document.createElement('div');

            const name = document.createElement('div');
            name.className = 'item-name';
            name.textContent = item.name;

            const description = document.createElement('div');
            description.className = 'item-description';
            description.textContent =
                `${formatMoney(item.unit_price_cents)} each · Qty ${item.quantity}`;

            const subtotal = document.createElement('div');
            subtotal.className = 'item-subtotal';
            subtotal.textContent = formatMoney(item.line_total_cents);

            info.append(name, description, subtotal);

            const controls = document.createElement('div');
            controls.className = 'item-controls';

            const quantity = document.createElement('input');
            quantity.className = 'quantity';
            quantity.type = 'number';
            quantity.min = '1';
            quantity.step = '1';
            quantity.value = item.quantity;
            quantity.setAttribute('aria-label', 'Quantity for ' + item.name);

            const update = document.createElement('button');
            update.className = 'button secondary small';
            update.textContent = 'Update';
            update.disabled = cart.status !== 'open';

            update.addEventListener('click', () =>
                updateItem(item, quantity.value)
            );

            const remove = document.createElement('button');
            remove.className = 'button danger small';
            remove.textContent = 'Remove';
            remove.disabled = cart.status !== 'open';

            remove.addEventListener('click', () => removeItem(item));

            controls.append(quantity, update, remove);
            row.append(info, controls);
            container.appendChild(row);
        });

        updateSummary();
        renderProducts();
    }

    async function refreshCart(showSuccess = false) {
        if (!state.cartId) {
            showMessage('Create a cart or load an existing cart first.', 'error');
            return;
        }

        try {
            const payload = await apiRequest(`/api/carts/${state.cartId}`);
            const cart = unwrap(payload);
            renderCart(cart);

            if (showSuccess) showMessage('Cart refreshed.');
        } catch (error) {
            showMessage(error.message, 'error');
        }
    }

    async function updateItem(item, quantityValue) {
        const quantity = Number(quantityValue);

        if (!Number.isInteger(quantity) || quantity < 1) {
            showMessage('Quantity must be a positive whole number.', 'error');
            return;
        }

        if (!item.id) {
            showMessage(
                'The cart API response is missing the cart item ID. Include item.id in each returned cart item.',
                'error'
            );
            return;
        }

        setBusy(true);

        try {
            await apiRequest(
                `/api/carts/${state.cartId}/items/${item.id}`,
                'PUT',
                { quantity }
            );

            state.checkoutKey = null;
            await refreshCart(false);
            showMessage('Cart quantity updated.');
        } catch (error) {
            showMessage(error.message, 'error');
        } finally {
            setBusy(false);
        }
    }

    async function removeItem(item) {
        if (!item.id) {
            showMessage('Cart item ID is missing from the API response.', 'error');
            return;
        }

        if (!window.confirm(`Remove "${item.name}" from this cart?`)) return;

        setBusy(true);

        try {
            await apiRequest(
                `/api/carts/${state.cartId}/items/${item.id}`,
                'DELETE'
            );

            state.checkoutKey = null;
            await refreshCart(false);
            showMessage('Product removed from your cart.');
        } catch (error) {
            showMessage(error.message, 'error');
        } finally {
            setBusy(false);
        }
    }

    async function loadCoupons() {
        const select = $('couponSelect');

        try {
            const payload = await apiRequest('/api/coupons/available');
            state.coupons = unwrap(payload) || [];

            const previous = select.value;
            select.replaceChildren();

            const none = document.createElement('option');
            none.value = '';
            none.textContent = 'No coupon';
            select.appendChild(none);

            state.coupons.forEach(coupon => {
                const option = document.createElement('option');
                option.value = coupon.id;
                option.textContent =
                    `Coupon #${coupon.id} · ${coupon.discount_percent}% off`;
                select.appendChild(option);
            });

            if (state.coupons.some(c => String(c.id) === previous)) {
                select.value = previous;
            }

            updateSummary();
        } catch (error) {
            showMessage('Could not load coupons: ' + error.message, 'error');
        }
    }

    async function checkout() {
        if (!state.cartId || state.cartStatus !== 'open') {
            showMessage('Select an open cart before checkout.', 'error');
            return;
        }

        if (!state.cart?.items?.length) {
            showMessage('Add at least one product before checkout.', 'error');
            return;
        }

        if (!state.checkoutKey) state.checkoutKey = makeIdempotencyKey();

        const body = { idempotency_key: state.checkoutKey };
        const couponId = $('couponSelect').value;

        if (couponId) body.coupon_id = Number(couponId);

        clearMessage();
        setBusy(true);
        $('checkoutButton').textContent = 'Processing…';

        try {
            const payload = await apiRequest(
                `/api/carts/${state.cartId}/checkout`,
                'POST',
                body
            );

            const order = unwrap(payload);
            const orderId = order.id ?? order.order?.id;

            $('lastOrder').hidden = false;
            $('lastOrder').textContent = orderId
                ? `Order #${orderId} placed successfully.`
                : 'Order placed successfully.';

            showMessage(
                orderId
                    ? `Checkout successful. Order #${orderId} has been created.`
                    : 'Checkout successful. Your order has been created.'
            );

            state.checkoutKey = null;
            $('couponSelect').value = '';

            await refreshCart(false);
            await loadProducts();
            await loadCoupons();
            await loadReport();
        } catch (error) {
            // Preserve the key after a failed/uncertain request.
            // A retry can safely use the same key if the first request
            // actually committed but its response was lost.
            showMessage(error.message, 'error');

            if (error.status === 409 || error.status === 422) {
                // These are definitive application responses, not network
                // uncertainty. Let the user correct the cart or coupon.
                state.checkoutKey = null;
            }
        } finally {
            $('checkoutButton').textContent = 'Place order →';
            setBusy(false);
            updateSummary();
        }
    }

    async function loadReport() {
        try {
            const payload = await apiRequest('/api/admin/report');
            const report = unwrap(payload);
            state.report = report;

            const orders = report.orders || {};
            const coupons = report.coupons || {};

            $('metricOrders').textContent = Number(orders.total || 0).toLocaleString('en-IN');
            $('metricGross').textContent = formatMoney(orders.gross_cents);
            $('metricDiscount').textContent = formatMoney(orders.discount_cents);
            $('metricNet').textContent = formatMoney(orders.net_cents);

            $('couponGenerated').textContent = Number(coupons.generated || 0);
            $('couponAvailable').textContent = Number(coupons.available || 0);
            $('couponRedeemed').textContent = Number(coupons.redeemed || 0);

            const container = $('productReport');
            container.replaceChildren();

            const products = report.products || [];

            if (!products.length) {
                const empty = document.createElement('div');
                empty.className = 'empty';
                empty.textContent = 'No product sales yet.';
                container.appendChild(empty);
            } else {
                products.forEach(product => {
                    const row = document.createElement('div');
                    row.className = 'coupon-row';

                    const left = document.createElement('div');
                    const name = document.createElement('div');
                    name.className = 'coupon-name';
                    name.textContent = product.product_name;

                    const note = document.createElement('div');
                    note.className = 'coupon-note';
                    note.textContent = `${Number(product.quantity_sold)} sold`;

                    left.append(name, note);

                    const amount = document.createElement('div');
                    amount.className = 'coupon-percent';
                    amount.textContent = formatMoney(product.gross_cents);

                    row.append(left, amount);
                    container.appendChild(row);
                });
            }
        } catch (error) {
            $('productReport').textContent = 'Report unavailable: ' + error.message;
        }
    }

    $('newCartButton').addEventListener('click', createCart);
    $('loadCartButton').addEventListener('click', loadCartFromInput);
    $('refreshCartButton').addEventListener('click', () => refreshCart(true));
    $('reloadProductsButton').addEventListener('click', loadProducts);
    $('reloadCouponsButton').addEventListener('click', loadCoupons);
    $('refreshReportButton').addEventListener('click', loadReport);
    $('checkoutButton').addEventListener('click', checkout);
    $('productSearch').addEventListener('input', renderProducts);
    $('couponSelect').addEventListener('change', updateSummary);

    async function initialize() {
        await Promise.all([
            loadProducts(),
            loadCoupons(),
            loadReport()
        ]);

        renderProducts();
        updateSummary();
    }

    initialize();
</script>
</body>
</html>
